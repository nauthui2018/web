<?php

namespace App\Contracts;

use App\Models\Certificate;

interface CertificateGeneratorInterface
{
    /**
     * Generate certificate document (PDF, HTML, etc.)
     */
    public function generate(Certificate $certificate, string $format = 'pdf'): string;

    /**
     * Generate certificate with specific template
     */
    public function generateWithTemplate(Certificate $certificate, CertificateTemplateInterface $template): string;

    /**
     * Get supported formats
     */
    public function getSupportedFormats(): array;

    /**
     * Save certificate to storage
     */
    public function save(Certificate $certificate, string $content, string $format = 'pdf'): string;

    /**
     * Get certificate download URL
     */
    public function getDownloadUrl(Certificate $certificate): string;
}
