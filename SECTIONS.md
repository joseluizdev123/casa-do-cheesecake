# Mapa de seções — Figma → código (Fase 0)

Arquivo Figma: `wKx5nynbRuELPfTDjiAWDg` · página `◆ Design` (7057:67) · frame raiz **Desktop 7057:68** (1440 × 9057)

| # | Seção (nome Figma) | Node ID | y no frame | Altura | Slug no código | Notas |
|---|---|---|---|---|---|---|
| 0 | Header default / Header scroll | 7073:255 (instância) · 7073:253 / 7073:254 (símbolos) | 0 | 124 / 70 | `partials/header` | fixo após scroll, logo reduz |
| 1 | Hero | 7057:81 | 124 | 959 | `01-hero` | full-bleed, foto + PNG recortado sobreposto |
| 2 | Call to action | 7057:164 | 1083 | 441 | `02-primeira-fatia` | banner amarelo, fatias sobrepostas |
| 3 | Cardápio | 7057:175 | 1524 | 1150.5 | `03-cardapio` | grid 4 cards de cheesecake (CPT) |
| 4 | Sabores (instância) | 7057:329 · variantes 7057:519 / 566 / 613 / 660 | 2674.5 | 802.15 | `04-sabores` | fundo vermelho, carrossel por sabor |
| 5 | Cardápio (2) — diferenciais | 7057:330 | 3476.65 | 868 | `05-diferenciais` | 3 cards texto + foto (CPT) |
| 6 | Sobre | 7057:349 | 4344.65 | 728 | `06-sobre` | 2 colunas, foto + especialista |
| 7 | Depoimentos | 7057:361 | 5072.65 | 665 | `07-depoimentos` | carrossel 3 cards (CPT) |
| 8 | Entrega | 7057:490 | 5737.65 | 678 | `08-entrega` | 4 colunas com ícones |
| 9 | Delivery | 7057:508 | 6415.65 | 419 | `09-restaurantes` | banner amarelo B2B com moto |
| 10 | Perguntas | 7057:763 · aberto: 7057:1039 | 6834.65 | 881 | `10-perguntas` | acordeão (CPT); 7057:1039 tem todas as respostas |
| 11 | Sabores (CTA final) | 7057:955 | 7715.65 | 923.8 | `11-cta-final` | fundo vermelho, "Peça o seu cheesecake hoje" |
| 12 | Footer | 7073:66 | 8639.45 | 418 | `partials/footer` | fundo preto, logo central, pattern de linhas |

Fora do fluxo (ignorar no código): `status` 7057:517 (anotação de status), seção `Componentes` 7073:330 (biblioteca: botões + headers).

## Tokens estruturais (Fase 0.5)

| Token | Valor | Origem |
|---|---|---|
| `--frame-w` | 1440px | Desktop 7057:68 |
| `--content-w` | 1216px | containers internos (7057:165, 7057:176, 7057:179…) |
| `--section-px` | `clamp(24px, 7.78vw, 112px)` | padding lateral 112 @ 1440 |
| `--section-py` | 80px | padding vertical padrão |

## Responsivo

O Figma só tem frame **Desktop 1440**. Nenhum frame mobile/tablet encontrado.
