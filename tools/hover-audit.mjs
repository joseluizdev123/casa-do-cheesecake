// Auditoria de interações: passa o cursor em TODO elemento clicável visível da página
// e verifica se algo muda (cor, fundo, borda, sublinhado, filtro, ::before).
// Uso: node tools/hover-audit.mjs [largura=390] [--only-fail]
import fs from 'node:fs';
import puppeteer from 'puppeteer-core';
const W = +process.argv[2] || 390;
const onlyFail = process.argv.includes('--only-fail');
const CHROME = ['C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => fs.existsSync(p));
const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
const p = await b.newPage();
await p.setViewport({ width: W, height: 900, deviceScaleFactor: 1 });
await p.goto(`http://localhost:${process.env.PORT || 5500}/`, { waitUntil: 'networkidle0' });
await p.evaluate(() => document.fonts.ready);
await p.addStyleTag({ content: 'html{scroll-behavior:auto!important} *,*::before,*::after{transition:none!important;animation:none!important}' });
const PROPS = ['color', 'backgroundColor', 'borderTopColor', 'textDecorationLine', 'filter', 'opacity', 'transform'];
async function audit(label) {
  const handles = await p.$$('a[href], button, [role="tab"], summary');
  const rows = [];
  for (const h of handles) {
    const info = await h.evaluate((el) => {
      const r = el.getBoundingClientRect(); const cs = getComputedStyle(el);
      const vis = r.width > 0 && r.height > 0 && cs.visibility !== 'hidden' && cs.display !== 'none' && !el.closest('[hidden]');
      const name = (el.className && typeof el.className === 'string' ? '.' + el.className.trim().split(/\s+/).join('.') : el.tagName.toLowerCase());
      return { vis, name, text: (el.textContent || el.getAttribute('aria-label') || '').trim().replace(/\s+/g, ' ').slice(0, 26) };
    });
    if (!info.vis) continue;
    await h.evaluate(el => el.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'instant' }));
    await new Promise(r => setTimeout(r, 60));
    const box = await h.boundingBox(); if (!box) continue;
    const cx = box.x + box.width / 2, cy = box.y + box.height / 2;
    if (cx < 0 || cx > W || cy < 0 || cy > 900) { rows.push({ ...info, res: 'fora da tela (carrossel)' }); continue; }
    const occl = await p.evaluate((x, y, sel) => { const t = document.elementFromPoint(x, y); return t ? (t.closest('a,button,[role="tab"],summary') === null ? 'não-clicável' : '') : 'nada'; }, cx, cy);
    const snap = () => h.evaluate((el, props) => {
      const all = [el, ...el.querySelectorAll('*')].slice(0, 8);
      return all.map(e => { const cs = getComputedStyle(e); const bf = getComputedStyle(e, '::before'); return props.map(k => cs[k]).join('|') + '|' + bf.backgroundColor + '|' + bf.borderTopColor; }).join('#');
    }, PROPS);
    await p.mouse.move(1, 1); await new Promise(r => setTimeout(r, 250));
    const a = await snap();
    await p.mouse.move(cx, cy); await new Promise(r => setTimeout(r, 300));
    const hv = await snap();
    await p.mouse.move(1, 1);
    rows.push({ ...info, res: a !== hv ? 'muda ✓' : ('SEM MUDANÇA ✗' + (occl ? ' (coberto: ' + occl + ')' : '')) });
  }
  return rows;
}
let rows = await audit('página');
if (W < 1024) {
  await p.evaluate(() => window.scrollTo(0, 0));
  await p.click('[data-menu-toggle]'); await new Promise(r => setTimeout(r, 300));
  const menu = await p.$$eval('#menu-mobile a', els => els.length);
  rows.push({ name: '— menu aberto —', text: menu + ' links', res: '' });
}
const seen = new Map();
for (const r of rows) { const k = r.name + '|' + r.res; seen.set(k, (seen.get(k) || 0) + 1); }
console.log(`== ${W}px — ${rows.length} clicáveis visíveis`);
for (const [k, n] of seen) { const [name, res] = k.split('|'); if (onlyFail && !res.includes('✗')) continue; console.log(`${res.padEnd(40)} ×${n}  ${name}`); }
await b.close();
