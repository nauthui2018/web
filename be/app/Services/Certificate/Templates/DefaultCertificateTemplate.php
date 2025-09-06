<?php

namespace App\Services\Certificate\Templates;

use App\Models\Certificate;

class DefaultCertificateTemplate extends BaseCertificateTemplate
{
    protected string $name = 'default';
    protected array $metadata = [
        'title' => 'Classic Certificate',
        'description' => 'Traditional academic-style certificate with professional layout',
        'preview_image' => '/images/certificate-previews/default.png',
        'style_type' => 'classic'
    ];

    public function generate(Certificate $certificate): string
    {
        $data = [
            'user_name' => $certificate->user_name,
            'test_title' => $certificate->test_title,
            'score' => $certificate->score,
            'completed_at' => $certificate->completed_at,
            'certificate_number' => $certificate->certificate_number,
        ];

        if (!$this->validate($data)) {
            throw new \InvalidArgumentException('Invalid certificate data');
        }

        return $this->buildHtml($certificate);
    }

    private function buildHtml(Certificate $certificate): string
    {
        $score = $this->formatScore($certificate->score);
        $gradeLevel = $this->getGradeLevel($certificate->score);
        $date = $this->formatDate($certificate->completed_at);
        $qrCode = $this->generateQRCode($certificate->certificate_number);
        $commonStyles = $this->getCommonStyles();

        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Certificate of Completion - {$certificate->user_name}</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
                @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
                
                {$commonStyles}
                
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                @page {
                    size: A4 landscape;
                    margin: 0;
                }
                
                body {
                    font-family: 'Inter', 'Arial', sans-serif;
                    background: white;
                    margin: 0;
                    padding: 0;
                }
                
                .certificate {
                    /* A4 landscape: 297mm x 210mm */
                    width: 297mm;
                    height: 210mm;
                    max-width: 297mm;
                    max-height: 210mm;
                    padding: 10mm;
                    margin: 0 auto;
                    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #ffffff 100%);
                    border: 3px solid #1a365d;
                    border-radius: 6px;
                    box-sizing: border-box;
                    position: relative;
                    box-shadow: 0 6px 24px rgba(26, 54, 93, 0.12);
                    overflow: hidden;
                    page-break-inside: avoid;
                }

                .certificate::before {
                    content: '';
                    position: absolute;
                    top: 5mm;
                    left: 5mm;
                    right: 5mm;
                    bottom: 5mm;
                    border: 1px solid #d4a574;
                    border-radius: 4px;
                    pointer-events: none;
                    opacity: 0.6;
                }

                .certificate::after {
                    content: '';
                    position: absolute;
                    top: 8mm;
                    left: 8mm;
                    right: 8mm;
                    bottom: 8mm;
                    border: 1px solid #e2e8f0;
                    border-radius: 3px;
                    pointer-events: none;
                }

                .header {
                    text-align: center;
                    margin-bottom: 8mm;
                    padding-bottom: 6mm;
                    position: relative;
                    z-index: 10;
                }

                .header::after {
                    content: '';
                    position: absolute;
                    bottom: 0;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 100px;
                    height: 2px;
                    background: linear-gradient(90deg, transparent, #d4a574, transparent);
                    border-radius: 2px;
                }

                .institution-name {
                    font-size: 11px;
                    font-weight: 600;
                    color: #1a365d;
                    margin-bottom: 5mm;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                    position: relative;
                    z-index: 11;
                }

                .certificate-title {
                    font-family: 'Playfair Display', 'Times New Roman', serif;
                    font-size: 30px;
                    font-weight: 700;
                    color: #1a365d;
                    margin-bottom: 2mm;
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                }

                .certificate-subtitle {
                    font-size: 14px;
                    color: #64748b;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }

                .main-content {
                    margin: 6mm 0;
                    padding: 0 10mm;
                    position: relative;
                    z-index: 5;
                    max-width: 270mm;
                    margin-left: auto;
                    margin-right: auto;
                }

                .certifies-text {
                    font-size: 14px;
                    color: #1a365d;
                    margin-bottom: 6mm;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    text-align: center;
                }

                .recipient-name {
                    font-family: 'Playfair Display', 'Times New Roman', serif;
                    font-size: 28px;
                    font-weight: 700;
                    color: #1a365d;
                    margin: 6mm 0;
                    text-decoration: underline;
                    text-decoration-color: #d4a574;
                    text-decoration-thickness: 3px;
                    text-underline-offset: 8px;
                    text-align: center;
                }

                .completion-text {
                    font-size: 13px;
                    color: #2d3748;
                    margin: 6mm auto;
                    line-height: 1.6;
                    max-width: 240mm;
                    text-align: center;
                }

                .test-title {
                    font-family: 'Playfair Display', 'Times New Roman', serif;
                    font-size: 18px;
                    font-weight: 700;
                    color: #1a365d;
                    margin: 8mm auto;
                    padding: 8mm 15mm;
                    border: 2px solid #d4a574;
                    border-radius: 15px;
                    max-width: 240mm;
                    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                    box-shadow: 0 3px 12px rgba(212, 165, 116, 0.12);
                    text-align: center;
                    line-height: 1.4;
                }

                .achievement-section {
                    text-align: center;
                    margin: 8mm 0;
                    width: 100%;
                }

                .achievement-item {
                    text-align: center;
                    padding: 8mm 12mm;
                    border: 2px solid #d4a574;
                    border-radius: 12px;
                    min-width: 75mm;
                    background: linear-gradient(135deg, #ffffff 0%, #fefaf6 100%);
                    display: inline-block;
                    vertical-align: top;
                    margin: 0 6mm;
                    box-shadow: 0 3px 12px rgba(212, 165, 116, 0.12);
                }

                .achievement-value {
                    font-family: 'Playfair Display', 'Times New Roman', serif;
                    font-size: 20px;
                    font-weight: 700;
                    color: #1a365d;
                    display: block;
                    margin-bottom: 3mm;
                }

                .achievement-label {
                    font-size: 10px;
                    color: #4a5568;
                    text-transform: uppercase;
                    font-weight: 500;
                    letter-spacing: 1px;
                }

                .footer {
                    margin-top: 8mm;
                    padding-top: 5mm;
                    border-top: 2px solid #d4a574;
                    width: 100%;
                    max-width: 270mm;
                    margin-left: auto;
                    margin-right: auto;
                    display: table;
                    table-layout: fixed;
                    position: relative;
                    z-index: 5;
                }

                .date-section {
                    text-align: left;
                    display: table-cell;
                    width: 33.33%;
                    vertical-align: top;
                    padding: 3mm;
                }

                .date-label {
                    font-size: 9px;
                    color: #64748b;
                    text-transform: uppercase;
                    margin-bottom: 3mm;
                    font-weight: 600;
                    letter-spacing: 1px;
                }

                .date-value {
                    font-size: 12px;
                    color: #1a365d;
                    font-weight: 700;
                    line-height: 1.3;
                }

                .signature-section {
                    text-align: center;
                    display: table-cell;
                    width: 33.33%;
                    vertical-align: top;
                    padding: 3mm;
                }

                .signature-line {
                    border-top: 2px solid #4a5568;
                    width: 90px;
                    margin: 0 auto 5mm auto;
                }

                .signature-title {
                    font-size: 8px;
                    color: #4a5568;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 2mm;
                }

                .signature-name {
                    font-size: 11px;
                    color: #1a365d;
                    font-weight: 600;
                    line-height: 1.2;
                }                
                
                .verification-section {
                    text-align: right;
                    display: table-cell;
                    width: 33.33%;
                    vertical-align: top;
                    padding: 3mm 5mm 3mm 3mm;
                }

                .qr-code {
                    width: 30px;
                    height: 30px;
                    margin-bottom: 3mm;
                    border: 2px solid #1a365d;
                    border-radius: 4px;
                    margin-left: auto;
                    display: block;
                }

                .certificate-number {
                    font-size: 9px;
                    color: #1a365d;
                    font-weight: 600;
                    margin-bottom: 2mm;
                    line-height: 1.2;
                    word-break: break-all;
                }

                .verify-text {
                    font-size: 8px;
                    color: #718096;
                    line-height: 1.2;
                }

                .verification-url {
                    font-size: 8px;
                    color: #4a5568;
                    word-break: break-all;
                    line-height: 1.2;
                }

                @media print {
                    body {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                        margin: 0;
                        padding: 0;
                    }
                    
                    .certificate {
                        width: 297mm;
                        height: 210mm;
                        margin: 0;
                        padding: 10mm;
                        page-break-inside: avoid;
                        overflow: hidden;
                        position: relative;
                    }
                }
            </style>
        </head>
        <body>
            <div class='certificate'>
                <div class='header'>
                    <div class='institution-name'>Test Platform Academy</div>
                    <div class='certificate-title'>Certificate</div>
                    <div class='certificate-subtitle'>of Completion</div>
                </div>

                <div class='main-content'>
                    <div class='certifies-text'>This is to certify that</div>
                    <div class='recipient-name'>{$certificate->user_name}</div>
                    <div class='completion-text'>
                        has successfully completed the required assessment and demonstrated
                        competency in the subject matter of
                    </div>
                    <div class='test-title'>{$certificate->test_title}</div>

                    <div class='achievement-section'>
                        <div class='achievement-item'>
                            <span class='achievement-value'>{$score}</span>
                            <div class='achievement-label'>Final Score</div>
                        </div>
                        <div class='achievement-item'>
                            <span class='achievement-value'>{$gradeLevel}</span>
                            <div class='achievement-label'>Performance</div>
                        </div>
                    </div>
                </div>

                <div class='footer'>
                    <div class='date-section'>
                        <div class='date-label'>Date of Completion</div>
                        <div class='date-value'>{$date}</div>
                    </div>

                    <div class='signature-section'>
                        <div class='signature-line'></div>
                        <div class='signature-title'>Authorized by</div>
                        <div class='signature-name'>Test Platform Academy</div>
                    </div>

                    <div class='verification-section'>
                        <img src='{$qrCode}' alt='Verification QR Code' class='qr-code'>
                        <div class='certificate-number'>{$certificate->certificate_number}</div>
                        <div class='verification-url'>testplatform.com/verify</div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}