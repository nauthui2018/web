<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseHelper;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    protected CertificateService $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Get certificates for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $certificates = $this->certificateService->getUserCertificatesForApi($user->id);

        Log::info('CertificateController: Certificates retrieved', [
            'user_id' => $user->id,
            'certificate_count' => count($certificates)
        ]);

        return ResponseHelper::success($certificates, 'Certificates retrieved successfully');
    }

    /**
     * Get a specific certificate
     * @throws AppException
     */
    public function show(string $certificateNumber): JsonResponse
    {
        Log::info('CertificateController: Getting specific certificate', [
            'certificate_number' => $certificateNumber
        ]);

        $certificateData = $this->certificateService->getCertificateForApi($certificateNumber);

        Log::info('CertificateController: Certificate retrieved successfully', [
            'certificate_number' => $certificateNumber,
            'certificate_id' => $certificateData['id'] ?? null
        ]);

        return ResponseHelper::success($certificateData, 'Certificate retrieved successfully');
    }

    /**
     * Download certificate in various formats (PDF, HTML)
     * @throws AppException
     */
    public function download(string $certificateNumber, Request $request): StreamedResponse
    {
        $templateType = $request->query('template', 'default');
        $format = $request->query('format', 'pdf');

        return $this->certificateService->processDownloadRequest($certificateNumber, $format, $templateType);
    }

    /**
     * Regenerate certificate (admin only)
     * @throws AppException
     */
    public function regenerate(string $certificateNumber, Request $request): JsonResponse
    {
        // Check admin permission
        $this->certificateService->checkAdminPermission($request->user());

        $result = $this->certificateService->regenerateCertificateForApi($certificateNumber);

        return ResponseHelper::success($result, 'Certificate regenerated successfully');
    }

    /**
     * Get available certificate templates
     */
    public function templates(): JsonResponse
    {
        $templates = $this->certificateService->getAvailableTemplates();

        return ResponseHelper::success($templates, 'Templates retrieved successfully');
    }
}
