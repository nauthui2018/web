<?php

namespace App\Services\Certificate\Templates;

use App\Models\Certificate;

class ModernCertificateTemplate extends BaseCertificateTemplate
{
    protected string $name = 'modern';
    protected array $metadata = [
        'title' => 'Modern Certificate',
        'description' => 'Contemporary design with clean lines and minimalist approach',
        'preview_image' => '/images/certificate-previews/modern.png',
        'style_type' => 'contemporary'
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
            <title>Certificate of Achievement - {$certificate->user_name}</title>
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
                    font-family: 'Inter', sans-serif;
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
                    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 50%, #f1f5f9 100%);
                    border: none;
                    box-sizing: border-box;
                    position: relative;
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
                    border: 2px solid #2c5aa0;
                    border-radius: 6px;
                    pointer-events: none;
                }

                .header {
                    text-align: center;
                    margin-bottom: 4mm;
                    position: relative;
                    z-index: 2;
                    padding-bottom: 2mm;
                }

                .institution-name {
                    font-size: 11px;
                    font-weight: 600;
                    color: #2c5aa0;
                    margin-bottom: 5mm;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                    position: relative;
                    background: white;
                    padding: 0 15px;
                    z-index: 3;
                }

                .institution-name::after {
                    content: '';
                    position: absolute;
                    bottom: -2.5mm;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 60%;
                    height: 1px;
                    background: linear-gradient(90deg, transparent, #2c5aa0, transparent);
                    z-index: 1;
                }

                .certificate-title {
                    font-family: 'Playfair Display', serif;
                    font-size: 30px;
                    font-weight: 700;
                    color: #2c5aa0;
                    margin-bottom: 2mm;
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                    background: white;
                    padding: 0 20px;
                    position: relative;
                    z-index: 3;
                }

                .certificate-subtitle {
                    font-size: 14px;
                    color: #64748b;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 1.5px;
                    background: white;
                    padding: 0 20px;
                    position: relative;
                    z-index: 3;
                }

                .main-content {
                    text-align: center;
                    margin: 5mm 0;
                    position: relative;
                    z-index: 1;
                    max-width: 270mm;
                    margin-left: auto;
                    margin-right: auto;
                }

                .certifies-text {
                    font-size: 13px;
                    color: #64748b;
                    margin-bottom: 5mm;
                    font-style: italic;
                }

                .recipient-name {
                    font-family: 'Playfair Display', serif;
                    font-size: 27px;
                    font-weight: 700;
                    color: #2c5aa0;
                    margin: 5mm 0;
                    padding: 0 20px;
                    border-bottom: 3px solid #d4a574;
                    display: inline-block;
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
                }

                .completion-text {
                    font-size: 12px;
                    color: #64748b;
                    margin: 5mm auto;
                    line-height: 1.4;
                    max-width: 240mm;
                }

                .test-title {
                    font-family: 'Playfair Display', serif;
                    font-size: 19px;
                    font-weight: 600;
                    color: #2c5aa0;
                    margin: 7mm auto;
                    padding: 7mm 12mm;
                    background: linear-gradient(135deg, rgba(44, 90, 160, 0.1), rgba(212, 165, 116, 0.1));
                    border-radius: 8px;
                    border-left: 4px solid #d4a574;
                    max-width: 240mm;
                    line-height: 1.3;
                }

                .achievement-section {
                    text-align: center;
                    margin: 7mm 0;
                    width: 100%;
                }

                .achievement-item {
                    text-align: center;
                    background: linear-gradient(135deg, rgba(44, 90, 160, 0.05), rgba(212, 165, 116, 0.05));
                    padding: 7mm;
                    border-radius: 8px;
                    border: 1px solid rgba(44, 90, 160, 0.2);
                    min-width: 70mm;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                    display: inline-block;
                    vertical-align: top;
                    margin: 0 5mm;
                }

                .achievement-value {
                    font-family: 'Playfair Display', serif;
                    font-size: 19px;
                    font-weight: 700;
                    color: #2c5aa0;
                    display: block;
                    margin-bottom: 3mm;
                }

                .achievement-label {
                    font-size: 9px;
                    color: #64748b;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    font-weight: 500;
                }

                .footer {
                    margin-top: 10mm;
                    padding-top: 7mm;
                    border-top: 1px solid #e2e8f0;
                    position: relative;
                    z-index: 1;
                    width: 100%;
                    max-width: 270mm;
                    margin-left: auto;
                    margin-right: auto;
                    display: table;
                    table-layout: fixed;
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
                    font-weight: 500;
                    letter-spacing: 0.5px;
                }

                .date-value {
                    font-size: 12px;
                    color: #2c5aa0;
                    font-weight: 600;
                    line-height: 1.2;
                }

                .signature-section {
                    text-align: center;
                    display: table-cell;
                    width: 33.33%;
                    vertical-align: top;
                    padding: 3mm;
                }

                .signature-line {
                    width: 85px;
                    height: 2px;
                    background: #2c5aa0;
                    margin: 0 auto 5mm;
                }

                .signature-title {
                    font-size: 8px;
                    color: #64748b;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 3mm;
                }

                .signature-name {
                    font-size: 12px;
                    color: #2c5aa0;
                    font-weight: 600;
                    line-height: 1.2;
                }

                .verification-section {
                    text-align: right;
                    display: table-cell;
                    width: 33.33%;
                    vertical-align: top;
                    padding: 3mm;
                }

                .qr-code {
                    width: 30px;
                    height: 30px;
                    margin-bottom: 3mm;
                    border: 1px solid #2c5aa0;
                    border-radius: 4px;
                    margin-left: auto;
                    display: block;
                }

                .certificate-number {
                    font-size: 10px;
                    color: #2c5aa0;
                    font-weight: 600;
                    margin-bottom: 2mm;
                    line-height: 1.2;
                }

                .verify-text {
                    font-size: 8px;
                    color: #64748b;
                    line-height: 1.2;
                }

                .verification-url {
                    font-size: 8px;
                    color: #64748b;
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
                        has successfully completed the assessment and demonstrated
                        proficiency in the subject matter of
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