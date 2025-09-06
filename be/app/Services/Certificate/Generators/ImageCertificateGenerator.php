<?php

namespace App\Services\Certificate\Generators;

use App\Contracts\CertificateTemplateInterface;
use App\Models\Certificate;
use Illuminate\Support\Facades\Log;

class ImageCertificateGenerator extends BaseCertificateGenerator
{
    protected string $defaultFormat = 'png';
    protected array $supportedFormats = ['png', 'jpg', 'jpeg'];

    public function generate(Certificate $certificate, string $format = 'png'): string
    {
        Log::info('ImageCertificateGenerator: Starting image generation', [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'format' => $format
        ]);

        $this->validateFormat($format);

        // Get the default template service and generate HTML
        $templateService = app(\App\Contracts\CertificateTemplateInterface::class);
        
        Log::debug('ImageCertificateGenerator: Template service loaded', [
            'certificate_id' => $certificate->id,
            'template_class' => get_class($templateService)
        ]);

        $html = $templateService->generate($certificate);

        Log::debug('ImageCertificateGenerator: HTML generated', [
            'certificate_id' => $certificate->id,
            'html_length' => strlen($html)
        ]);

        $imageContent = $this->convertHtmlToImage($html, $format);

        Log::info('ImageCertificateGenerator: Image generation completed', [
            'certificate_id' => $certificate->id,
            'format' => $format,
            'image_size' => strlen($imageContent)
        ]);

        return $imageContent;
    }

    public function generateWithTemplate(Certificate $certificate, CertificateTemplateInterface $template): string
    {
        Log::info('ImageCertificateGenerator: Starting image generation with template', [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'template_class' => get_class($template),
            'default_format' => $this->defaultFormat
        ]);

        $html = $template->generate($certificate);
        
        Log::debug('ImageCertificateGenerator: HTML generated from template', [
            'certificate_id' => $certificate->id,
            'template_class' => get_class($template),
            'html_length' => strlen($html)
        ]);

        $imageContent = $this->convertHtmlToImage($html, $this->defaultFormat);

        Log::info('ImageCertificateGenerator: Image generation with template completed', [
            'certificate_id' => $certificate->id,
            'template_class' => get_class($template),
            'format' => $this->defaultFormat,
            'image_size' => strlen($imageContent)
        ]);

        return $imageContent;
    }

    public function save(Certificate $certificate, string $content, ?string $format = null): string
    {
        $format = $format ?? $this->defaultFormat;
        $this->validateFormat($format);

        Log::info('ImageCertificateGenerator: Starting certificate save', [
            'certificate_id' => $certificate->id,
            'format' => $format,
            'content_size' => strlen($content)
        ]);

        try {
            $filename = $this->s3Service->uploadCertificate($certificate, $content, $format, 'default');

            Log::info('ImageCertificateGenerator: Certificate saved successfully', [
                'certificate_id' => $certificate->id,
                'format' => $format,
                'filename' => $filename,
                'content_size' => strlen($content)
            ]);

            return $filename;

        } catch (\Exception $e) {
            Log::error('ImageCertificateGenerator: Failed to save certificate to S3', [
                'certificate_id' => $certificate->id,
                'format' => $format,
                'content_size' => strlen($content),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function convertHtmlToImage(string $html, string $format): string
    {
        Log::info('ImageCertificateGenerator: Starting HTML to image conversion', [
            'format' => $format,
            'html_length' => strlen($html)
        ]);

        // Method 1: Using wkhtmltoimage (requires wkhtmltopdf installed)
        if ($this->hasWkhtmltoimage()) {
            Log::debug('ImageCertificateGenerator: Using wkhtmltoimage method');
            return $this->convertWithWkhtmltoimage($html, $format);
        }

        // Method 2: Using Puppeteer via Node.js (requires Node.js and Puppeteer)
        if ($this->hasPuppeteer()) {
            Log::debug('ImageCertificateGenerator: Using Puppeteer method');
            return $this->convertWithPuppeteer($html, $format);
        }

        // Method 3: Using Canvas/HTML5 (fallback method)
        Log::warning('ImageCertificateGenerator: Using fallback Canvas method', [
            'format' => $format,
            'reason' => 'No advanced conversion tools available'
        ]);
        
        return $this->convertWithCanvas($html, $format);
    }

    private function hasWkhtmltoimage(): bool
    {
        $output = [];
        $returnVar = 0;
        exec('which wkhtmltoimage 2>/dev/null', $output, $returnVar);
        return $returnVar === 0;
    }

    private function hasPuppeteer(): bool
    {
        $output = [];
        $returnVar = 0;
        exec('which node 2>/dev/null', $output, $returnVar);
        if ($returnVar !== 0) return false;

        // Check if puppeteer is available in the project directory
        $projectPath = base_path();
        exec("cd " . escapeshellarg($projectPath) . " && node -e \"require('puppeteer')\" 2>/dev/null", $output, $returnVar);
        return $returnVar === 0;
    }

    private function convertWithWkhtmltoimage(string $html, string $format): string
    {
        Log::info('ImageCertificateGenerator: Starting wkhtmltoimage conversion', [
            'format' => $format,
            'html_length' => strlen($html)
        ]);

        $tempHtmlFile = tempnam(sys_get_temp_dir(), 'cert_') . '.html';
        $tempImageFile = tempnam(sys_get_temp_dir(), 'cert_') . '.' . $format;

        try {
            // Write HTML to temp file
            file_put_contents($tempHtmlFile, $html);
            
            Log::debug('ImageCertificateGenerator: HTML written to temp file', [
                'temp_html_file' => $tempHtmlFile,
                'format' => $format
            ]);

            // Convert to image using wkhtmltoimage
            $command = sprintf(
                'wkhtmltoimage --format %s --width 1200 --height 800 --quality 100 %s %s 2>/dev/null',
                $format,
                escapeshellarg($tempHtmlFile),
                escapeshellarg($tempImageFile)
            );

            Log::debug('ImageCertificateGenerator: Executing wkhtmltoimage command', [
                'command' => $command,
                'format' => $format
            ]);

            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($tempImageFile)) {
                Log::error('ImageCertificateGenerator: wkhtmltoimage conversion failed', [
                    'return_code' => $returnVar,
                    'output' => $output,
                    'command' => $command,
                    'temp_image_exists' => file_exists($tempImageFile)
                ]);
                throw new \Exception('Failed to convert HTML to image using wkhtmltoimage');
            }

            $imageContent = file_get_contents($tempImageFile);

            Log::info('ImageCertificateGenerator: wkhtmltoimage conversion completed', [
                'format' => $format,
                'image_size' => strlen($imageContent),
                'return_code' => $returnVar
            ]);

            return $imageContent;
        } finally {
            // Clean up temp files
            if (file_exists($tempHtmlFile)) {
                unlink($tempHtmlFile);
                Log::debug('ImageCertificateGenerator: Cleaned up temp HTML file', [
                    'file' => $tempHtmlFile
                ]);
            }
            if (file_exists($tempImageFile)) {
                unlink($tempImageFile);
                Log::debug('ImageCertificateGenerator: Cleaned up temp image file', [
                    'file' => $tempImageFile
                ]);
            }
        }
    }

    private function convertWithPuppeteer(string $html, string $format): string
    {
        $tempHtmlFile = tempnam(sys_get_temp_dir(), 'cert_') . '.html';
        $tempImageFile = tempnam(sys_get_temp_dir(), 'cert_') . '.' . $format;
        $tempScriptFile = tempnam(sys_get_temp_dir(), 'puppeteer_') . '.js';

        try {
            // Write HTML to temp file
            file_put_contents($tempHtmlFile, $html);

            // Create Puppeteer script with proper module path
            $projectPath = base_path();
            $puppeteerScript = "
                const path = require('path');
                process.chdir('{$projectPath}');
                const puppeteer = require('puppeteer');
                (async () => {
                    try {
                        const browser = await puppeteer.launch({
                            headless: true,
                            args: ['--no-sandbox', '--disable-setuid-sandbox']
                        });
                        const page = await browser.newPage();
                        await page.setViewport({width: 1200, height: 800});
                        await page.goto('file://" . $tempHtmlFile . "', {waitUntil: 'networkidle0'});
                        await page.screenshot({
                            path: '" . $tempImageFile . "',
                            type: '" . ($format === 'jpg' ? 'jpeg' : $format) . "'" .
                            ($format === 'png' ? '' : ', quality: 100') . ",
                            fullPage: true
                        });
                        await browser.close();
                        console.log('Screenshot created successfully');
                    } catch (error) {
                        console.error('Puppeteer error:', error.message);
                        process.exit(1);
                    }
                })();
            ";

            file_put_contents($tempScriptFile, $puppeteerScript);

            // Execute with error output captured from project directory
            $output = [];
            $command = "cd " . escapeshellarg($projectPath) . " && node " . escapeshellarg($tempScriptFile) . " 2>&1";
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                $errorOutput = implode("\n", $output);
                throw new \Exception("Puppeteer execution failed: {$errorOutput}");
            }

            if (!file_exists($tempImageFile)) {
                throw new \Exception('Image file was not created by Puppeteer');
            }

            $imageContent = file_get_contents($tempImageFile);

            if (empty($imageContent)) {
                throw new \Exception('Generated image file is empty');
            }

            return $imageContent;
        } finally {
            // Clean up temp files
            if (file_exists($tempHtmlFile)) unlink($tempHtmlFile);
            if (file_exists($tempImageFile)) unlink($tempImageFile);
            if (file_exists($tempScriptFile)) unlink($tempScriptFile);
        }
    }

    private function convertWithCanvas(string $html, string $format): string
    {
        // Basic fallback - create a simple image with certificate text
        // This is a simplified version - in real implementation you'd want to use
        // a proper HTML to image conversion library

        $width = 1200;
        $height = 800;

        // Create image
        $image = imagecreatetruecolor($width, $height);

        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $blue = imagecolorallocate($image, 70, 105, 189);

        // Fill background
        imagefill($image, 0, 0, $white);

        // Add border
        imagerectangle($image, 50, 50, $width - 50, $height - 50, $blue);
        imagerectangle($image, 55, 55, $width - 55, $height - 55, $blue);

        // Add text (simplified)
        $font_size = 24;
        imagestring($image, $font_size, 200, 150, 'CERTIFICATE', $blue);
        imagestring($image, 3, 250, 200, 'of Completion', $black);

        // Note: This is a very basic implementation
        // In production, you'd want to parse the HTML and render it properly

        // Capture image content
        ob_start();
        switch ($format) {
            case 'png':
                imagepng($image);
                break;
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, null, 100);
                break;
            default:
                imagepng($image);
        }
        $imageContent = ob_get_contents();
        ob_end_clean();

        // Clean up
        imagedestroy($image);

        return $imageContent;
    }
}
