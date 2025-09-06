<?php

namespace App\Services\Certificate\Generators;

use App\Contracts\CertificateTemplateInterface;
use App\Exceptions\AppException;
use App\Models\Certificate;
use App\Services\Certificate\S3CertificateService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;

class PdfCertificateGenerator extends BaseCertificateGenerator
{
    private Dompdf $dompdf;
    protected string $defaultFormat = 'pdf';
    protected array $supportedFormats = ['pdf'];

    public function __construct(S3CertificateService $s3Service)
    {
        parent::__construct($s3Service);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('debugPng', false);
        $options->set('debugKeepTemp', false);
        $options->set('dpi', 150);
        $options->set('defaultMediaType', 'print');
        $options->set('isFontSubsettingEnabled', true);
        $options->set('isPhpEnabled', false);

        $this->dompdf = new Dompdf($options);
    }

    /**
     * @throws AppException
     */
    public function generate(Certificate $certificate, string $format = 'pdf'): string
    {
        $this->validateFormat($format);

        // Get the default template service and generate HTML
        $templateService = app(CertificateTemplateInterface::class);
        return $this->generatePdfFromHtml($templateService->generate($certificate));
    }

    /**
     * @throws AppException
     */
    public function generateWithTemplate(Certificate $certificate, CertificateTemplateInterface $template): string
    {
        $html = $template->generate($certificate);
        return $this->generatePdfFromHtml($html);
    }

    public function save(Certificate $certificate, string $content, ?string $format = null): string
    {
        // Default to PDF if format is null
        if ($format === null) {
            $format = 'pdf';
        }

        $this->validateFormat($format);

        try {
            return $this->s3Service->uploadCertificate($certificate, $content, $format, $certificate->certificate_template ?? 'default');
        } catch (\Exception $e) {
            Log::error("Failed to save {$format} certificate to S3", [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @throws AppException
     */
    private function generatePdfFromHtml(string $html): string
    {
        Log::info('PdfCertificateGenerator: Starting PDF generation with fallback system');

        // Try wkhtmltopdf first (best quality)
        if ($this->hasWkhtmltopdf()) {
            Log::info('PdfCertificateGenerator: Using wkhtmltopdf');
            try {
                return $this->convertWithWkhtmltopdf($html);
            } catch (\Exception $e) {
                Log::warning('PdfCertificateGenerator: wkhtmltopdf failed, trying Puppeteer', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Try Puppeteer second (good quality)
        if ($this->hasPuppeteer()) {
            Log::info('PdfCertificateGenerator: Using Puppeteer');
            try {
                return $this->convertWithPuppeteer($html);
            } catch (\Exception $e) {
                Log::warning('PdfCertificateGenerator: Puppeteer failed, trying Chrome headless', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Try Chrome headless third
        if ($this->hasChromeHeadless()) {
            Log::info('PdfCertificateGenerator: Using Chrome headless');
            try {
                return $this->convertWithChromeHeadless($html);
            } catch (\Exception $e) {
                Log::warning('PdfCertificateGenerator: Chrome headless failed, falling back to DomPDF', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Throw exception if all methods fail
        throw new AppException("No PDF generation tool available. Please install wkhtmltopdf, Puppeteer, or Chrome headless.");
    }

    private function hasWkhtmltopdf(): bool
    {
        $output = [];
        $returnVar = 0;
        exec('which wkhtmltopdf 2>/dev/null', $output, $returnVar);
        return $returnVar === 0 && !empty($output);
    }

    private function hasPuppeteer(): bool
    {
        // Check if Node.js and Puppeteer are available with ES modules syntax
        $output = [];
        $returnVar = 0;
        exec('node -e "import puppeteer from \'puppeteer\'; console.log(\'ok\')" 2>/dev/null', $output, $returnVar);
        return $returnVar === 0;
    }

    private function hasChromeHeadless(): bool
    {
        $chromeCommands = [
            'google-chrome',
            'chrome',
            'chromium',
            'chromium-browser',
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
        ];

        foreach ($chromeCommands as $command) {
            $output = [];
            $returnVar = 0;
            exec("which \"$command\" 2>/dev/null || test -f \"$command\"", $output, $returnVar);
            if ($returnVar === 0) {
                return true;
            }
        }
        return false;
    }

    private function convertWithWkhtmltopdf(string $html): string
    {
        $tempHtmlFile = tempnam(sys_get_temp_dir(), 'cert_') . '.html';
        $tempPdfFile = tempnam(sys_get_temp_dir(), 'cert_') . '.pdf';

        try {
            // Write HTML to temp file
            file_put_contents($tempHtmlFile, $html);

            // Build wkhtmltopdf command
            $command = sprintf(
                'wkhtmltopdf --page-size A4 --orientation Landscape --margin-top 0 --margin-right 0 --margin-bottom 0 --margin-left 0 --disable-smart-shrinking --print-media-type --enable-local-file-access "%s" "%s" 2>&1',
                $tempHtmlFile,
                $tempPdfFile
            );

            Log::debug('PdfCertificateGenerator: wkhtmltopdf command', ['command' => $command]);

            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($tempPdfFile)) {
                throw new \Exception('wkhtmltopdf conversion failed: ' . implode("\n", $output));
            }

            $pdfContent = file_get_contents($tempPdfFile);

            Log::info('PdfCertificateGenerator: wkhtmltopdf conversion successful', [
                'pdf_size' => strlen($pdfContent)
            ]);

            return $pdfContent;

        } finally {
            // Clean up temp files
            if (file_exists($tempHtmlFile)) unlink($tempHtmlFile);
            if (file_exists($tempPdfFile)) unlink($tempPdfFile);
        }
    }

    private function convertWithPuppeteer(string $html): string
    {
        $tempHtmlFile = tempnam(sys_get_temp_dir(), 'cert_') . '.html';
        $tempPdfFile = tempnam(sys_get_temp_dir(), 'cert_') . '.pdf';
        $scriptPath = base_path('resources/scripts/puppeteer-generate.cjs');

        try {
            file_put_contents($tempHtmlFile, $html);

            $command = "node \"$scriptPath\" \"$tempHtmlFile\" \"$tempPdfFile\" 2>&1";
            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($tempPdfFile)) {
                throw new \Exception('Puppeteer conversion failed: ' . implode("\n", $output));
            }

            return file_get_contents($tempPdfFile);

        } finally {
            if (file_exists($tempHtmlFile)) unlink($tempHtmlFile);
            if (file_exists($tempPdfFile)) unlink($tempPdfFile);
        }
    }

    private function convertWithChromeHeadless(string $html): string
    {
        $tempHtmlFile = tempnam(sys_get_temp_dir(), 'cert_') . '.html';
        $tempPdfFile = tempnam(sys_get_temp_dir(), 'cert_') . '.pdf';

        try {
            // Write HTML to temp file
            file_put_contents($tempHtmlFile, $html);

            // Find Chrome executable
            $chromeCommands = [
                'google-chrome',
                'chrome',
                'chromium',
                'chromium-browser',
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
            ];

            $chromeCmd = null;
            foreach ($chromeCommands as $command) {
                $output = [];
                $returnVar = 0;
                exec("which \"$command\" 2>/dev/null || test -f \"$command\"", $output, $returnVar);
                if ($returnVar === 0) {
                    $chromeCmd = $command;
                    break;
                }
            }

            if (!$chromeCmd) {
                throw new \Exception('Chrome executable not found');
            }

            // Build Chrome command for A4 landscape certificate
            $command = sprintf(
                '"%s" --headless --disable-gpu --no-sandbox --disable-setuid-sandbox --run-all-compositor-stages-before-draw --virtual-time-budget=5000 --print-to-pdf="%s" --print-to-pdf-no-header --no-margins "file://%s" 2>&1',
                $chromeCmd,
                $tempPdfFile,
                $tempHtmlFile
            );

            Log::debug('PdfCertificateGenerator: Chrome headless command', ['command' => $command]);

            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($tempPdfFile)) {
                throw new \Exception('Chrome headless conversion failed: ' . implode("\n", $output));
            }

            $pdfContent = file_get_contents($tempPdfFile);

            Log::info('PdfCertificateGenerator: Chrome headless conversion successful', [
                'pdf_size' => strlen($pdfContent)
            ]);

            return $pdfContent;

        } finally {
            // Clean up temp files
            if (file_exists($tempHtmlFile)) unlink($tempHtmlFile);
            if (file_exists($tempPdfFile)) unlink($tempPdfFile);
        }
    }

    /**
     * Pre-process HTML to ensure better PDF compatibility
     */
    private function preprocessHtmlForPdf(string $html): string
    {
        // Replace Google Fonts imports with fallback fonts for better PDF compatibility
        $html = str_replace('@import url(\'https://fonts.googleapis.com/css2?family=Playfair+Display', '/* @import url(\'https://fonts.googleapis.com/css2?family=Playfair+Display', $html);
        $html = str_replace('@import url("https://fonts.googleapis.com/css2?family=Playfair+Display', '/* @import url("https://fonts.googleapis.com/css2?family=Playfair+Display', $html);

        // Add fallback font-family declarations
        $html = str_replace('font-family: \'Playfair Display\', serif;', 'font-family: \'Playfair Display\', \'Times New Roman\', serif;', $html);
        $html = str_replace('font-family: \'Inter\', sans-serif;', 'font-family: \'Inter\', \'Arial\', sans-serif;', $html);
        $html = str_replace('font-family: \'Crimson Text\', serif;', 'font-family: \'Crimson Text\', \'Times New Roman\', serif;', $html);

        // Ensure proper margin and page setup
        if (strpos($html, '@page') === false) {
            $pageStyles = '<style>@page { size: A4 landscape; margin: 20mm; }</style>';
            $html = str_replace('<head>', '<head>' . $pageStyles, $html);
        }

        return $html;
    }
}
