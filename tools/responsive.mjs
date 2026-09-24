// QA responsivo (o Figma só tem desktop; aqui não existe referência pixel a pixel,
// então o gate é objetivo: sem overflow, sem texto minúsculo, sem alvo de toque pequeno,
// sem imagem distorcida, sem sobreposição acidental de texto).
//
// Uso:
//   node tools/responsive.mjs <slug> [--widths 1280,1024,768,390,375] [--path /s/<slug>] [--open-menu]
//   slug "page" = página inteira (/). Screenshots full-page em _shots/resp-<slug>-<w>.png
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer-core';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const args = process.argv.slice(2);
const slug = args[0];
if (!slug) { console.error('uso: node tools/responsive.mjs <slug|page> [--widths 1280,1024,768,390,375] [--path /s/slug] [--open-menu]'); process.exit(1); }
const opt = (n, d) => { const i = args.indexOf(n); return i > -1 ? args[i + 1] : d; };
const widths = opt('--widths', '1280,1024,768,390,375').split(',').map(Number);
const port = process.env.PORT || '5500';
const urlPath = opt('--path', slug === 'page' ? '/' : `/s/${slug}`);
const openMenu = args.includes('--open-menu');
const CHROME = ['C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => fs.existsSync(p));
fs.mkdirSync(path.join(ROOT, '_shots'), { recursive: true });

const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--hide-scrollbars'] });
let failures = 0;
try {
  for (const w of widths) {
    const page = await browser.newPage();
    const mobile = w < 768;
    await page.setViewport({ width: w, height: mobile ? 844 : 900, deviceScaleFactor: 1, isMobile: mobile, hasTouch: mobile });
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    page.on('console', m => { if (m.type() === 'error') errors.push(m.text()); });
    page.on('response', r => { if (r.status() >= 400 && !r.url().endsWith('favicon.ico')) errors.push(`${r.status()} ${r.url()}`); });
    await page.goto(`http://localhost:${port}${urlPath}`, { waitUntil: 'networkidle0', timeout: 60000 });
    await page.evaluate(() => document.fonts.ready);
    if (openMenu) { await page.click('[data-menu-toggle]').catch(() => {}); await new Promise(r => setTimeout(r, 300)); }
    await new Promise(r => setTimeout(r, 250));

    const report = await page.evaluate((mobile) => {
      const vw = document.documentElement.clientWidth;
      const name = el => el.tagName.toLowerCase() + (el.id ? '#' + el.id : '') + (typeof el.className === 'string' && el.className.trim() ? '.' + el.className.trim().split(/\s+/).slice(0, 2).join('.') : '');
      const visible = el => { const cs = getComputedStyle(el); const b = el.getBoundingClientRect(); return cs.visibility !== 'hidden' && cs.display !== 'none' && +cs.opacity > 0 && b.width > 0 && b.height > 0; };
      // elementos cujo conteúdo recortado por um ancestral overflow:hidden/clip não contam
      const clipped = el => { for (let p = el.parentElement; p && p !== document.body; p = p.parentElement) { const o = getComputedStyle(p); if (/(hidden|clip|auto|scroll)/.test(o.overflowX)) { const pb = p.getBoundingClientRect(); if (pb.right <= vw + 0.5 && pb.left >= -0.5) return true; } } return false; };
      const all = [...document.querySelectorAll('body *')].filter(visible);
      const overflow = all.filter(el => { const b = el.getBoundingClientRect(); return (b.right > vw + 1 || b.left < -1) && !clipped(el); })
        .map(el => { const b = el.getBoundingClientRect(); return `${name(el)} [${Math.round(b.left)}→${Math.round(b.right)}]`; });
      const texts = all.filter(el => [...el.childNodes].some(n => n.nodeType === 3 && n.textContent.trim()));
      const tiny = texts.filter(el => parseFloat(getComputedStyle(el).fontSize) < 12).map(el => `${name(el)} ${getComputedStyle(el).fontSize}`);
      const targets = mobile ? [...document.querySelectorAll('a[href], button, [role="tab"], summary')].filter(visible).filter(el => { const b = el.getBoundingClientRect(); return b.width < 44 || b.height < 24 || (b.height < 44 && b.width < 44); })
        .map(el => { const b = el.getBoundingClientRect(); return `${name(el)} ${Math.round(b.width)}×${Math.round(b.height)} "${(el.textContent || el.getAttribute('aria-label') || '').trim().slice(0, 24)}"`; }) : [];
      const distorted = [...document.querySelectorAll('img')].filter(visible).filter(img => {
        const cs = getComputedStyle(img); if (cs.objectFit !== 'fill' || !img.naturalWidth) return false;
        const b = img.getBoundingClientRect(); const r1 = img.naturalWidth / img.naturalHeight, r2 = b.width / b.height;
        return Math.abs(r1 - r2) / r1 > 0.03;
      }).map(img => `${img.getAttribute('src')} natural ${img.naturalWidth}×${img.naturalHeight} render ${Math.round(img.getBoundingClientRect().width)}×${Math.round(img.getBoundingClientRect().height)}`);
      // blocos de texto que se sobrepõem (bounding boxes de elementos-folha de texto que se cruzam)
      const leaves = texts.filter(el => !el.querySelector('*') || getComputedStyle(el).display !== 'inline');
      const overlaps = [];
      for (let i = 0; i < leaves.length && overlaps.length < 10; i++) for (let j = i + 1; j < leaves.length; j++) {
        const a = leaves[i], b = leaves[j]; if (a.contains(b) || b.contains(a)) continue;
        const A = a.getBoundingClientRect(), B = b.getBoundingClientRect();
        const ix = Math.min(A.right, B.right) - Math.max(A.left, B.left), iy = Math.min(A.bottom, B.bottom) - Math.max(A.top, B.top);
        if (ix > 4 && iy > 4) overlaps.push(`${name(a)} ✕ ${name(b)} (${Math.round(ix)}×${Math.round(iy)})`);
      }
      return { vw, scrollW: document.documentElement.scrollWidth, height: document.documentElement.scrollHeight, overflow: overflow.slice(0, 12), tiny: tiny.slice(0, 12), targets: targets.slice(0, 15), distorted, overlaps };
    }, mobile);

    const file = `_shots/resp-${slug}-${w}${openMenu ? '-menu' : ''}.png`;
    await page.screenshot({ path: path.join(ROOT, file), fullPage: true });
    const bad = (report.scrollW > report.vw) || report.overflow.length || report.tiny.length || report.distorted.length || report.overlaps.length || errors.length;
    if (bad) failures++;
    console.log(`\n=== ${w}px ${bad ? '✗' : '✓'}  scrollWidth=${report.scrollW} (viewport ${report.vw}) altura=${report.height} → ${file}`);
    if (report.overflow.length) console.log('  OVERFLOW:', report.overflow.join(' | '));
    if (report.tiny.length) console.log('  TEXTO < 12px:', report.tiny.join(' | '));
    if (report.targets.length) console.log('  ALVO DE TOQUE PEQUENO (aviso):', report.targets.join(' | '));
    if (report.distorted.length) console.log('  IMAGEM DISTORCIDA:', report.distorted.join(' | '));
    if (report.overlaps.length) console.log('  TEXTO SOBREPOSTO:', report.overlaps.join(' | '));
    if (errors.length) console.log('  ERROS:', errors.join(' | '));
    await page.close();
  }
} finally {
  await browser.close();
}
console.log(`\n${failures ? failures + ' largura(s) com problema' : 'todas as larguras OK'} (alvos de toque são aviso; confira os screenshots)`);
