// Auditoria de animações (ver MOTION.md).
//
// Uso: node tools/motion-audit.mjs <slug|page> [--widths 1440,390] [--frames]
//   slug "page" = página inteira (/); outro slug = /s/<slug>.
//
// Para cada largura, com animação LIGADA:
//  1. CLS (Cumulative Layout Shift) durante carregamento + rolagem até o fim. Meta: < 0.01.
//  2. Elementos que nunca aparecem: [data-reveal] ainda presente após rolar tudo.
//  3. Acima da dobra: tudo revelado em até 2.5s sem rolar.
//  4. Estado final (movimento ambiente congelado, contadores terminados) comparado
//     pixel a pixel com a versão estática (prefers-reduced-motion). Meta: ~0%.
//  5. Erros de console.
//  --frames grava quadros das entradas em _shots/motion-<slug>-<w>-*.png
//  (topo em 0/250/600/1200 ms e cada passo de rolagem 300 ms depois de chegar) — ABRA para julgar.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer-core';
import { PNG } from 'pngjs';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const args = process.argv.slice(2);
const slug = args[0] || 'page';
const opt = (n, d) => { const i = args.indexOf(n); return i > -1 ? args[i + 1] : d; };
const widths = opt('--widths', '1440,390').split(',').map(Number);
const frames = args.includes('--frames');
const port = process.env.PORT || '5500';
const url = `http://localhost:${port}${slug === 'page' ? '/' : '/s/' + slug}`;
const CHROME = ['C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => fs.existsSync(p));
const OUT = path.join(ROOT, '_shots');
fs.mkdirSync(OUT, { recursive: true });
const wait = ms => new Promise(r => setTimeout(r, ms));

// congela o movimento contínuo e leva as quedas ligadas à rolagem ao estado pousado
const FREEZE = '.motion-float,.motion-spin,.motion-wiggle,.sobre__selo img,.cta-final__selo img{animation:none!important} html{scroll-behavior:auto!important} .sabores__painel,.cta-final__fatia{translate:none!important;rotate:none!important;opacity:1!important}';

async function settle(page) {
  await page.evaluate(async () => {
    await document.fonts.ready;
    document.querySelectorAll('img[loading="lazy"]').forEach(i => { i.loading = 'eager'; });
    const all = Promise.all([...document.images].map(i => (i.complete ? i.decode() : new Promise(r => { i.onload = i.onerror = r; })).catch(() => {})));
    await Promise.race([all, new Promise(r => setTimeout(r, 6000))]);
  });
}

// Viewport do tamanho da página inteira: tudo "em tela" (lazy carrega, animações
// dependentes de visibilidade — ex.: a moto da seção 09 — chegam ao estado final).
async function fullShot(page, file, w, mobile) {
  const total = await page.evaluate(() => document.documentElement.scrollHeight);
  await page.setViewport({ width: w, height: total, deviceScaleFactor: 1, isMobile: mobile, hasTouch: mobile });
  await settle(page);
  await wait(2500);
  await page.screenshot({ path: file, captureBeyondViewport: false });
}

const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, protocolTimeout: 240000, args: ['--hide-scrollbars'] });
let fail = 0;
try {
  for (const w of widths) {
    const h = w < 768 ? 844 : 900;
    const mobile = w < 768;
    // ---------- com animação ----------
    const page = await browser.newPage();
    await page.setViewport({ width: w, height: h, deviceScaleFactor: 1, isMobile: mobile, hasTouch: mobile });
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    page.on('console', m => { if (m.type() === 'error') errors.push(m.text()); });
    await page.evaluateOnNewDocument(() => {
      window.__cls = 0; window.__shifts = [];
      new PerformanceObserver(list => {
        for (const e of list.getEntries()) {
          if (e.hadRecentInput) continue;
          window.__cls += e.value;
          window.__shifts.push({ v: +e.value.toFixed(4), src: (e.sources || []).map(s => s.node && s.node.nodeType === 1 ? (s.node.className || s.node.tagName) + '' : '').filter(Boolean).slice(0, 3) });
        }
      }).observe({ type: 'layout-shift', buffered: true });
    });
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 90000 });
    const t0 = Date.now();
    if (frames) {
      for (const t of [0, 250, 600, 1200]) {
        const dt = t - (Date.now() - t0); if (dt > 0) await wait(dt);
        await page.screenshot({ path: path.join(OUT, `motion-${slug}-${w}-top-${t}ms.png`) });
      }
    }
    await page.waitForNetworkIdle({ idleTime: 300, timeout: 30000 }).catch(() => {});
    await wait(2500);
    const aboveFold = await page.evaluate((vh) => [...document.querySelectorAll('[data-reveal]')]
      .filter(el => { const r = el.getBoundingClientRect(); return r.top < vh && r.bottom > 0 && r.width > 0; })
      .map(el => (el.className || el.tagName) + ''), h);
    // rola até o fim em passos
    const total = await page.evaluate(() => document.documentElement.scrollHeight);
    let step = 0;
    for (let y = 0; y < total; y += Math.round(h * 0.6)) {
      await page.evaluate(y => window.scrollTo(0, y), y);
      await wait(300);
      if (frames) await page.screenshot({ path: path.join(OUT, `motion-${slug}-${w}-scroll-${String(step).padStart(2, '0')}.png`) });
      await wait(700);
      step++;
    }
    await page.evaluate(() => window.scrollTo(0, document.documentElement.scrollHeight));
    await wait(2500);
    const never = await page.evaluate(() => [...document.querySelectorAll('[data-reveal]')].filter(el => el.getBoundingClientRect().width > 0 && getComputedStyle(el).display !== 'none').map(el => `${(el.className || el.tagName) + ''} (${el.getAttribute('data-reveal')})`));
    const cls = await page.evaluate(() => ({ cls: +window.__cls.toFixed(4), shifts: window.__shifts.slice(0, 6) }));
    await page.addStyleTag({ content: FREEZE });
    await page.evaluate(() => window.scrollTo(0, 0));
    const motionShot = path.join(OUT, `motion-${slug}-${w}-final.png`);
    await fullShot(page, motionShot, w, mobile);
    await page.close();

    // ---------- estático (reduced motion) ----------
    const still = await browser.newPage();
    await still.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    await still.setViewport({ width: w, height: h, deviceScaleFactor: 1, isMobile: mobile, hasTouch: mobile });
    await still.goto(url, { waitUntil: 'networkidle0', timeout: 90000 });
    await still.addStyleTag({ content: FREEZE });
    const stillShot = path.join(OUT, `motion-${slug}-${w}-static.png`);
    await fullShot(still, stillShot, w, mobile);
    const hiddenStatic = await still.evaluate(() => [...document.querySelectorAll('main *, .site-footer *')].filter(el => { const cs = getComputedStyle(el); const r = el.getBoundingClientRect(); return r.width > 0 && r.height > 0 && +cs.opacity === 0 && !el.closest('[aria-hidden="true"],[hidden]'); }).slice(0, 8).map(el => (el.className || el.tagName) + ''));
    await still.close();

    // ---------- diff final × estático ----------
    const a = PNG.sync.read(fs.readFileSync(motionShot)), b = PNG.sync.read(fs.readFileSync(stillShot));
    const H = Math.min(a.height, b.height);
    let bad = 0, n = 0; const rows = new Map();
    for (let y = 0; y < H; y++) for (let x = 0; x < Math.min(a.width, b.width); x++) {
      const i = (y * a.width + x) * 4, j = (y * b.width + x) * 4;
      const d = Math.max(Math.abs(a.data[i] - b.data[j]), Math.abs(a.data[i + 1] - b.data[j + 1]), Math.abs(a.data[i + 2] - b.data[j + 2]));
      n++; if (d > 48) { bad++; const band = Math.floor(y / 50) * 50; rows.set(band, (rows.get(band) || 0) + 1); }
    }
    const pct = bad / n * 100;
    const topBands = [...rows.entries()].sort((p, q) => q[1] - p[1]).slice(0, 5).map(([y, c]) => `${y}-${y + 50}:${c}`);

    const ok = cls.cls < 0.01 && !never.length && !aboveFold.length && pct < 0.1 && !errors.length && a.height === b.height;
    if (!ok) fail++;
    console.log(`\n=== ${w}px ${ok ? '✓' : '✗'}  ${url}`);
    console.log(`  CLS: ${cls.cls}${cls.cls >= 0.01 ? '  ✗ ' + JSON.stringify(cls.shifts) : ''}`);
    console.log(`  acima da dobra ainda escondido após 2.5s: ${aboveFold.length ? aboveFold.join(', ') + ' ✗' : 'nenhum'}`);
    console.log(`  nunca revelados após rolar tudo: ${never.length ? never.join(', ') + ' ✗' : 'nenhum'}`);
    console.log(`  estado final × estático: ${pct.toFixed(3)}% pixels diferentes${a.height !== b.height ? ` ✗ alturas ${a.height} × ${b.height}` : ''}${topBands.length ? ' · faixas y: ' + topBands.join(' ') : ''}`);
    console.log(`  estático com opacity 0 visível: ${hiddenStatic.length ? hiddenStatic.join(', ') + ' (verifique se é do design)' : 'nenhum'}`);
    console.log(`  erros de console: ${errors.length ? errors.join(' | ') + ' ✗' : 'nenhum'}`);
    if (frames) console.log(`  quadros: _shots/motion-${slug}-${w}-top-*.png e -scroll-*.png`);
  }
} finally {
  await browser.close();
}
console.log(fail ? `\n${fail} largura(s) com problema` : '\ntudo OK');
