<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AWS S3 Storage Folders
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the folder structure used for
    | storing different types of files in AWS S3. You can customize these
    | values to organize your S3 bucket according to your needs.
    |
    */

    'certificates' => env('AWS_CERTIFICATES_FOLDER', 'certificates'),
    'documents' => env('AWS_DOCUMENTS_FOLDER', 'documents'),
    'media' => env('AWS_MEDIA_FOLDER', 'media'),
    'temp' => env('AWS_TEMP_FOLDER', 'temp'),
    'archive' => env('AWS_ARCHIVE_FOLDER', 'archive'),
    'backups' => env('AWS_BACKUPS_FOLDER', 'backups'),

    /*
    |--------------------------------------------------------------------------
    | Certificate Storage Structure
    |--------------------------------------------------------------------------
    |
    | These settings define how certificate files are organized within
    | the certificates folder structure.
    |
    */

    'certificate_structure' => [
        'by_number' => true,              // Group by certificate number
        'by_format' => true,              // Separate folders for each format (pdf, html, images)
        'by_template' => true,            // Separate files for each template
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported File Types
    |--------------------------------------------------------------------------
    |
    | Define the supported file types for each storage category.
    |
    */

    'supported_formats' => [
        'certificates' => ['pdf', 'html', 'png', 'jpg', 'jpeg'],
        'documents' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
        'media' => ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Naming Conventions
    |--------------------------------------------------------------------------
    |
    | Define naming patterns for different file types.
    |
    */

    'naming_patterns' => [
        'certificate' => '{folder}/{certificate_number}/{format}/{template}.{format}',
        'document' => '{folder}/{year}/{month}/{filename}',
        'media' => '{folder}/{type}/{year}/{month}/{filename}',
        'temp' => '{folder}/{session_id}/{filename}',
    ],

];
