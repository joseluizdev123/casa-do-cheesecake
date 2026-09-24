# Motion — A Casa do Cheesecake

Pedido do cliente: "o site está meio estático — animações de entrada, mais interativo, dinâmico, em toda a página".

## Regras inegociáveis
1. **Estado final = layout atual.** Desktop continua pixel-perfect com o Figma; tablet/mobile aprovados não mudam. Animação só em `opacity`, `translate`, `scale`, `rotate`, `clip-path` (propriedades individuais — **nunca** `transform`, que as seções usam para layout).
2. **Sem markup novo.** Tudo via `cdcMotion` (JS) sobre os elementos/classes que já existem → o tema WordPress herda sem mudar PHP.
3. **Acessibilidade:** `prefers-reduced-motion: reduce` = tudo estático e visível (o motor já garante; keyframes próprios de seção ficam dentro de `@media (prefers-reduced-motion: no-preference)`). Nada pisca mais de 3×/s. Movimento contínuo só decorativo e sutil.
4. **Performance:** nada de animar `width/height/top/left/margin`; sem CLS (meta < 0.01); `will-change` só se medir ganho.
5. **Nada escondido para sempre:** conteúdo dentro de carrossel/acordeão/aba fechada não recebe entrada (ele já aparece por interação).

## Motor (assets/js/motion.js + assets/css/motion.css) — já pronto, não edite
```js
cdcMotion.reveal(target, { variant, delay, duration, distance, scale, stagger, trigger, threshold, onReveal })
//   variant: 'up' (padrão) | 'down' | 'left' | 'right' | 'fade' | 'scale' | 'zoom' | 'pop' | 'mask'
cdcMotion.counter(el, { duration: 1600, delay, trigger })      // conta de 0 até o número do texto
cdcMotion.parallax(el, { speed: .12, max: 40 })               // usa `translate`: não use com reveal up/down/left/right NO MESMO elemento (anime o pai ou o filho)
cdcMotion.ambient(el, 'float' | 'spin', { '--float-amp': '8px', '--float-dur': '6s', '--spin-dur': '24s' })
cdcMotion.fall(el, { section, measure, rotate: -18, endAt: .12 })  // cai de cima conforme a rolagem (seção com overflow: clip)
cdcMotion.onEnter(el, fn, { threshold, once })
cdcMotion.reduced                                              // true = não anime nada extra
```
Os scripts de seção rodam depois do motor (defer, em ordem). Use sempre `if (!window.cdcMotion) return;`.

## Linguagem (para a página ser coerente)
| Token | Valor |
|---|---|
| Easing entradas | `--motion-ease` expo-out `cubic-bezier(.16,1,.3,1)` |
| Duração | pequenos 450ms · padrão 800ms · imagens grandes/hero 1000–1400ms |
| Distância | 32px desktop · 20px mobile (automático via `--motion-distance`) |
| Stagger | 80–120ms entre itens irmãos · no máximo ~6 itens em cascata (depois disso todos juntos) |
| Duração total de uma seção | ≤ 1.4s do primeiro ao último elemento |

Padrões por tipo de elemento:
- **Títulos Anton (h1/h2):** `mask` (sobe de dentro da máscara), 900–1000ms.
- **Parágrafos, listas, botões:** `up`, 700–800ms, depois do título (+120–200ms).
- **Cards em grade:** `up` com stagger 90–110ms, disparados pelo 1º card (lista inteira).
- **Fotos dentro de moldura com overflow hidden:** `zoom` (1.1→1) 1200ms na `<img>` + `fade` na moldura.
- **Recortes de fatia / produtos que vazam:** `pop` ou `up` com distância maior (60–80px) e depois `ambient float` sutil (amp 6–10px, 5–7s) — float no wrapper, entrada no filho (ou vice-versa) para não brigar pela mesma propriedade.
- **Selos circulares com texto em volta:** `pop` na entrada + `ambient spin` lento (20–30s) **só no anel de texto** se ele for elemento separado do ícone; se for uma imagem única, só `pop`.
- **Números (+5 milhões, +355, +354):** `counter`.
- **Ícones em círculo:** `pop` com stagger.
- **Hero (acima da dobra):** coreografia no carregamento (título 0ms → foto 150ms → recorte 350ms → selo 650ms → badges 750ms+stagger → botões/texto 900ms). Total ≤ 1.6s.

Interações extras permitidas (mesmas em todas as larguras, só com `@media (hover: hover)` para hover):
- zoom suave (scale 1.04–1.06, 600ms) na foto de cards ao passar o cursor;
- transições de troca (abas de sabores: crossfade/escala da fatia + transição de cor de fundo; carrosséis: já rolam suave);
- NÃO mude os hovers de botões/links (assets/css/interactions.css).

## Validação (obrigatória)
- `node tools/motion-audit.mjs <slug> --widths 1440,390 --frames` → CLS < 0.01, nada "nunca revelado", nada acima da dobra escondido após 2.5s, estado final × estático ≤ 0.01%, zero erros. **Abra os quadros** `_shots/motion-<slug>-*` e julgue como diretor de arte (ritmo, elegância, nada "pulando", nada cortado durante a animação).
- Regressão de layout (ferramentas já usam reduced-motion = estado final): `python tools/shot.py <slug>` (divergência não sobe) · `node tools/responsive.mjs <slug> --widths 1440,1024,768,390,320` (sem overflow) · `node tools/hover-audit.mjs 390 --only-fail` e `1440` (nada novo quebrado).
- Página inteira: `node tools/motion-audit.mjs page --widths 1440,390`.

## Pedidos específicos do cliente (implementados)
- **Fatia caindo com a rolagem** — "Peça o seu cheesecake hoje" (11) e a fatia de cada sabor em "Yes, we have cheesecake" (04): começa acima da borda da seção e desce proporcional à rolagem até pousar na composição do Figma quando o topo da seção chega a 12% da tela (`cdcMotion.fall`). Reversível.
- **Selos girando para a direita** — "5 milhões de fatias · Yes, we have it" (06) e o selo do CTA final (11): o anel de texto gira sem parar (22s/volta, horário); o ícone do centro fica parado (cópia só do ícone em `*-selo-icone.svg` por cima, centro do anel mascarado).
- **Menu acompanha a rolagem** (main.js): o link da seção na tela fica ativo no header e no menu mobile.
