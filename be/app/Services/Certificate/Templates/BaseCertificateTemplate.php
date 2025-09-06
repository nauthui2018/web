<?php

namespace App\Services\Certificate\Templates;

use App\Contracts\CertificateTemplateInterface;

abstract class BaseCertificateTemplate implements CertificateTemplateInterface
{
    protected string $name;
    protected array $metadata;

    public function getName(): string
    {
        return $this->name;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function validate(array $data): bool
    {
        $required = ['user_name', 'test_title', 'score', 'completed_at', 'certificate_number'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }

        if ($data['score'] < 0 || $data['score'] > 100) {
            return false;
        }

        return true;
    }

    protected function formatScore(float $score): string
    {
        return number_format($score, 1) . '%';
    }

    protected function formatDate(\DateTime $date): string
    {
        return $date->format('F j, Y');
    }

    protected function getGradeLevel(float $score): string
    {
        if ($score >= 95) return 'Excellent';
        if ($score >= 85) return 'Very Good';
        if ($score >= 75) return 'Good';
        if ($score >= 65) return 'Satisfactory';
        return 'Pass';
    }

    protected function generateQRCode(string $verificationCode): string
    {
        $verificationUrl = config('app.url') . "/verify-certificate/{$verificationCode}";
        return "data:image/svg+xml;base64," . base64_encode($this->generateQRSVG($verificationUrl));
    }

    private function generateQRSVG(string $url): string
    {
        return '<svg width="80" height="80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80">
                    <rect width="80" height="80" fill="#333"/>
                    <rect x="10" y="10" width="60" height="60" fill="none" stroke="#fff" stroke-width="2"/>
                    <text x="40" y="45" fill="#fff" text-anchor="middle" font-size="8" font-family="monospace">QR CODE</text>
                </svg>';
    }

    protected function getCommonStyles(): string
    {
        return "
            @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap');

            @page {
                size: A4 landscape;
                margin: 20mm;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', 'Arial', sans-serif;
                line-height: 1.6;
                color: #2c3e50;
                background: white;
                width: 100%;
                height: 100vh;
                margin: 0;
                padding: 0;
            }

            @media print {
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                .no-print {
                    display: none !important;
                }
            }
        ";
    }
}
