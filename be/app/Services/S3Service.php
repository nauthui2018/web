<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class S3Service
{
    private string $defaultDisk;

    public function __construct(string $defaultDisk = 'certificates')
    {
        $this->defaultDisk = $defaultDisk;
    }

    /**
     * Upload a file to S3
     */
    public function upload(string $path, string $content, array $options = [], string $disk = null): bool
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            $visibility = $options['visibility'] ?? 'private';
            $metadata = $options['metadata'] ?? [];
            $tags = $options['tags'] ?? [];

            $putOptions = [
                'visibility' => $visibility,
            ];

            // Add metadata if provided
            if (!empty($metadata)) {
                $putOptions['Metadata'] = $metadata;
            }

            // Add tags if provided
            if (!empty($tags)) {
                $putOptions['Tagging'] = http_build_query($tags);
            }

            $result = Storage::disk($disk)->put($path, $content, $putOptions);

            if ($result) {
                Log::info("S3 Upload Success", [
                    'disk' => $disk,
                    'path' => $path,
                    'size' => strlen($content),
                    'visibility' => $visibility,
                    'metadata_count' => count($metadata),
                    'tags_count' => count($tags)
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("S3 Upload Failed", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception("Failed to upload file to S3: " . $e->getMessage());
        }
    }

    /**
     * Download a file from S3
     */
    public function download(string $path, string $disk = null): string
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            if (!$this->exists($path, $disk)) {
                throw new \RuntimeException("File not found in S3: {$path}");
            }

            $content = Storage::disk($disk)->get($path);

            Log::info("S3 Download Success", [
                'disk' => $disk,
                'path' => $path,
                'size' => strlen($content)
            ]);

            return $content;

        } catch (Exception $e) {
            Log::error("S3 Download Failed", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException("Failed to download file from S3: " . $e->getMessage());
        }
    }

    /**
     * Stream download a file from S3 (for large files)
     */
    public function streamDownload(string $path, string $downloadName = null, array $headers = [], string $disk = null): StreamedResponse
    {
        $disk = $disk ?? $this->defaultDisk;
        $downloadName = $downloadName ?? basename($path);

        try {
            if (!$this->exists($path, $disk)) {
                throw new Exception("File not found in S3: {$path}");
            }

            $defaultHeaders = [
                'Content-Type' => $this->getMimeType($path),
                'Content-Disposition' => "attachment; filename=\"{$downloadName}\""
            ];

            $mergedHeaders = array_merge($defaultHeaders, $headers);

            return response()->streamDownload(function () use ($path, $disk) {
                $stream = Storage::disk($disk)->readStream($path);
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
            }, $downloadName, $mergedHeaders);

        } catch (Exception $e) {
            Log::error("S3 Stream Download Failed", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to stream download file from S3: " . $e->getMessage());
        }
    }

    /**
     * Check if a file exists in S3
     */
    public function exists(string $path, string $disk = null): bool
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            return Storage::disk($disk)->exists($path);
        } catch (Exception $e) {
            Log::error("S3 Exists Check Failed", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete a file from S3
     */
    public function delete(string $path, string $disk = null): bool
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            $result = Storage::disk($disk)->delete($path);

            if ($result) {
                Log::info("S3 Delete Success", [
                    'disk' => $disk,
                    'path' => $path
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("S3 Delete Failed", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to delete file from S3: " . $e->getMessage());
        }
    }

    /**
     * Delete multiple files from S3
     */
    public function deleteMultiple(array $paths, string $disk = null): array
    {
        $disk = $disk ?? $this->defaultDisk;
        $results = [];

        try {
            $result = Storage::disk($disk)->delete($paths);

            foreach ($paths as $path) {
                $results[$path] = $result;
            }

            Log::info("S3 Bulk Delete", [
                'disk' => $disk,
                'file_count' => count($paths),
                'success' => $result
            ]);

            return $results;

        } catch (Exception $e) {
            Log::error("S3 Bulk Delete Failed", [
                'disk' => $disk,
                'file_count' => count($paths),
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to delete files from S3: " . $e->getMessage());
        }
    }

    /**
     * Get file size
     */
    public function size(string $path, string $disk = null): int
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            if (!$this->exists($path, $disk)) {
                throw new Exception("File not found in S3: {$path}");
            }

            return Storage::disk($disk)->size($path);

        } catch (Exception $e) {
            Log::error("S3 Size Check Failed", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to get file size from S3: " . $e->getMessage());
        }
    }

    /**
     * Get file last modified timestamp
     */
    public function lastModified(string $path, string $disk = null): int
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            if (!$this->exists($path, $disk)) {
                throw new Exception("File not found in S3: {$path}");
            }

            return Storage::disk($disk)->lastModified($path);

        } catch (Exception $e) {
            Log::error("S3 Last Modified Check Failed", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to get last modified time from S3: " . $e->getMessage());
        }
    }

    /**
     * List files in a directory
     */
    public function listFiles(string $directory = '', string $disk = null): array
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            return Storage::disk($disk)->files($directory);
        } catch (Exception $e) {
            Log::error("S3 List Files Failed", [
                'disk' => $disk,
                'directory' => $directory,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to list files from S3: " . $e->getMessage());
        }
    }

    /**
     * List all files recursively
     */
    public function listAllFiles(string $directory = '', string $disk = null): array
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            return Storage::disk($disk)->allFiles($directory);
        } catch (Exception $e) {
            Log::error("S3 List All Files Failed", [
                'disk' => $disk,
                'directory' => $directory,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to list all files from S3: " . $e->getMessage());
        }
    }

    /**
     * List directories
     */
    public function listDirectories(string $directory = '', string $disk = null): array
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            return Storage::disk($disk)->directories($directory);
        } catch (Exception $e) {
            Log::error("S3 List Directories Failed", [
                'disk' => $disk,
                'directory' => $directory,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to list directories from S3: " . $e->getMessage());
        }
    }

    /**
     * Copy a file within S3
     */
    public function copy(string $fromPath, string $toPath, string $disk = null): bool
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            if (!$this->exists($fromPath, $disk)) {
                throw new Exception("Source file not found in S3: {$fromPath}");
            }

            $result = Storage::disk($disk)->copy($fromPath, $toPath);

            if ($result) {
                Log::info("S3 Copy Success", [
                    'disk' => $disk,
                    'from' => $fromPath,
                    'to' => $toPath
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("S3 Copy Failed", [
                'disk' => $disk,
                'from' => $fromPath,
                'to' => $toPath,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to copy file in S3: " . $e->getMessage());
        }
    }

    /**
     * Move a file within S3
     */
    public function move(string $fromPath, string $toPath, string $disk = null): bool
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            if (!$this->exists($fromPath, $disk)) {
                throw new Exception("Source file not found in S3: {$fromPath}");
            }

            $result = Storage::disk($disk)->move($fromPath, $toPath);

            if ($result) {
                Log::info("S3 Move Success", [
                    'disk' => $disk,
                    'from' => $fromPath,
                    'to' => $toPath
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("S3 Move Failed", [
                'disk' => $disk,
                'from' => $fromPath,
                'to' => $toPath,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to move file in S3: " . $e->getMessage());
        }
    }

    /**
     * Get bucket info and statistics
     */
    public function getBucketInfo(string $disk = null, string $directory = ''): array
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            $allFiles = $this->listAllFiles($directory, $disk);
            $totalSize = 0;
            $fileTypes = [];
            $sizeByType = [];

            foreach ($allFiles as $file) {
                try {
                    $size = $this->size($file, $disk);
                    $totalSize += $size;

                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION)) ?: 'no-extension';
                    $fileTypes[$extension] = ($fileTypes[$extension] ?? 0) + 1;
                    $sizeByType[$extension] = ($sizeByType[$extension] ?? 0) + $size;
                } catch (Exception $e) {
                    // Skip files that can't be read
                    continue;
                }
            }

            return [
                'disk' => $disk,
                'directory' => $directory ?: '/',
                'total_files' => count($allFiles),
                'total_size_bytes' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'total_size_gb' => round($totalSize / 1024 / 1024 / 1024, 3),
                'file_types' => $fileTypes,
                'size_by_type_mb' => array_map(fn($size) => round($size / 1024 / 1024, 2), $sizeByType),
                'bucket' => config("filesystems.disks.{$disk}.bucket"),
                'region' => config("filesystems.disks.{$disk}.region"),
                'analyzed_at' => now()->toDateTimeString()
            ];

        } catch (Exception $e) {
            Log::error("S3 Bucket Info Failed", [
                'disk' => $disk,
                'directory' => $directory,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to get bucket info: " . $e->getMessage());
        }
    }

    /**
     * Batch upload multiple files
     */
    public function batchUpload(array $files, array $globalOptions = [], string $disk = null): array
    {
        $disk = $disk ?? $this->defaultDisk;
        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($files as $path => $data) {
            try {
                $content = is_array($data) ? $data['content'] : $data;
                $options = is_array($data) && isset($data['options']) ?
                    array_merge($globalOptions, $data['options']) :
                    $globalOptions;

                $success = $this->upload($path, $content, $options, $disk);
                $results[$path] = [
                    'success' => $success,
                    'error' => null,
                    'size' => strlen($content)
                ];

                if ($success) {
                    $successCount++;
                } else {
                    $failureCount++;
                }

            } catch (Exception $e) {
                $results[$path] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'size' => 0
                ];
                $failureCount++;
            }
        }

        Log::info("S3 Batch Upload Completed", [
            'disk' => $disk,
            'total_files' => count($files),
            'successful' => $successCount,
            'failed' => $failureCount
        ]);

        return [
            'results' => $results,
            'summary' => [
                'total' => count($files),
                'successful' => $successCount,
                'failed' => $failureCount,
                'success_rate' => count($files) > 0 ? round(($successCount / count($files)) * 100, 2) : 0
            ]
        ];
    }

    /**
     * Get MIME type for a file path
     */
    private function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'html' => 'text/html',
            'htm' => 'text/html',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'txt' => 'text/plain',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'csv' => 'text/csv',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Generate a download URL via Laravel route
     */
    public function getDownloadUrl(string $path, array $routeParams = [], string $routeName = 'files.download', string $disk = null): string
    {
        $disk = $disk ?? $this->defaultDisk;

        try {
            if (!$this->exists($path, $disk)) {
                throw new \RuntimeException("File not found in S3: {$path}");
            }

            $defaultParams = [
                'disk' => $disk,
                'path' => base64_encode($path)
            ];

            $params = array_merge($defaultParams, $routeParams);

            return route($routeName, $params);

        } catch (Exception $e) {
            Log::error("S3 Download URL Failed", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to generate download URL: " . $e->getMessage());
        }
    }
}
