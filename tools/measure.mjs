// Diff numérico DOM × Figma.
//
// Uso:
//   node tools/measure.mjs <slug> [--meta _ref/meta-<slug>.xml] [--path /s/<slug>] [--height 1000] [--width 1440] [--styles]
//
// - Abre http://localhost:5500/s/<slug> em Chrome headless 1440px, DPR 1, espera fontes.
// - Mede todo elemento com [data-figma-node="<id>"] (caixa relativa ao elemento raiz da
//   seção = primeiro [data-figma-node] do documento) + estilos computados relevantes.
// - Com --meta (XML salvo do get_metadata da seção), calcula a posição de cada node Figma
//   relativa ao node raiz e imprime Δx Δy Δw Δh. Linhas com |Δ| > 1px saem marcadas com ✗.
//   (Grupos Figma aparecem como <frame> no XML; o script detecta pelo encaixe dos filhos.
//   Nodes rotacionados podem dar Δ falso — confira no design_context.)
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer-core';
import { XMLParser } from 'fast-xml-parser';

process.stdout.setDefaultEncoding?.('utf8');
const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const args = process.argv.slice(2);
const slug = args[0];
if (!slug) { console.error('uso: node tools/measure.mjs <slug> [--meta file] [--path /s/slug] [--height H] [--styles]'); process.exit(1); }
const opt = (name, def) => { const i = args.indexOf(name); return i > -1 ? args[i + 1] : def; };
const metaFile = opt('--meta', fs.existsSync(path.join(ROOT, `_ref/meta-${slug}.xml`)) ? `_ref/meta-${slug}.xml` : null);
const urlPath = opt('--path', `/s/${slug}`);
const height = parseInt(opt('--height', '1000'), 10);
const width = parseInt(opt('--width', '1440'), 10);
const showStyles = args.includes('--styles');

const CHROME = [
  'C:/Program Files/Google/Chrome/Application/chrome.exe',
  'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
].find(p => fs.existsSync(p));

// ---------- Figma metadata → posições absolutas relativas ao root ----------
function figmaBoxes(xmlText) {
  const parser = new XMLParser({ ignoreAttributes: false, attributeNamePrefix: '', preserveOrder: true });
  const tree = parser.parse(xmlText);
  const out = new Map();
  const num = v => parseFloat(v);
  const toNode = (entry) => {
    const tag = Object.keys(entry).find(k => k !== ':@');
    const a = entry[':@'] || {};
    return { tag, id: a.id, name: a.name, x: num(a.x), y: num(a.y), w: num(a.width), h: num(a.height), hidden: a.hidden === 'true', children: (entry[tag] || []).filter(c => Object.keys(c).some(k => k !== ':@' && k !== '#text')).map(toNode) };
  };
  const roots = tree.map(toNode).filter(n => n.id);
  const fits = (n, kids, ox, oy) => kids.every(c => c.x >= ox - 0.5 && c.y >= oy - 0.5 && c.x + c.w <= ox + n.w + 0.5 && c.y + c.h <= oy + n.h + 0.5);
  const walk = (n, absX, absY, originX, originY) => {
    // absX/absY = posição absoluta do node; originX/Y = origem das coords do node (pai)
    out.set(n.id, { name: n.name, tag: n.tag, x: absX, y: absY, w: n.w, h: n.h, hidden: n.hidden });
    const kids = n.children;
    if (!kids.length) return;
    const isGroup = kids.length && fits(n, kids, n.x, n.y) && !fits(n, kids, 0, 0);
    for (const c of kids) {
      if (isGroup) walk(c, originX + c.x, originY + c.y, originX, originY);
      else walk(c, absX + c.x, absY + c.y, absX, absY);
    }
  };
  for (const r of roots) walk(r, 0, 0, 0, 0);
  return out;
}

// ---------- DOM ----------
const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--hide-scrollbars', '--force-device-scale-factor=1'] });
try {
  const page = await browser.newPage();
  // estado final do layout: sem animações de entrada (MOTION=1 para ver com animação)
  if (!process.env.MOTION) await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
  await page.setViewport({ width, height, deviceScaleFactor: 1 });
  const errors = [];
  page.on('pageerror', e => errors.push(String(e)));
  page.on('console', m => { if (m.type() === 'error') errors.push(m.text()); });
  page.on('requestfailed', r => errors.push('FAILED ' + r.url()));
  page.on('response', r => { if (r.status() >= 400) errors.push(`${r.status()} ${r.url()}`); });
  await page.goto('http://localhost:5500' + urlPath, { waitUntil: 'networkidle0', timeout: 60000 });
  await page.evaluate(() => document.fonts.ready);
  await new Promise(r => setTimeout(r, 300));

  const dom = await page.evaluate(() => {
    const els = [...document.querySelectorAll('[data-figma-node]')];
    if (!els.length) return { root: null, items: [] };
    const root = els[0];
    const rb = root.getBoundingClientRect();
    const px = v => v;
    return {
      root: root.dataset.figmaNode,
      scrollW: document.documentElement.scrollWidth,
      items: els.map(el => {
        const b = el.getBoundingClientRect();
        const cs = getComputedStyle(el);
        return {
          id: el.dataset.figmaNode,
          sel: el.tagName.toLowerCase() + (el.className && typeof el.className === 'string' ? '.' + el.className.trim().split(/\s+/)[0] : ''),
          x: +(b.x - rb.x).toFixed(2), y: +(b.y - rb.y).toFixed(2), w: +b.width.toFixed(2), h: +b.height.toFixed(2),
          style: {
            font: `${cs.fontFamily.split(',')[0].replace(/"/g, '')} ${cs.fontWeight} ${cs.fontSize}/${cs.lineHeight}`,
            ls: cs.letterSpacing, color: cs.color, bg: cs.backgroundColor, pad: cs.padding, gap: cs.gap, radius: cs.borderRadius,
          },
        };
      }),
    };
  });

  if (!dom.root) { console.log('Nenhum [data-figma-node] encontrado.'); process.exit(0); }
  console.log(`URL http://localhost:5500${urlPath} | root data-figma-node=${dom.root} | scrollWidth=${dom.scrollW}${dom.scrollW > width ? '  ✗ OVERFLOW HORIZONTAL' : ''} | viewport=${width}`);
  if (errors.length) console.log('ERROS DE CONSOLE/REDE:\n  ' + errors.join('\n  '));

  let figma = null;
  if (metaFile) {
    const xml = fs.readFileSync(path.resolve(ROOT, metaFile), 'utf8');
    const all = figmaBoxes(xml);
    const r = all.get(dom.root);
    if (!r) console.log(`(root ${dom.root} não está no XML ${metaFile})`);
    else figma = { all, r };
  }

  const pad = (s, n) => String(s).padEnd(n);
  const fmt = v => (v >= 0 ? '+' : '') + v.toFixed(1);
  let bad = 0;
  console.log(pad('node', 12) + pad('elemento', 34) + pad('DOM x,y w×h', 32) + (figma ? pad('Figma x,y w×h', 32) + 'Δx Δy Δw Δh' : ''));
  for (const it of dom.items) {
    let line = pad(it.id, 12) + pad(it.sel.slice(0, 33), 34) + pad(`${it.x},${it.y} ${it.w}×${it.h}`, 32);
    if (figma) {
      const f = figma.all.get(it.id);
      if (f) {
        const fx = f.x - figma.r.x, fy = f.y - figma.r.y;
        const d = [it.x - fx, it.y - fy, it.w - f.w, it.h - f.h];
        const worst = Math.max(...d.map(Math.abs));
        if (worst > 1) bad++;
        line += pad(`${+fx.toFixed(2)},${+fy.toFixed(2)} ${+f.w.toFixed(2)}×${+f.h.toFixed(2)}`, 32) + d.map(fmt).join(' ') + (worst > 1 ? '  ✗' : '  ✓');
      } else line += pad('(não está no XML)', 32);
    }
    console.log(line);
    if (showStyles) console.log('            ' + JSON.stringify(it.style));
  }
  if (figma) console.log(`\n${bad} node(s) com |Δ| > 1px de ${dom.items.length} medidos.`);
} finally {
  await browser.close();
}
