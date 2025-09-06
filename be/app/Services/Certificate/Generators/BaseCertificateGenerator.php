<?php

namespace App\Services\Certificate\Generators;

use App\Contracts\CertificateGeneratorInterface;
use App\Models\Certificate;
use App\Services\Certificate\S3CertificateService;
use Illuminate\Support\Facades\Log;

abstract class BaseCertificateGenerator implements CertificateGeneratorInterface
{
    protected S3CertificateService $s3Service;
    protected string $defaultFormat;
    protected array $supportedFormats;

    public function __construct(S3CertificateService $s3Service)
    {
        $this->s3Service = $s3Service;
    }

    public function getSupportedFormats(): array
    {
        return $this->supportedFormats;
    }

    public function getDefaultFormat(): string
    {
        return $this->defaultFormat;
    }

    public function getDownloadUrl(Certificate $certificate, string $template = 'default'): string
    {
        // Always use route-based download for consistency
        return route('certificates.download', [
            'certificateNumber' => $certificate->certificate_number,
            'format' => $this->defaultFormat,
            'template' => $template
        ]);
    }

    /**
     * Save certificate content to S3
     */
    public function save(Certificate $certificate, string $content, string $format = null): string
    {
        $format = $format ?? $this->defaultFormat;

        try {
            return $this->s3Service->uploadCertificate($certificate, $content, $format, 'default');

        } catch (\Exception $e) {
            Log::error("Failed to save {$format} certificate to S3", [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Validate format is supported
     */
    protected function validateFormat(string $format): void
    {
        if (!in_array($format, $this->supportedFormats)) {
            throw new \InvalidArgumentException("Unsupported format: {$format}");
        }
    }

    /**
     * Check if certificate file exists in S3
     */
    protected function fileExists(Certificate $certificate, string $format, string $template = 'default'): bool
    {
        return $this->s3Service->certificateExists($certificate, $format, $template);
    }

    /**
     * Get certificate file from S3
     */
    protected function getFileContent(Certificate $certificate, string $format, string $template = 'default'): string
    {
        return $this->s3Service->downloadCertificate($certificate, $format, $template);
    }

    /**
     * Delete certificate file from S3
     */
    protected function deleteFile(Certificate $certificate, string $format, string $template = 'default'): bool
    {
        return $this->s3Service->deleteCertificate($certificate, $format, $template);
    }
}
