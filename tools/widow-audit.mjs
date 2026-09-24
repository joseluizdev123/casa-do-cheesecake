// Procura palavras viúvas (última linha com uma única palavra) em textos corridos.
// Uso: node tools/widow-audit.mjs [larguras=1440,1280,1024,768,390,375]
import fs from 'node:fs';
import puppeteer from 'puppeteer-core';
const widths = (process.argv[2] || '1440,1280,1024,768,390,375').split(',').map(Number);
const CHROME = ['C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => fs.existsSync(p));
const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
let total = 0;
for (const w of widths) {
  const p = await b.newPage();
  await p.setViewport({ width: w, height: 900, deviceScaleFactor: 1 });
  await p.goto(`http://localhost:${process.env.PORT || 5500}/`, { waitUntil: 'networkidle0' });
  const found = await p.evaluate(async () => {
    await document.fonts.ready;
    const out = [];
    const els = [...document.querySelectorAll('main p, main li, footer p, main h1, main h2, main h3')];
    for (const el of els) {
      const r = el.getBoundingClientRect(); const cs = getComputedStyle(el);
      if (!r.width || cs.display === 'none' || cs.visibility === 'hidden' || el.closest('[hidden]')) continue;
      if (el.querySelector('p, li, h1, h2, h3, ul, ol')) continue;
      const words = [];
      const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
      while (walker.nextNode()) {
        const n = walker.currentNode; const re = /\S+/g; let m;
        while ((m = re.exec(n.nodeValue))) {
          const rg = document.createRange(); rg.setStart(n, m.index); rg.setEnd(n, m.index + m[0].length);
          const rects = rg.getClientRects(); if (!rects.length) continue;
          words.push({ t: m[0], top: Math.round(rects[rects.length - 1].top) });
        }
      }
      if (words.length < 4) continue;
      const lines = [...new Set(words.map(x => x.top))];
      if (lines.length < 2) continue;
      const last = words.filter(x => x.top === lines[lines.length - 1]);
      if (last.length === 1) out.push(`${el.tagName.toLowerCase()}.${(el.className || '').split(' ')[0]}: "…${words.slice(-4).map(x => x.t).join(' ')}" → última linha só "${last[0].t}"`);
    }
    return out;
  });
  total += found.length;
  console.log(`== ${w}px: ${found.length ? found.length + ' viúva(s)' : 'nenhuma viúva ✓'}`);
  found.forEach(f => console.log('   ' + f));
  await p.close();
}
await b.close();
console.log(total ? `\n${total} ocorrência(s)` : '\nsem viúvas em nenhuma largura');
