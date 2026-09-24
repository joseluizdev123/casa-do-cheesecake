// Inspeciona um site de referência em várias larguras: screenshots full-page + medidas
// de header, tipografia, seções, botões, grids/carrosséis e imagens.
// Uso: node tools/inspect-ref.mjs <url> [--widths 390,768,1024] [--out _ref/mobile-ref]
import fs from 'node:fs';
import path from 'node:path';
import puppeteer from 'puppeteer-core';

const args = process.argv.slice(2);
const url = args[0];
const opt = (n, d) => { const i = args.indexOf(n); return i > -1 ? args[i + 1] : d; };
const widths = opt('--widths', '390,768,1024').split(',').map(Number);
const out = opt('--out', '_ref/mobile-ref');
fs.mkdirSync(out, { recursive: true });
const CHROME = ['C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => fs.existsSync(p));
const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--hide-scrollbars'] });
const result = {};
try {
  for (const w of widths) {
    const page = await browser.newPage();
    const mobile = w < 768;
    await page.setViewport({ width: w, height: mobile ? 844 : 1024, deviceScaleFactor: 1, isMobile: mobile, hasTouch: mobile });
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 90000 });
    await page.evaluate(async () => {
      await document.fonts.ready;
      // força animações de entrada / lazy a aparecerem
      for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 60)); }
      window.scrollTo(0, 0);
      document.querySelectorAll('img[loading="lazy"]').forEach(i => { i.loading = 'eager'; });
      document.querySelectorAll('*').forEach(el => { const cs = getComputedStyle(el); if (+cs.opacity === 0 && el.getBoundingClientRect().height > 0) el.style.opacity = '1'; });
    });
    await new Promise(r => setTimeout(r, 800));
    const data = await page.evaluate(() => {
      const r = v => Math.round(v * 10) / 10;
      const name = el => el.tagName.toLowerCase() + (el.id ? '#' + el.id : '') + (typeof el.className === 'string' && el.className.trim() ? '.' + el.className.trim().split(/\s+/).slice(0, 2).join('.') : '');
      const box = el => { const b = el.getBoundingClientRect(); return { x: r(b.x), y: r(b.y + scrollY), w: r(b.width), h: r(b.height) }; };
      const typo = el => { const cs = getComputedStyle(el); return { font: cs.fontFamily.split(',')[0].replace(/"/g, ''), size: cs.fontSize, lh: cs.lineHeight, weight: cs.fontWeight, ls: cs.letterSpacing, tt: cs.textTransform, color: cs.color }; };
      const vis = el => { const cs = getComputedStyle(el); const b = el.getBoundingClientRect(); return cs.display !== 'none' && cs.visibility !== 'hidden' && b.width > 0 && b.height > 0; };
      const header = document.querySelector('header');
      const hdr = header && {
        el: name(header), box: box(header), position: getComputedStyle(header).position, bg: getComputedStyle(header).backgroundColor,
        children: [...header.querySelectorAll('a, button, img, svg')].filter(vis).slice(0, 12).map(e => ({ el: name(e), box: box(e), text: (e.textContent || e.getAttribute('aria-label') || '').trim().slice(0, 30) })),
      };
      const heads = [...document.querySelectorAll('h1,h2,h3,h4')].filter(vis).map(e => ({ tag: e.tagName, text: e.textContent.trim().replace(/\s+/g, ' ').slice(0, 50), ...typo(e), box: box(e) }));
      const paras = [...document.querySelectorAll('p, li')].filter(vis).filter(e => e.textContent.trim().length > 40).slice(0, 25).map(e => ({ el: name(e), text: e.textContent.trim().slice(0, 40), ...typo(e), w: r(e.getBoundingClientRect().width) }));
      const small = [...document.querySelectorAll('span, small, p, a, li, label')].filter(vis).filter(e => [...e.childNodes].some(n => n.nodeType === 3 && n.textContent.trim())).map(e => parseFloat(getComputedStyle(e).fontSize));
      const sizes = {}; small.forEach(s => { sizes[s] = (sizes[s] || 0) + 1; });
      const sections = [...document.querySelectorAll('section, footer, main > div')].filter(vis).map(e => { const cs = getComputedStyle(e); return { el: name(e), box: box(e), pad: cs.padding, bg: cs.backgroundColor }; });
      const containers = [...document.querySelectorAll('section *')].filter(vis).filter(e => { const cs = getComputedStyle(e); return (cs.display.includes('grid') || cs.display.includes('flex')) && e.children.length >= 2; }).slice(0, 60).map(e => { const cs = getComputedStyle(e); return { el: name(e), display: cs.display, dir: cs.flexDirection, cols: cs.gridTemplateColumns, gap: cs.gap, pad: cs.padding, w: r(e.getBoundingClientRect().width), ox: cs.overflowX, snap: cs.scrollSnapType }; });
      const scrollers = [...document.querySelectorAll('*')].filter(vis).filter(e => { const cs = getComputedStyle(e); return /(auto|scroll)/.test(cs.overflowX) && e.scrollWidth > e.clientWidth + 4; }).map(e => { const cs = getComputedStyle(e); const kids = [...e.children].filter(vis); return { el: name(e), w: r(e.clientWidth), scrollW: e.scrollWidth, snap: cs.scrollSnapType, gap: cs.gap, pad: cs.padding, child_w: kids[0] && r(kids[0].getBoundingClientRect().width), n: kids.length }; });
      const buttons = [...document.querySelectorAll('a, button')].filter(vis).filter(e => { const cs = getComputedStyle(e); return cs.backgroundColor !== 'rgba(0, 0, 0, 0)' || parseFloat(cs.borderTopWidth) > 0; }).slice(0, 20).map(e => { const cs = getComputedStyle(e); const b = e.getBoundingClientRect(); return { el: name(e), text: e.textContent.trim().slice(0, 30), w: r(b.width), h: r(b.height), pad: cs.padding, size: cs.fontSize, radius: cs.borderRadius, bg: cs.backgroundColor, font: cs.fontFamily.split(',')[0] }; });
      const cards = [...document.querySelectorAll('article, [class*="card"]')].filter(vis).slice(0, 15).map(e => { const cs = getComputedStyle(e); return { el: name(e), box: box(e), pad: cs.padding, radius: cs.borderRadius, bg: cs.backgroundColor, shadow: cs.boxShadow !== 'none' }; });
      const imgs = [...document.querySelectorAll('img')].filter(vis).slice(0, 25).map(e => { const cs = getComputedStyle(e); return { src: (e.getAttribute('src') || '').split('/').pop().slice(0, 40), box: box(e), fit: cs.objectFit, radius: cs.borderRadius }; });
      return { vw: document.documentElement.clientWidth, scrollW: document.documentElement.scrollWidth, height: document.documentElement.scrollHeight, bodyFont: getComputedStyle(document.body).fontFamily, bodySize: getComputedStyle(document.body).fontSize, header: hdr, heads, paras, fontSizeHistogram: sizes, sections, containers, scrollers, buttons, cards, imgs };
    });
    result[w] = data;
    await page.screenshot({ path: path.join(out, `ref-${w}.png`), fullPage: true });
    // menu aberto (se houver toggle)
    const toggle = await page.$('header button, [aria-controls], .menu-toggle, .hamburger, [class*="toggle"]');
    if (toggle && w < 1024) {
      await toggle.click().catch(() => {});
      await new Promise(r => setTimeout(r, 600));
      await page.screenshot({ path: path.join(out, `ref-${w}-menu.png`) });
    }
    await page.close();
  }
} finally {
  await browser.close();
}
fs.writeFileSync(path.join(out, 'measures.json'), JSON.stringify(result, null, 1));
console.log('ok', Object.keys(result).map(w => `${w}: h=${result[w].height} scrollW=${result[w].scrollW}`).join(' | '));
