<?php

namespace App\Services\Certificate;

use App\Contracts\CertificateGeneratorInterface;
use App\Services\Certificate\Generators\HtmlCertificateGenerator;
use App\Services\Certificate\Generators\ImageCertificateGenerator;
use App\Services\Certificate\Generators\PdfCertificateGenerator;

class CertificateGeneratorFactory
{
    /**
     * Create certificate generator by format
     */
    public static function create(string $format): CertificateGeneratorInterface
    {
        return match ($format) {
            'pdf' => app(PdfCertificateGenerator::class),
            'html' => app(HtmlCertificateGenerator::class),
            'png', 'jpg', 'jpeg', 'image' => app(ImageCertificateGenerator::class),
            default => throw new \InvalidArgumentException("Unsupported certificate format: {$format}")
        };
    }

    /**
     * Get all available generators with their supported formats
     */
    public static function getAvailableGenerators(): array
    {
        return [
            'pdf' => [
                'class' => PdfCertificateGenerator::class,
                'formats' => ['pdf'],
                'description' => 'PDF documents for printing and official use'
            ],
            'html' => [
                'class' => HtmlCertificateGenerator::class,
                'formats' => ['html'],
                'description' => 'HTML files for web display and sharing'
            ],
            'image' => [
                'class' => ImageCertificateGenerator::class,
                'formats' => ['png', 'jpg', 'jpeg'],
                'description' => 'Image files for social media and web use'
            ],
        ];
    }

    /**
     * Get supported formats across all generators
     */
    public static function getSupportedFormats(): array
    {
        return collect(self::getAvailableGenerators())
            ->pluck('formats')
            ->flatten()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Check if format is supported
     */
    public static function isFormatSupported(string $format): bool
    {
        return in_array($format, self::getSupportedFormats());
    }
}
