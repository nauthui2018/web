const puppeteer = require('puppeteer');
const fs = require('fs');

const htmlFile = process.argv[2];
const pdfFile = process.argv[3];

(async () => {
    try {
        const browser = await puppeteer.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        const page = await browser.newPage();
        const html = fs.readFileSync(htmlFile, 'utf8');
        await page.setContent(html, { waitUntil: 'networkidle0' });

        await page.pdf({
            path: pdfFile,
            format: 'A4',
            margin: {
                top: '0in',
                right: '0in',
                bottom: '0in',
                left: '0in'
            },
            printBackground: true
        });

        await browser.close();
    } catch (err) {
        console.error('Puppeteer failed:', err);
        process.exit(1);
    }
})();
