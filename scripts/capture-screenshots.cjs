/**
 * Mengambil tangkapan layar untuk portofolio.
 *
 * Prasyarat:
 *   1. Aplikasi berjalan di APP_URL (default http://127.0.0.1:8000).
 *   2. Basis data sudah di-seed (php artisan migrate:fresh --seed).
 *   3. Google Chrome terpasang.
 *   4. Paket opsional: npm install --no-save puppeteer-core
 *
 * Jalankan: node scripts/capture-screenshots.cjs
 * Hasil tersimpan di docs/screenshots/.
 */
const fs = require('fs');
const path = require('path');

const puppeteer = require('puppeteer-core');

const BASE_URL = process.env.SIMAS_URL || 'http://127.0.0.1:8000';
const OUTPUT_DIR = path.join(__dirname, '..', 'docs', 'screenshots');

const CHROME_CANDIDATES = [
    process.env.CHROME_PATH,
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
].filter(Boolean);

const DESKTOP = { width: 1440, height: 900 };
const MOBILE = { width: 390, height: 844 };

const ACCOUNTS = {
    admin: 'admin@simas.test',
    guru: 'budi.santoso@simas.test',
    siswa: 'andi.pratama@siswa.simas.test',
};

const PASSWORD = 'password';

function resolveChrome() {
    const found = CHROME_CANDIDATES.find((candidate) => fs.existsSync(candidate));

    if (!found) {
        throw new Error('Chrome/Edge tidak ditemukan. Setel variabel lingkungan CHROME_PATH.');
    }

    return found;
}

async function login(page, email) {
    // Bersihkan sesi sebelumnya agar halaman /masuk tidak mengalihkan ke dashboard.
    const cookies = await page.cookies();
    await page.deleteCookie(...cookies);
    await page.evaluate(() => {
        try {
            localStorage.clear();
        } catch (e) {}
    });

    await page.goto(`${BASE_URL}/masuk`, { waitUntil: 'domcontentloaded' });

    await page.waitForSelector('#login', { timeout: 15000 });
    await page.$eval('#login', (el) => (el.value = ''));
    await page.$eval('#password', (el) => (el.value = ''));

    await page.type('#login', email);
    await page.type('#password', PASSWORD);

    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 }),
        page.click('button[type="submit"]'),
    ]);

    await page.waitForSelector('main', { timeout: 15000 });
    await new Promise((resolve) => setTimeout(resolve, 400));
}

async function shoot(page, name, { viewport = DESKTOP, fullPage = true, wait = 900 } = {}) {
    await page.setViewport(viewport);
    await new Promise((resolve) => setTimeout(resolve, wait));

    const file = path.join(OUTPUT_DIR, `${name}.png`);
    await page.screenshot({ path: file, fullPage });

    const size = (fs.statSync(file).size / 1024).toFixed(1);
    console.log(`  ✓ ${name}.png (${size} kB)`);
}

/**
 * Konteks terisolasi per role: menghindari sisa cookie/localStorage
 * dari login sebelumnya.
 */
async function freshPage(browser) {
    const context = typeof browser.createBrowserContext === 'function'
        ? await browser.createBrowserContext()
        : await browser.createIncognitoBrowserContext();

    const page = await context.newPage();
    await page.setViewport(DESKTOP);

    // Headless Chrome dapat default ke mode gelap; paksa terang agar konsisten.
    // Reduced motion juga mematikan animasi Chart.js sehingga grafik sudah final.
    await page.emulateMediaFeatures([
        { name: 'prefers-color-scheme', value: 'light' },
        { name: 'prefers-reduced-motion', value: 'reduce' },
    ]);

    return page;
}

async function main() {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });

    const browser = await puppeteer.launch({
        executablePath: resolveChrome(),
        headless: 'new',
        args: ['--no-sandbox', '--disable-dev-shm-usage', '--hide-scrollbars'],
    });

    let page = await freshPage(browser);

    console.log('Halaman publik');
    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle2' });
    await shoot(page, '01-landing-desktop');
    await shoot(page, '02-landing-mobile', { viewport: MOBILE });

    await page.goto(`${BASE_URL}/masuk`, { waitUntil: 'networkidle2' });
    await shoot(page, '03-login-desktop');

    console.log('Mode gelap (landing)');
    await page.evaluate(() => localStorage.setItem('simas-theme', 'dark'));
    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle2' });
    await shoot(page, '04-landing-dark');

    await page.close();

    console.log('Panel admin');
    page = await freshPage(browser);
    await login(page, ACCOUNTS.admin);
    await shoot(page, '05-admin-dashboard');
    await shoot(page, '06-admin-dashboard-mobile', { viewport: MOBILE });

    await page.goto(`${BASE_URL}/admin/students`, { waitUntil: 'networkidle2' });
    await shoot(page, '07-admin-siswa');

    await page.goto(`${BASE_URL}/admin/schedules`, { waitUntil: 'networkidle2' });
    await shoot(page, '08-admin-jadwal');

    await page.goto(`${BASE_URL}/admin/attendance`, { waitUntil: 'networkidle2' });
    await shoot(page, '09-admin-rekap-absensi');

    await page.goto(`${BASE_URL}/admin/audit-logs`, { waitUntil: 'networkidle2' });
    await shoot(page, '10-admin-audit-log');

    // Mode gelap pada dashboard.
    await page.evaluate(() => localStorage.setItem('simas-theme', 'dark'));
    await page.goto(`${BASE_URL}/admin/dashboard`, { waitUntil: 'networkidle2' });
    await shoot(page, '11-admin-dashboard-dark');
    await page.evaluate(() => localStorage.setItem('simas-theme', 'light'));
    await page.close();

    console.log('Portal guru');
    page = await freshPage(browser);
    await login(page, ACCOUNTS.guru);
    await shoot(page, '12-guru-dashboard');

    await page.goto(`${BASE_URL}/guru/attendance`, { waitUntil: 'networkidle2' });
    await shoot(page, '13-guru-daftar-sesi');

    const fillLink = await page.evaluate(() => {
        const link = document.querySelector('a[href*="/fill?date="]');

        return link ? link.getAttribute('href') : null;
    });

    if (fillLink) {
        const url = fillLink.startsWith('http') ? fillLink : `${BASE_URL}${fillLink}`;

        await page.goto(url, { waitUntil: 'networkidle2' });
        await shoot(page, '14-guru-form-absensi');
        await shoot(page, '15-guru-form-absensi-mobile', { viewport: MOBILE });
    } else {
        console.warn('  ! Tautan form absensi tidak ditemukan (lewati 14 & 15).');
    }

    console.log('Portal siswa');
    await page.close();
    page = await freshPage(browser);
    await login(page, ACCOUNTS.siswa);
    await shoot(page, '16-siswa-dashboard');

    await page.goto(`${BASE_URL}/siswa/attendance`, { waitUntil: 'networkidle2' });
    await shoot(page, '17-siswa-riwayat');

    await page.goto(`${BASE_URL}/siswa/schedule`, { waitUntil: 'networkidle2' });
    await shoot(page, '18-siswa-jadwal');

    console.log('Halaman error');
    await page.goto(`${BASE_URL}/halaman-tidak-ada`, { waitUntil: 'networkidle2' });
    await shoot(page, '19-error-404');

    await browser.close();
    console.log(`\nSelesai. Tangkapan layar tersimpan di ${OUTPUT_DIR}`);
}

main().catch((error) => {
    console.error('\nGagal mengambil tangkapan layar:', error.message);
    process.exit(1);
});
