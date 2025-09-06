<?php

namespace App\Services\Certificate;

use App\Models\Certificate;
use App\Services\S3Service;
use Exception;
use Illuminate\Support\Facades\Log;

class S3CertificateService extends S3Service
{
    public function __construct()
    {
        parent::__construct('certificates');
    }

    /**
     * Upload certificate file with standardized metadata
     * @throws Exception
     */
    public function uploadCertificate(Certificate $certificate, string $content, string $format, string $template = 'default'): string
    {
        Log::info('S3CertificateService: Starting certificate upload', [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'user_id' => $certificate->user_id,
            'format' => $format,
            'template' => $template,
            'content_size' => strlen($content)
        ]);

        // Validate format
        if (!$this->isCertificateFormatSupported($format)) {
            Log::error('S3CertificateService: Unsupported format', [
                'certificate_id' => $certificate->id,
                'format' => $format,
                'supported_formats' => $this->getSupportedCertificateFormats()
            ]);
            throw new Exception("Unsupported certificate format: {$format}. Supported formats: " . implode(', ', $this->getSupportedCertificateFormats()));
        }

        $filename = $this->generateCertificateFilename($certificate, $format, $template);

        Log::info('S3CertificateService: Generated filename', [
            'certificate_id' => $certificate->id,
            'filename' => $filename,
            'format' => $format,
            'template' => $template
        ]);

        $metadata = [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'user_id' => $certificate->user_id,
            'test_id' => $certificate->test_id,
            'format' => $format,
            'template' => $template,
            'generated_at' => now()->toISOString(),
            'content_type' => $this->getCertificateContentType($format)
        ];

        $tags = [
            'certificate_id' => $certificate->id,
            'format' => $format,
            'template' => $template,
            'user_id' => $certificate->user_id
        ];

        Log::debug('S3CertificateService: Upload metadata prepared', [
            'certificate_id' => $certificate->id,
            'metadata_keys' => array_keys($metadata),
            'tags_keys' => array_keys($tags)
        ]);

        try {
            $this->upload($filename, $content, [
                'visibility' => 'private',
                'metadata' => $metadata,
                'tags' => $tags
            ]);

            Log::info('S3CertificateService: Certificate uploaded successfully', [
                'certificate_id' => $certificate->id,
                'filename' => $filename,
                'content_size' => strlen($content),
                'format' => $format,
                'template' => $template
            ]);

        } catch (Exception $e) {
            Log::error('S3CertificateService: Certificate upload failed', [
                'certificate_id' => $certificate->id,
                'filename' => $filename,
                'format' => $format,
                'template' => $template,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        Log::info("Certificate uploaded to S3", [
            'certificate_id' => $certificate->id,
            'filename' => $filename,
            'format' => $format,
            'template' => $template,
            'size' => strlen($content)
        ]);

        return $filename;
    }

    /**
     * Download certificate file
     */
    public function downloadCertificate(Certificate $certificate, string $format, string $template = 'default'): string
    {
        $filename = $this->generateCertificateFilename($certificate, $format, $template);
        return $this->download($filename);
    }

    /**
     * Check if certificate file exists
     */
    public function certificateExists(Certificate $certificate, string $format, string $template = 'default'): bool
    {
        $filename = $this->generateCertificateFilename($certificate, $format, $template);
        return $this->exists($filename);
    }

    /**
     * Delete certificate file
     */
    public function deleteCertificate(Certificate $certificate, string $format, string $template = 'default'): bool
    {
        $filename = $this->generateCertificateFilename($certificate, $format, $template);
        return $this->delete($filename);
    }

    /**
     * Delete all files for a certificate
     */
    public function deleteAllCertificateFiles(Certificate $certificate): array
    {
        $certificateDir = "{$this->getCertificatesFolder()}/{$certificate->certificate_number}";
        $allFiles = $this->listAllFiles($certificateDir);

        if (empty($allFiles)) {
            return ['deleted' => 0, 'files' => []];
        }

        $results = $this->deleteMultiple($allFiles);

        Log::info("Deleted all certificate files", [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'files_deleted' => count($allFiles)
        ]);

        return [
            'deleted' => count($allFiles),
            'files' => $allFiles,
            'results' => $results
        ];
    }

    /**
     * Get certificate download URL
     */
    public function getCertificateDownloadUrl(Certificate $certificate, string $format, string $template = 'default'): string
    {
        $filename = $this->generateCertificateFilename($certificate, $format, $template);

        return route('certificates.download', [
            'certificateNumber' => $certificate->certificate_number,
            'format' => $format,
            'template' => $template
        ]);
    }

    /**
     * Stream download certificate (for large files)
     */
    public function streamDownloadCertificate(Certificate $certificate, string $format, string $template = 'default', array $headers = [])
    {
        $filename = $this->generateCertificateFilename($certificate, $format, $template);
        $downloadName = "certificate_{$certificate->certificate_number}.{$format}";

        return $this->streamDownload($filename, $downloadName, $headers);
    }

    /**
     * List all certificate files for a certificate
     */
    public function listCertificateFiles(Certificate $certificate): array
    {
        $certificateDir = "{$this->getCertificatesFolder()}/{$certificate->certificate_number}";
        return $this->listAllFiles($certificateDir);
    }

    /**
     * Get certificate folder statistics
     */
    public function getCertificateStats(Certificate $certificate): array
    {
        $certificateDir = "{$this->getCertificatesFolder()}/{$certificate->certificate_number}";
        return $this->getBucketInfo(null, $certificateDir);
    }

    /**
     * Copy certificate files (useful for creating backups or duplicates)
     */
    public function copyCertificateFiles(Certificate $fromCertificate, Certificate $toCertificate): array
    {
        $certificatesFolder = $this->getCertificatesFolder();
        $fromDir = "{$certificatesFolder}/{$fromCertificate->certificate_number}";
        $toDir = "{$certificatesFolder}/{$toCertificate->certificate_number}";

        $sourceFiles = $this->listAllFiles($fromDir);
        $results = [];

        foreach ($sourceFiles as $sourceFile) {
            $relativePath = str_replace($fromDir . '/', '', $sourceFile);
            $targetFile = $toDir . '/' . $relativePath;

            try {
                $success = $this->copy($sourceFile, $targetFile);
                $results[$sourceFile] = [
                    'success' => $success,
                    'target' => $targetFile,
                    'error' => null
                ];
            } catch (Exception $e) {
                $results[$sourceFile] = [
                    'success' => false,
                    'target' => $targetFile,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Generate standardized certificate filename
     */
    private function generateCertificateFilename(Certificate $certificate, string $format, string $template = 'default'): string
    {
        return S3FolderManager::getCertificatePath($certificate->certificate_number, $format, $template);
    }

    /**
     * Get content type for certificate format
     */
    private function getCertificateContentType(string $format): string
    {
        return match($format) {
            'pdf' => 'application/pdf',
            'html' => 'text/html',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream'
        };
    }

    /**
     * Archive old certificates (move to archive folder)
     */
    public function archiveCertificate(Certificate $certificate, string $archiveReason = 'manual'): array
    {
        $sourceDir = "{$this->getCertificatesFolder()}/{$certificate->certificate_number}";
        $archiveDir = S3FolderManager::getArchivePath($certificate->certificate_number);

        $sourceFiles = $this->listAllFiles($sourceDir);
        $results = [];

        foreach ($sourceFiles as $sourceFile) {
            $relativePath = str_replace($sourceDir . '/', '', $sourceFile);
            $archiveFile = $archiveDir . '/' . $relativePath;

            try {
                $success = $this->move($sourceFile, $archiveFile);
                $results[$sourceFile] = [
                    'success' => $success,
                    'archived_to' => $archiveFile,
                    'error' => null
                ];
            } catch (Exception $e) {
                $results[$sourceFile] = [
                    'success' => false,
                    'archived_to' => $archiveFile,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Add archive metadata
        $metadataFile = $archiveDir . '/archive_info.json';
        $archiveInfo = [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'archived_at' => now()->toISOString(),
            'archive_reason' => $archiveReason,
            'files_archived' => count($sourceFiles),
            'original_location' => $sourceDir
        ];

        $this->upload($metadataFile, json_encode($archiveInfo, JSON_PRETTY_PRINT), [
            'visibility' => 'private',
            'metadata' => [
                'type' => 'archive_metadata',
                'certificate_id' => $certificate->id
            ]
        ]);

        Log::info("Certificate archived", [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'files_archived' => count($sourceFiles),
            'archive_reason' => $archiveReason
        ]);

        return [
            'archived' => true,
            'files_count' => count($sourceFiles),
            'archive_location' => $archiveDir,
            'metadata_file' => $metadataFile,
            'results' => $results
        ];
    }

    /**
     * Validate if a format is supported for certificates
     */
    public function isCertificateFormatSupported(string $format): bool
    {
        return S3FolderManager::isFormatSupported('certificates', $format);
    }

    /**
     * Get all supported certificate formats
     */
    public function getSupportedCertificateFormats(): array
    {
        return S3FolderManager::getSupportedFormats('certificates');
    }

    /**
     * Get the configured certificates folder from environment
     */
    private function getCertificatesFolder(): string
    {
        return config('s3folders.certificates', 'certificates');
    }
}
