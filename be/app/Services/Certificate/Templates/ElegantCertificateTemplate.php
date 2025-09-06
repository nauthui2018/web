<?php

namespace App\Services\Certificate\Templates;

use App\Models\Certificate;

class ElegantCertificateTemplate extends BaseCertificateTemplate
{
    protected string $name = 'elegant';
    protected array $metadata = [
        'title' => 'Elegant Certificate',
        'description' => 'Sophisticated design with gold accents and decorative elements',
        'preview_image' => '/images/certificate-previews/elegant.png',
        'style_type' => 'elegant'
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
            <title>Certificate of Excellence - {$certificate->user_name}</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
                @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
                @import url('https://fonts.googleapis.com/css2?family=Crimson+Text:wght@400;600&display=swap');
                
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
                    background: linear-gradient(135deg, #fdfcfb 0%, #ffffff 50%, #f8f6f0 100%);
                    border: none;
                    box-sizing: border-box;
                    position: relative;
                    overflow: hidden;
                    page-break-inside: avoid;
                    box-shadow: 0 0 20px rgba(139, 90, 43, 0.15);
                }

                .certificate::before {
                    content: '';
                    position: absolute;
                    top: 5mm;
                    left: 5mm;
                    right: 5mm;
                    bottom: 5mm;
                    border: 3px double #d4a574;
                    pointer-events: none;
                    border-radius: 8px;
                }

                .certificate::after {
                    content: '';
                    position: absolute;
                    top: 8mm;
                    left: 8mm;
                    right: 8mm;
                    bottom: 8mm;
                    border: 1px solid #d4a574;
                    pointer-events: none;
                    border-radius: 6px;
                }

                .ornate-corner {
                    position: absolute;
                    width: 40px;
                    height: 40px;
                    background: radial-gradient(circle, #d4a574 30%, transparent 70%);
                    border-radius: 50%;
                    opacity: 0.6;
                }

                .ornate-corner.top-left {
                    top: 3mm;
                    left: 3mm;
                }

                .ornate-corner.top-right {
                    top: 3mm;
                    right: 3mm;
                }

                .ornate-corner.bottom-left {
                    bottom: 3mm;
                    left: 3mm;
                }

                .ornate-corner.bottom-right {
                    bottom: 3mm;
                    right: 3mm;
                }

                .watermark {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    font-size: 80px;
                    color: rgba(212, 165, 116, 0.08);
                    font-weight: 700;
                    font-family: 'Playfair Display', serif;
                    z-index: 0;
                    pointer-events: none;
                }

                .header {
                    text-align: center;
                    margin-bottom: 4mm;
                    position: relative;
                    z-index: 1;
                    padding-bottom: 2mm;
                }

                .institution-logo {
                    width: 40px;
                    height: 40px;
                    background: linear-gradient(135deg, #8b5a2b, #d4a574);
                    border-radius: 50%;
                    margin: 0 auto 4mm;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-family: 'Playfair Display', serif;
                    font-size: 16px;
                    font-weight: 700;
                    color: white;
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
                }

                .institution-name {
                    font-family: 'Crimson Text', serif;
                    font-size: 11px;
                    font-weight: 600;
                    color: #8b5a2b;
                    margin-bottom: 3mm;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                }

                .certificate-title {
                    font-family: 'Playfair Display', serif;
                    font-size: 26px;
                    font-weight: 700;
                    background: linear-gradient(135deg, #8b5a2b, #d4a574);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    margin-bottom: 1mm;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
                }

                .certificate-subtitle {
                    font-family: 'Crimson Text', serif;
                    font-size: 12px;
                    color: #8b5a2b;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 1.5px;
                    margin-bottom: 3mm;
                    font-style: italic;
                }

                .decorative-line {
                    width: 100px;
                    height: 2px;
                    background: linear-gradient(90deg, transparent, #d4a574, transparent);
                    margin: 2mm auto;
                }

                .main-content {
                    text-align: center;
                    margin: 3mm 0;
                    padding: 2mm 0;
                    position: relative;
                    z-index: 1;
                    max-width: 275mm;
                    margin-left: auto;
                    margin-right: auto;
                }

                .certifies-text {
                    font-family: 'Crimson Text', serif;
                    font-size: 12px;
                    color: #5d4e37;
                    margin-bottom: 3mm;
                    font-style: italic;
                    font-weight: 400;
                }

                .recipient-name {
                    font-family: 'Playfair Display', serif;
                    font-size: 24px;
                    font-weight: 600;
                    background: linear-gradient(135deg, #8b5a2b, #d4a574);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    margin: 4mm 0;
                    padding: 0 20px;
                    border-bottom: 3px solid #d4a574;
                    display: inline-block;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
                    position: relative;
                }

                .recipient-name::before,
                .recipient-name::after {
                    content: '✦';
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #d4a574;
                    font-size: 14px;
                }

                .recipient-name::before {
                    left: -10px;
                }

                .recipient-name::after {
                    right: -10px;
                }

                .completion-text {
                    font-family: 'Crimson Text', serif;
                    font-size: 11px;
                    color: #5d4e37;
                    margin: 4mm auto;
                    line-height: 1.4;
                    max-width: 245mm;
                    font-weight: 400;
                }

                .test-title {
                    font-family: 'Playfair Display', serif;
                    font-size: 17px;
                    font-weight: 600;
                    color: #8b5a2b;
                    margin: 5mm auto;
                    background: linear-gradient(135deg, rgba(139, 90, 43, 0.1), rgba(212, 165, 116, 0.1));
                    padding: 5mm 10mm;
                    border-radius: 10px;
                    border: 2px solid #d4a574;
                    position: relative;
                    max-width: 245mm;
                    line-height: 1.3;
                }

                .test-title::before,
                .test-title::after {
                    content: '';
                    position: absolute;
                    top: -3px;
                    width: 6px;
                    height: 6px;
                    background: #d4a574;
                    border-radius: 50%;
                }

                .test-title::before {
                    left: -3px;
                }

                .test-title::after {
                    right: -3px;
                }

                .achievement-section {
                    text-align: center;
                    margin: 5mm 0;
                    width: 100%;
                }

                .achievement-item {
                    text-align: center;
                    background: linear-gradient(135deg, rgba(139, 90, 43, 0.05), rgba(212, 165, 116, 0.05));
                    padding: 5mm;
                    border-radius: 10px;
                    border: 2px solid #d4a574;
                    min-width: 65mm;
                    position: relative;
                    box-shadow: 0 2px 8px rgba(139, 90, 43, 0.06);
                    display: inline-block;
                    vertical-align: top;
                    margin: 0 4mm;
                }

                .achievement-item::before {
                    content: '';
                    position: absolute;
                    top: -3px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 6px;
                    height: 6px;
                    background: #d4a574;
                    border-radius: 50%;
                }

                .achievement-value {
                    font-family: 'Playfair Display', serif;
                    font-size: 17px;
                    font-weight: 700;
                    background: linear-gradient(135deg, #8b5a2b, #d4a574);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    display: block;
                    margin-bottom: 2mm;
                }

                .achievement-label {
                    font-family: 'Crimson Text', serif;
                    font-size: 8px;
                    color: #8b5a2b;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    font-weight: 600;
                }

                .footer {
                    margin-top: 8mm;
                    padding-top: 5mm;
                    border-top: 2px solid #d4a574;
                    position: relative;
                    z-index: 1;
                    width: 100%;
                    max-width: 275mm;
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
                    padding: 2mm;
                }

                .date-label {
                    font-family: 'Crimson Text', serif;
                    font-size: 8px;
                    color: #8b5a2b;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    margin-bottom: 2mm;
                    font-weight: 600;
                }

                .date-value {
                    font-family: 'Playfair Display', serif;
                    font-size: 11px;
                    color: #8b5a2b;
                    font-weight: 600;
                    line-height: 1.2;
                }

                .signature-section {
                    text-align: center;
                    display: table-cell;
                    width: 33.33%;
                    vertical-align: top;
                    padding: 2mm;
                }

                .signature-line {
                    width: 75px;
                    height: 2px;
                    background: linear-gradient(90deg, transparent, #d4a574, transparent);
                    margin: 0 auto 4mm;
                }

                .signature-title {
                    font-family: 'Crimson Text', serif;
                    font-size: 7px;
                    color: #8b5a2b;
                    text-transform: uppercase;
                    margin-bottom: 2mm;
                    letter-spacing: 1px;
                    font-weight: 600;
                }

                .signature-name {
                    font-family: 'Playfair Display', serif;
                    font-size: 11px;
                    color: #8b5a2b;
                    font-weight: 600;
                    line-height: 1.2;
                }

                .verification-section {
                    text-align: right;
                    display: table-cell;
                    width: 33.33%;
                    vertical-align: top;
                    padding: 2mm;
                }

                .qr-code {
                    width: 26px;
                    height: 26px;
                    margin-bottom: 2mm;
                    border: 2px solid #d4a574;
                    border-radius: 5px;
                    box-shadow: 0 2px 5px rgba(139, 90, 43, 0.12);
                    margin-left: auto;
                    display: block;
                }

                .certificate-number {
                    font-family: 'Crimson Text', serif;
                    font-size: 9px;
                    color: #8b5a2b;
                    font-weight: 600;
                    margin-bottom: 1mm;
                    line-height: 1.2;
                }

                .verification-url {
                    font-family: 'Crimson Text', serif;
                    font-size: 7px;
                    color: #8b5a2b;
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
                <div class='ornate-corner top-left'></div>
                <div class='ornate-corner top-right'></div>
                <div class='ornate-corner bottom-left'></div>
                <div class='ornate-corner bottom-right'></div>
                <div class='watermark'>CERTIFIED</div>
                
                <div class='header'>
                    <div class='institution-logo'>TP</div>
                    <div class='institution-name'>Test Platform Academy</div>
                    <div class='certificate-title'>Certificate</div>
                    <div class='certificate-subtitle'>of Excellence</div>
                    <div class='decorative-line'></div>
                </div>

                <div class='main-content'>
                    <div class='certifies-text'>This is to certify that</div>
                    <div class='recipient-name'>{$certificate->user_name}</div>
                    <div class='completion-text'>
                        has successfully completed the comprehensive assessment and demonstrated
                        outstanding proficiency in the subject matter of
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
                        <div class='date-label'>Date of Excellence</div>
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