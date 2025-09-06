<?php

namespace App\Services\Certificate;

class S3FolderManager
{
    /**
     * Get configured folder name for a specific type
     */
    public static function getFolder(string $type): string
    {
        return config("s3folders.{$type}", $type);
    }

    /**
     * Get all configured folders
     */
    public static function getAllFolders(): array
    {
        return [
            'certificates' => self::getFolder('certificates'),
            'documents' => self::getFolder('documents'),
            'media' => self::getFolder('media'),
            'temp' => self::getFolder('temp'),
            'archive' => self::getFolder('archive'),
            'backups' => self::getFolder('backups'),
        ];
    }

    /**
     * Generate certificate file path using configured structure
     * For certificates disk (which has root 'certificates'), we don't include the folder prefix
     */
    public static function getCertificatePath(string $certificateNumber, string $format, string $template = 'default'): string
    {
        // When using certificates disk, the root is already 'certificates', so we don't need the prefix
        return "{$certificateNumber}/{$format}/{$template}.{$format}";
    }

    /**
     * Generate document file path
     */
    public static function getDocumentPath(string $filename, string $subfolder = null): string
    {
        $documentsFolder = self::getFolder('documents');
        $year = date('Y');
        $month = date('m');

        $path = "{$documentsFolder}/{$year}/{$month}";
        if ($subfolder) {
            $path .= "/{$subfolder}";
        }
        return "{$path}/{$filename}";
    }

    /**
     * Generate media file path
     */
    public static function getMediaPath(string $filename, string $type = 'images'): string
    {
        $mediaFolder = self::getFolder('media');
        $year = date('Y');
        $month = date('m');

        return "{$mediaFolder}/{$type}/{$year}/{$month}/{$filename}";
    }

    /**
     * Generate temporary file path
     */
    public static function getTempPath(string $filename, string $sessionId = null): string
    {
        $tempFolder = self::getFolder('temp');
        $sessionId = $sessionId ?: session()->getId();

        return "{$tempFolder}/{$sessionId}/{$filename}";
    }

    /**
     * Generate archive path for certificates
     */
    public static function getArchivePath(string $certificateNumber): string
    {
        $archiveFolder = self::getFolder('archive');
        $certificatesFolder = self::getFolder('certificates');

        return "{$archiveFolder}/{$certificatesFolder}/{$certificateNumber}";
    }

    /**
     * Generate backup path
     */
    public static function getBackupPath(string $filename, string $type = 'general'): string
    {
        $backupFolder = self::getFolder('backups');
        $date = date('Y-m-d');

        return "{$backupFolder}/{$type}/{$date}/{$filename}";
    }

    /**
     * Get supported file formats for a folder type
     */
    public static function getSupportedFormats(string $type): array
    {
        return config("s3folders.supported_formats.{$type}", []);
    }

    /**
     * Validate if a file format is supported for a folder type
     */
    public static function isFormatSupported(string $type, string $format): bool
    {
        $supportedFormats = self::getSupportedFormats($type);
        return in_array(strtolower($format), $supportedFormats);
    }

    /**
     * Get folder structure configuration
     */
    public static function getFolderStructure(): array
    {
        return config('s3folders.certificate_structure', [
            'by_number' => true,
            'by_format' => true,
            'by_template' => true,
        ]);
    }

    /**
     * Get naming pattern for a specific type
     */
    public static function getNamingPattern(string $type): string
    {
        return config("s3folders.naming_patterns.{$type}", '{folder}/{filename}');
    }

    /**
     * Clean old temporary files (returns paths that should be deleted)
     */
    public static function getExpiredTempPaths(int $hoursOld = 24): array
    {
        $tempFolder = self::getFolder('temp');
        $cutoffTime = now()->subHours($hoursOld);

        // This would need to be implemented with actual S3 listing
        // For now, return the temp folder pattern for cleanup
        return [
            'pattern' => "{$tempFolder}/*",
            'cutoff_time' => $cutoffTime->toDateTimeString(),
            'note' => 'Use S3Service->listAllFiles() to get actual files for cleanup'
        ];
    }
}
