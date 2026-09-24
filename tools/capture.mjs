// Captura headless via puppeteer-core: espera networkidle0, fontes e decode() das imagens.
// Uso: node tools/capture.mjs <url> <largura> <altura> <saida.png> [--full]
import fs from 'node:fs';
import puppeteer from 'puppeteer-core';

const [url, w, h, out] = process.argv.slice(2);
const full = process.argv.includes('--full');
const CHROME = ['C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => fs.existsSync(p));
const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--hide-scrollbars', '--force-device-scale-factor=1'] });
const kill = setTimeout(() => { try { browser.process()?.kill('SIGKILL'); } catch {} process.exit(2); }, 120000);
try {
  const page = await browser.newPage();
  await page.setViewport({ width: +w, height: +h, deviceScaleFactor: 1 });
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 90000 });
  await page.evaluate(async () => {
    await document.fonts.ready;
    document.querySelectorAll('img[loading="lazy"]').forEach(i => { i.loading = 'eager'; });
    await Promise.all([...document.images].map(i => (i.complete ? i.decode() : new Promise(r => { i.onload = i.onerror = r; })).catch(() => {})));
  });
  await new Promise(r => setTimeout(r, 200));
  await page.screenshot({ path: out, fullPage: full });
} finally {
  clearTimeout(kill);
  await Promise.race([browser.close(), new Promise(r => setTimeout(r, 5000))]);
  try { browser.process()?.kill('SIGKILL'); } catch {}
}
