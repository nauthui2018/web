<?php

namespace App\Contracts;

use App\Models\Certificate;

interface CertificateTemplateInterface
{
    /**
     * Generate certificate content from template
     */
    public function generate(Certificate $certificate): string;

    /**
     * Get template name
     */
    public function getName(): string;

    /**
     * Get template metadata
     */
    public function getMetadata(): array;

    /**
     * Validate template data
     */
    public function validate(array $data): bool;
}
