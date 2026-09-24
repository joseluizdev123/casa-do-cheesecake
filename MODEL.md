# A Casa do Cheesecake — Modelo de dados WordPress

Tema: `theme/casadocheesecake/` · prefixo de código: `cdc_` · text domain: `casadocheesecake`

## Decisões (Fase 0.0)

| Decisão | Escolha | Por quê |
|---|---|---|
| Campos customizados | **Meta box nativo** (sem ACF) | Zero dependência de plugin; motor genérico em `inc/fields.php` |
| Conteúdo global | **Customizer** (`theme_mod`) | Cliente edita com preview ao vivo |
| Quem opera o admin | Cliente leigo | Rótulos em PT-BR, um meta box por CPT, poucos campos |
| Imagens | Assets do Figma viram **seed** importado para a Mídia | Seeder roda sozinho na ativação do tema + botão em *Ferramentas → Importar conteúdo* |
| Atualizações | **Git Updater** (`GitHub Theme URI` no `style.css`) | Deploy via push no GitHub |
| Fallback | Todo `theme_mod` tem default = copy do Figma | Tema renderiza idêntico ao estático mesmo antes do seed |

## Tabela de modelo

| Conteúdo | Tipo | Slug WP | Campos principais |
|---|---|---|---|
| Cheesecakes (cardápio + carrossel de sabores) | CPT | `cdc_cheesecake` | título, imagem destacada (bolo inteiro), `descricao`, `tag` (ex. "Mais pedido"), `cor_fundo`, `precos` (1 por linha: `preço \| tamanho`), `imagem_fatia` (carrossel Sabores), `link_pedido` |
| Diferenciais ("O que faz um cheesecake ser o de verdade") | CPT | `cdc_diferencial` | título, `texto`, imagem destacada, `tag`, `destaque` (card vermelho) |
| Depoimentos | CPT | `cdc_depoimento` | título (nome), `texto`, `estrelas`, `data`, `fonte` |
| Perguntas frequentes | CPT | `cdc_faq` | título (pergunta), `resposta` |
| Contato / links globais | Customizer | `cdc_contato_*` | WhatsApp, telefone, Instagram, Facebook, iFood, link do cardápio |
| Copy + imagens de cada seção da home | Customizer | `cdc_<secao>_*` | títulos, textos, rótulos de botão, imagens (um painel por seção) |
| Menus do header e do footer | Menus WP | `header-left`, `header-right`, `footer-comprar`, `footer-marca`, `footer-contato`, `footer-social` | fallback = links do Figma |
| Home | Página (única) | `/` (`show_on_front=page`) | renderizada por `front-page.php` |

## Onde cada coisa mora no tema

```
theme/casadocheesecake/
├── style.css                    cabeçalho WP + Git Updater
├── functions.php                setup, enqueue, autoload de inc/sections/*.php
├── index.php / front-page.php / header.php / footer.php
├── inc/
│   ├── helpers.php              cdc_mod(), cdc_image(), cdc_asset(), cdc_meta(), cdc_lines()
│   ├── fields.php               motor de meta box genérico (filtro cdc_post_fields)
│   ├── cpt.php                  registro dos 4 CPTs + campos base
│   ├── customizer.php           registro (filtro cdc_customizer_sections) → painéis/controles
│   ├── menus.php                locais de menu + cdc_menu_links() com fallback
│   ├── seeder.php               importador one-click (filtro cdc_seed_posts)
│   └── sections/NN-slug.php     por seção: customizer + campos extras + seed
├── template-parts/
│   ├── site-header.php / site-footer.php
│   └── sections/NN-slug.php     uma por bloco do estático (mesma ordem)
└── assets/                      espelho de /assets (sincronizado por tools/build.py)
```
