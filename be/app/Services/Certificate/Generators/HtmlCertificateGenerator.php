<?php

namespace App\Services\Certificate\Generators;

use App\Contracts\CertificateTemplateInterface;
use App\Models\Certificate;
use App\Services\Certificate\S3CertificateService;

class HtmlCertificateGenerator extends BaseCertificateGenerator
{
    protected string $defaultFormat = 'html';
    protected array $supportedFormats = ['html'];

    public function __construct(S3CertificateService $s3Service)
    {
        parent::__construct($s3Service);
    }

    public function generate(Certificate $certificate, string $format = 'html'): string
    {
        $this->validateFormat($format);

        // Get the default template service and generate HTML
        $templateService = app(CertificateTemplateInterface::class);
        return $templateService->generate($certificate);
    }

    public function generateWithTemplate(Certificate $certificate, CertificateTemplateInterface $template): string
    {
        return $template->generate($certificate);
    }
}
