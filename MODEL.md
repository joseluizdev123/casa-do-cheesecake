# A Casa do Cheesecake — Modelo de dados WordPress

Tema: `theme/casadocheesecake/` · prefixo de código: `cdc_` · text domain: `casadocheesecake`

## Decisões (Fase 0.0)

| Decisão | Escolha | Por quê |
|---|---|---|
| Campos customizados | **Meta box nativo** (sem ACF) | Zero dependência de plugin; motor genérico em `inc/fields.php` |
| Páginas internas | **Post types para listas + option pages nativas para textos/fotos** | Cliente cadastra produtos e canais sem mexer em código; option pages = mesmo motor de campos, sem plugin (`inc/options.php`) |
| Conteúdo global | **Customizer** (`theme_mod`) | Cliente edita com preview ao vivo |
| Quem opera o admin | Cliente leigo | Rótulos em PT-BR, um meta box por CPT, poucos campos |
| Imagens | Assets do Figma viram **seed** importado para a Mídia | Seeder roda sozinho na ativação do tema + botão em *Ferramentas → Importar conteúdo* |
| Atualizações | **Git Updater** (`GitHub Theme URI` no `style.css`) | Deploy via push no GitHub |
| Fallback | Todo `theme_mod` tem default = copy do Figma | Tema renderiza idêntico ao estático mesmo antes do seed |

## Tabela de modelo

| Conteúdo | Tipo | Slug WP | Campos principais |
|---|---|---|---|
| Produtos (página Produtos + cards e abas de sabores da home) | CPT "Produtos" | `cdc_cheesecake` | título, imagem destacada (inteiro), `descricao`, `tag` (ex. "Mais pedido"), `cor_fundo`, `precos` (1 por linha: `preço \| tamanho \| link`; tamanho começando com "Fatia" = linha da seção Fatias), `imagem_fatia` (seção Fatias + abas Sabores), `home` (mostrar na home), `link_pedido` |
| Categorias do cardápio (seções da página Produtos) | Taxonomia | `cdc_categoria` | nome (título da seção), descrição (texto), `cdc_ordem` (term meta). A 1ª categoria dá o título e o texto do topo da página |
| Canais de contato (lista da página Contato) | CPT | `cdc_canal` | título (canal), `contato`, `botao`, `link` |
| Diferenciais ("O que faz um cheesecake ser o de verdade") | CPT | `cdc_diferencial` | título, `texto`, imagem destacada, `tag`, `destaque` (card vermelho) |
| Depoimentos | CPT | `cdc_depoimento` | título (nome), `texto`, `estrelas`, `data`, `fonte` |
| Perguntas frequentes | CPT | `cdc_faq` | título (pergunta), `resposta` |
| Contato / links globais | Customizer | `cdc_contato_*` | WhatsApp, telefone, Instagram, Facebook, iFood, link do cardápio |
| Copy + imagens de cada seção da home | Customizer | `cdc_<secao>_*` | títulos, textos, rótulos de botão, imagens (um painel por seção) |
| Menus do header e do footer | Menus WP | `header-left`, `header-right`, `footer-comprar`, `footer-marca`, `footer-contato`, `footer-social` | fallback = links do Figma |
| Textos e fotos das páginas internas | Option pages "Páginas do site" | `cdc_opt_produtos`, `cdc_opt_sobre`, `cdc_opt_contato`, `cdc_opt_blog` | banner e seção Fatias (Produtos); blocos de texto e fotos (Sobre nós); título, texto e carrossel de fotos (Contato); título, texto, categorias exibidas e rótulos (Blog) |
| Home | Página (única) | `/` (`show_on_front=page`) | renderizada por `front-page.php` |
| Produtos, Sobre nós, Contato | Páginas | `/produtos/`, `/sobre-nos/`, `/contato/` | template de página `templates/<chave>.php`, criadas pelo importador |
| Blog | Página de posts + posts nativos | `/blog/` (`page_for_posts`) | `home.php` (destaque + 1 seção por categoria), `category.php` (todos os artigos), `single.php` (artigo) |

## Onde cada coisa mora no tema

```
theme/casadocheesecake/
├── style.css                    cabeçalho WP + Git Updater
├── functions.php                setup, enqueue, autoload de inc/sections/*.php
├── index.php / front-page.php / header.php / footer.php
├── inc/
│   ├── helpers.php              cdc_mod(), cdc_image(), cdc_asset(), cdc_meta(), cdc_lines(),
│   │                            cdc_page_url(), cdc_categorias(), cdc_home_products(), cdc_paragraphs()
│   ├── fields.php               motor de campos genérico (meta box + option pages; tipos image/gallery)
│   ├── options.php              option pages nativas "Páginas do site" (filtro cdc_option_pages,
│   │                            leitura cdc_opt() / cdc_opt_image() / cdc_opt_gallery())
│   ├── cpt.php                  CPTs + taxonomia cdc_categoria + campos base
│   ├── customizer.php           registro (filtro cdc_customizer_sections) → painéis/controles
│   ├── menus.php                locais de menu + cdc_menu_links() com fallback
│   ├── seeder.php               importador one-click (filtros cdc_seed_posts, cdc_seed_terms,
│   │                            cdc_seed_pages); roda de novo quando CDC_VERSION muda
│   ├── sections/NN-slug.php     por seção da home: customizer + campos extras + seed
│   └── pages/NN-slug.php        por página interna: option page + seed + funções do template
├── templates/<chave>.php        templates de página (Produtos, Sobre nós, Contato)
├── home.php / category.php / single.php   blog
├── template-parts/
│   ├── site-header.php / site-footer.php
│   └── sections/NN-slug.php     uma por bloco do estático (mesma ordem)
└── assets/                      espelho de /assets (sincronizado por tools/build.py)
```
