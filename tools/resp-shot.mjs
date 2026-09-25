// Captura seções da página em várias larguras (mobile/tablet) para revisão visual.
//
// Uso:
//   node tools/resp-shot.mjs <id-da-seção|header|footer> [larguras] [--out pasta] [--open-menu]
//   ex.: node tools/resp-shot.mjs sabores 360,390,768,1024 --out _shots/resp
//
// - Página completa (http://localhost:$PORT/, padrão 49678), imagens lazy forçadas
//   para eager, prefers-reduced-motion: reduce (layout final, sem entradas).
// - Grava <out>/<id>-<largura>.png (só a seção) e imprime altura da seção,
//   overflow horizontal da página e elementos da seção que vazam da tela.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer-core';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const args = process.argv.slice(2);
const id = args[0];
if (!id) { console.error('uso: node tools/resp-shot.mjs <id> [360,390,768,1024] [--out dir] [--open-menu]'); process.exit(1); }
const widths = (args[1] && !args[1].startsWith('--') ? args[1] : '360,390,768,1024').split(',').map(Number);
const oi = args.indexOf('--out');
const out = path.resolve(ROOT, oi > -1 ? args[oi + 1] : '_shots/resp');
const openMenu = args.includes('--open-menu');
const port = process.env.PORT || '49678';
fs.mkdirSync(out, { recursive: true });

const CHROME = ['C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => fs.existsSync(p));
const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--hide-scrollbars'] });
try {
  for (const w of widths) {
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    await page.evaluateOnNewDocument(() => {
      document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('img[loading="lazy"]').forEach(i => { i.loading = 'eager'; }));
    });
    await page.setViewport({ width: w, height: w < 768 ? 800 : 1024, deviceScaleFactor: 1, isMobile: w < 1024, hasTouch: w < 1024 });
    await page.goto(`http://localhost:${port}/`, { waitUntil: 'load', timeout: 90000 });
    await page.evaluate(() => document.fonts.ready);
    await page.evaluate(async () => {
      await Promise.all([...document.images].map(i => i.complete ? 0 : new Promise(r => { i.onload = i.onerror = r; setTimeout(r, 8000); })));
    });
    if (openMenu) { const t = await page.$('[data-menu-toggle]'); if (t) { await t.click(); await new Promise(r => setTimeout(r, 400)); } }
    const sel = id === 'header' ? '.site-header' : id === 'footer' ? 'footer.site-footer' : `#${id}`;
    const info = await page.evaluate((sel) => {
      const el = document.querySelector(sel);
      if (!el) return null;
      const W = document.documentElement.clientWidth;
      const b = el.getBoundingClientRect();
      const vazam = [...el.querySelectorAll('*')].filter(e => {
        const r = e.getBoundingClientRect();
        if (!r.width || getComputedStyle(e).position === 'fixed') return false;
        if (e.closest('[data-carousel-track], [data-cardapio-track], .sabores__abas, .depoimentos__track, .cardapio__grid')) return false;
        return r.right > W + 1 || r.left < -1;
      }).slice(0, 10).map(e => (e.className && typeof e.className === 'string' ? '.' + e.className.trim().split(/\s+/)[0] : e.tagName.toLowerCase()) + ` [${Math.round(e.getBoundingClientRect().left)}→${Math.round(e.getBoundingClientRect().right)}]`);
      return { y: b.top + scrollY, h: b.height, scrollW: document.documentElement.scrollWidth, W, vazam };
    }, sel);
    if (!info) { console.log(`${w}px: seletor ${sel} não encontrado`); await page.close(); continue; }
    const file = path.join(out, `${id}-${w}.png`);
    await page.screenshot({ path: file, clip: { x: 0, y: openMenu ? 0 : info.y, width: w, height: openMenu ? Math.min(1600, await page.evaluate(() => innerHeight)) : Math.min(info.h, 6000) }, captureBeyondViewport: true });
    console.log(`${w}px: altura ${Math.round(info.h)} | scrollWidth ${info.scrollW}${info.scrollW > info.W ? ' ✗ OVERFLOW' : ''} | vazando: ${info.vazam.length ? info.vazam.join(', ') : 'nada'}${errors.length ? ' | ERROS: ' + errors.join('; ') : ''} → ${path.relative(ROOT, file)}`);
    await page.close();
  }
} finally {
  await browser.close();
}
