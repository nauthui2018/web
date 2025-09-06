<?php

namespace App\Services;

use App\Constants\ErrorCodes;
use App\Contracts\CertificateGeneratorInterface;
use App\Contracts\CertificateTemplateInterface;
use App\Exceptions\AppException;
use App\Models\Certificate;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Services\Certificate\CertificateGeneratorFactory;
use App\Services\Certificate\S3CertificateService;
use App\Services\Certificate\Templates\DefaultCertificateTemplate;
use App\Services\Certificate\Templates\ElegantCertificateTemplate;
use App\Services\Certificate\Templates\ModernCertificateTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateService
{
    protected CertificateRepositoryInterface $certificateRepository;
    protected CertificateGeneratorInterface  $certificateGenerator;
    protected S3CertificateService  $s3Service;

    public function __construct(
        CertificateRepositoryInterface $certificateRepository,
        CertificateGeneratorInterface  $certificateGenerator,
        S3CertificateService $s3Service
    )
    {
        $this->certificateRepository = $certificateRepository;
        $this->certificateGenerator = $certificateGenerator;
        $this->s3Service = $s3Service;
    }

    /**
     * Get all certificates for a user
     */
    public function getUserCertificates(User $user, bool $activeOnly = false): Collection
    {
        return $this->certificateRepository->getUserCertificates($user->id, $activeOnly);
    }

    /**
     * Get certificate by certificate number
     */
    public function getCertificateByNumber(string $certificateNumber): ?Certificate
    {
        return $this->certificateRepository->findByCertificateNumber($certificateNumber);
    }

    /**
     * Verify certificate validity
     */
    public function verifyCertificate(string $certificateNumber): array
    {
        $certificate = $this->getCertificateByNumber($certificateNumber);

        if (!$certificate) {
            return [
                'valid' => false,
                'message' => 'Certificate not found',
                'certificate' => null
            ];
        }

        if (!$certificate->is_active) {
            $reason = $certificate->revoked_at ? 'Certificate has been revoked' : 'Certificate is not valid';
            if ($certificate->is_expired) {
                $reason = 'Certificate has expired';
            }

            return [
                'valid' => false,
                'message' => $reason,
                'certificate' => $certificate
            ];
        }

        return [
            'valid' => true,
            'message' => 'Certificate is valid',
            'certificate' => $certificate
        ];
    }

    /**
     * Revoke a certificate
     */
    public function revokeCertificate(Certificate $certificate, ?string $reason = null): bool
    {
        return $certificate->revoke($reason);
    }

    /**
     * Restore a revoked certificate
     */
    public function restoreCertificate(Certificate $certificate): bool
    {
        return $certificate->restore();
    }

    /**
     * Get certificates that are about to expire
     */
    public function getCertificatesExpiringIn(int $days): Collection
    {
        return $this->certificateRepository->getCertificatesExpiringIn($days);
    }

    /**
     * Get certificate statistics
     */
    public function getCertificateStats(): array
    {
        return $this->certificateRepository->getCertificateStats();
    }

    /**
     * Get tests with most certificates issued
     */
    protected function getTopCertifiedTests(int $limit = 5)
    {
        return Certificate::select('test_id', DB::raw('count(*) as certificate_count'))
                         ->with('test:id,title')
                         ->groupBy('test_id')
                         ->orderByDesc('certificate_count')
                         ->limit($limit)
                         ->get();
    }

    /**
     * Bulk revoke certificates for a test
     */
    public function revokeTestCertificates(Test $test, ?string $reason = null): int
    {
        return $this->certificateRepository->bulkRevokeTestCertificates($test->id, $reason ?? "Test {$test->title} has been modified");
    }

    /**
     * Check if a certificate can be generated for a test attempt
     */
    public function canGenerateCertificate(TestAttempt $testAttempt): bool
    {
        return $testAttempt->canIssueCertificate();
    }

    /**
     * Generate unique certificate number
     */
    public function generateCertificateNumber(): string
    {
        do {
            $certificateNumber = 'CERT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(6)));
        } while ($this->certificateRepository->certificateNumberExists($certificateNumber));

        return $certificateNumber;
    }

    /**
     * Regenerate a certificate with a new certificate number
     */
    public function regenerateCertificate(Certificate $certificate): Certificate
    {
        // Create a new certificate with the same data but new certificate number
        $newCertificate = $certificate->replicate();
        $newCertificate->certificate_number = $this->generateCertificateNumber();
        $newCertificate->issued_at = now();
        $newCertificate->save();

        // Optionally revoke the old certificate
        $certificate->update(['is_revoked' => true]);

        return $newCertificate;
    }

    /**
     * Get user certificates formatted for API response
     */
    public function getUserCertificatesForApi(int $userId, bool $activeOnly = false): array
    {
        $user = User::findOrFail($userId);
        $certificates = $this->getUserCertificates($user, $activeOnly);

        return $certificates->map(function ($certificate) {
            return [
                'id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
                'user_name' => $certificate->user_name,
                'test_title' => $certificate->test_title,
                'score' => $certificate->score,
                'completed_at' => $certificate->completed_at,
                'issued_at' => $certificate->issued_at,
                'expires_at' => $certificate->expires_at,
                'is_valid' => $certificate->is_valid,
                'certificate_template' => $certificate->certificate_template ?? 'default',
                'download_url' => route('certificates.download', $certificate->certificate_number)
            ];
        })->toArray();
    }

    /**
     * Get certificate data formatted for API response
     * @throws AppException
     */
    public function getCertificateForApi(string $certificateNumber): ?array
    {
        $certificate = $this->getCertificateByNumber($certificateNumber);

        if (!$certificate) {
            Log::warning('CertificateController: Certificate not found', [
                'certificate_number' => $certificateNumber
            ]);
            throw new AppException(ErrorCodes::CERTIFICATE_NOT_FOUND, 404);
        }

        if ($certificate->user_id !== Auth::id() || !$certificate->user()->exists()) {
            throw new AppException(ErrorCodes::CERTIFICATE_UNAUTHORIZED_ACCESS, 403);
        }

        return [
            'id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'user_name' => $certificate->user_name,
            'test_title' => $certificate->test_title,
            'score' => $certificate->score,
            'completed_at' => $certificate->completed_at,
            'issued_at' => $certificate->issued_at,
            'expires_at' => $certificate->expires_at,
            'is_valid' => $certificate->is_valid,
            'certificate_template' => $certificate->certificate_template ?? 'default',
            'download_url' => route('certificates.download', $certificate->certificate_number)
        ];
    }

    /**
     * Download certificate as PDF
     * @throws \Exception
     */
    public function downloadCertificatePdf(Certificate $certificate, string $templateType = 'default'): StreamedResponse
    {
        // Get template instance
        $template = $this->getTemplateInstance($templateType);

        // Check if file exists on S3, if not generate and save it
        if (!$this->s3Service->certificateExists($certificate, 'pdf', $templateType)) {
            // Generate PDF using the injected generator
            $pdfContent = $this->certificateGenerator->generateWithTemplate($certificate, $template);

            // Save to S3
            $this->s3Service->uploadCertificate($certificate, $pdfContent, 'pdf', $templateType);
        }

        // Stream download using S3CertificateService
        return $this->s3Service->streamDownloadCertificate($certificate, 'pdf', $templateType, [
            'Content-Type' => 'application/pdf'
        ]);
    }

    /**
     * Download certificate as PDF directly without saving to S3
     * @throws \Exception
     */
    public function downloadCertificatePdfDirect(Certificate $certificate, string $templateType = 'default'): StreamedResponse
    {
        // Get template instance
        $template = $this->getTemplateInstance($templateType);

        // Generate PDF using the injected generator
        $pdfContent = $this->certificateGenerator->generateWithTemplate($certificate, $template);

        // Create filename
        $filename = "certificate-{$certificate->certificate_number}.pdf";

        // Return streamed response directly without saving to S3
        return response()->stream(
            function () use ($pdfContent) {
                echo $pdfContent;
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Content-Length' => strlen($pdfContent),
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]
        );
    }

    /**
     * Download certificate as HTML
     */
    public function downloadCertificateHtml(Certificate $certificate, string $templateType = 'default'): StreamedResponse
    {
        // Get template instance
        $template = $this->getTemplateInstance($templateType);

        // Check if file exists on S3, if not generate and save it
        if (!$this->s3Service->certificateExists($certificate, 'html', $templateType)) {
            // Generate HTML using the HTML generator
            $generator = CertificateGeneratorFactory::create('html');
            $htmlContent = $generator->generateWithTemplate($certificate, $template);

            // Save to S3
            $this->s3Service->uploadCertificate($certificate, $htmlContent, 'html', $templateType);
        }

        // Stream download using S3CertificateService
        return $this->s3Service->streamDownloadCertificate($certificate, 'html', $templateType, [
            'Content-Type' => 'text/html'
        ]);
    }

    /**
     * Download certificate as HTML directly without saving to S3
     * @throws \Exception
     */
    public function downloadCertificateHtmlDirect(Certificate $certificate, string $templateType = 'default'): StreamedResponse
    {
        // Get template instance
        $template = $this->getTemplateInstance($templateType);

        // Generate HTML directly using template
        $htmlContent = $template->generate($certificate);

        // Create filename
        $filename = "certificate-{$certificate->certificate_number}.html";

        // Return streamed response directly without saving to S3
        return response()->stream(
            function () use ($htmlContent) {
                echo $htmlContent;
            },
            200,
            [
                'Content-Type' => 'text/html; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Content-Length' => strlen($htmlContent),
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]
        );
    }

    /**
     * Verify certificate and return API formatted response
     */
    public function verifyCertificateForApi(string $certificateNumber): array
    {
        $certificate = $this->getCertificateByNumber($certificateNumber);

        if (!$certificate) {
            return [
                'verified' => false,
                'message' => 'Certificate not found',
                'data' => null
            ];
        }

        $isValid = $certificate->is_valid;

        return [
            'verified' => $isValid,
            'message' => $isValid ? 'Certificate is valid' : 'Certificate is not valid',
            'data' => [
                'certificate_number' => $certificate->certificate_number,
                'user_name' => $certificate->user_name,
                'test_title' => $certificate->test_title,
                'score' => $certificate->score,
                'completed_at' => $certificate->completed_at,
                'issued_at' => $certificate->issued_at,
                'expires_at' => $certificate->expires_at,
                'is_valid' => $isValid,
                'status' => $isValid ? 'valid' : ($certificate->is_revoked ? 'revoked' : 'expired')
            ]
        ];
    }

    /**
     * Regenerate certificate and return API formatted response
     * @throws AppException
     */
    public function regenerateCertificateForApi(string $certificateNumber): array
    {
        $certificate = $this->getCertificateByNumber($certificateNumber);

        if (!$certificate) {
            Log::warning('CertificateController: Certificate not found', [
                'certificate_number' => $certificateNumber
            ]);
            throw new AppException(ErrorCodes::CERTIFICATE_NOT_FOUND, 404);
        }

        $newCertificate = $this->regenerateCertificate($certificate);

        return [
            'old_certificate_number' => $certificate->certificate_number,
            'new_certificate_number' => $newCertificate->certificate_number,
            'download_url' => asset("storage/certificates/{$newCertificate->certificate_number}.pdf"),
            'verification_url' => route('certificates.verify', $newCertificate->certificate_number)
        ];
    }

    /**
     * Get available certificate templates
     */
    public function getAvailableTemplates(): array
    {
        return [
            [
                'name' => 'default',
                'title' => 'Default Certificate Template',
                'description' => 'Simple and clean certificate template',
                'preview_url' => null
            ],
            [
                'name' => 'modern',
                'title' => 'Modern Certificate Template',
                'description' => 'Contemporary design with clean lines and modern typography',
                'preview_url' => null
            ],
            [
                'name' => 'elegant',
                'title' => 'Elegant Certificate Template',
                'description' => 'Sophisticated design with decorative elements and refined typography',
                'preview_url' => null
            ]
        ];
    }

    /**
     * Get template instance by type
     */
    private function getTemplateInstance(string $type): CertificateTemplateInterface
    {
        return match ($type) {
            'modern' => new ModernCertificateTemplate(),
            'elegant' => new ElegantCertificateTemplate(),
            default => new DefaultCertificateTemplate()
        };
    }

    /**
     * Validate and normalize download format
     * @throws AppException
     */
    public function validateAndNormalizeFormat(?string $format): string
    {
        // Handle null or empty format - default to PDF
        if (empty($format) || is_null($format)) {
            $format = 'pdf';
        } else {
            $format = strtolower($format);
        }

        // Normalize format
        if ($format === 'jpeg') {
            $format = 'jpg';
        }

        // Validate format
        if (!in_array($format, ['pdf', 'html', 'png', 'jpg'])) {
            throw new ApiException('Invalid format. Supported formats: pdf, html, png, jpg', 400);
        }

        return $format;
    }

    /**
     * Validate template type
     * @throws AppException
     */
    public function validateTemplate(string $templateType): void
    {
        if (!in_array($templateType, ['default', 'modern', 'elegant'])) {
            throw new AppException(ErrorCodes::CERTIFICATE_UNSUPPORTED_TEMPLATE, 400);
        }
    }

    /**
     * Check if user has admin permissions
     * @throws AppException
     */
    public function checkAdminPermission(User $user): void
    {
        if (!$user->isAdmin()) {
            throw new AppException(ErrorCodes::ACCESS_DENIED, 403);
        }
    }

    /**
     * Process download request with validation
     * @throws AppException
     * @throws \Exception
     */
    public function processDownloadRequest(string $certificateNumber, ?string $format, string $templateType): StreamedResponse
    {
        // Set timeout
        set_time_limit(180);
        ini_set('max_execution_time', 180);

        // Increase memory limit
        ini_set('memory_limit', '512M');

        // Validate and normalize format
        $format = $this->validateAndNormalizeFormat($format);

        // Validate template
        $this->validateTemplate($templateType);

        // Check if certificate exists and is valid
        $certificate = $this->getCertificateByNumber($certificateNumber);

        if (!$certificate || !$certificate->is_valid) {
            throw new AppException(ErrorCodes::CERTIFICATE_NOT_FOUND, 404);
        }

        // Route to appropriate service method based on format
        return match ($format) {
            'pdf' => $this->downloadCertificatePdfDirect($certificate, $templateType),
            'html' => $this->downloadCertificateHtmlDirect($certificate, $templateType),
            default => throw new AppException(ErrorCodes::CERTIFICATE_UNSUPPORTED_FORMAT, 400)
        };
    }
}
