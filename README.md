# A Casa do Cheesecake — Landing page

Implementação pixel-perfect do Figma *A Casa do Cheesecake — Landing Page* (frame Desktop 1440), com saída dupla:

- **Site estático** — `index.html` + `assets/` (publicado no GitHub Pages)
- **Tema WordPress** — `theme/casadocheesecake/` (CPTs, Customizer por seção, importador de conteúdo em 1 clique)

## Estrutura

| Caminho | O que é |
|---|---|
| `src/layout.html`, `src/partials/`, `src/sections/` | Fonte do HTML (uma seção por arquivo, em ordem) |
| `assets/css/` | `tokens.css` (tokens do Figma), `base.css`, `components.css`, `sections/*.css` |
| `assets/js/` | `main.js` (header) + `sections/*.js` (sabores, depoimentos, restaurantes, FAQ) |
| `index.html` | Página montada — gerada por `tools/build.py`, não edite à mão |
| `theme/casadocheesecake/` | Tema WordPress (ver `MODEL.md`) |
| `MODEL.md` / `SECTIONS.md` | Modelo de dados WP e mapa de seções Figma → código |

## Desenvolvimento

```bash
python tools/server.py            # http://localhost:5500 — monta src/ on-the-fly, sem cache
python tools/build.py             # gera index.html e sincroniza assets/ → theme/casadocheesecake/assets/
bash tools/php-lint.sh            # lint PHP 7.4 do tema (WordPress Playground, sem Docker)
bash tools/wp-serve.sh            # WordPress local em http://127.0.0.1:9400 com o tema + seed
```

Ferramentas de validação (precisam de `npm install` e das referências locais em `_ref/`):
`node tools/measure.mjs <seção>` (diff numérico DOM × Figma) e `python tools/shot.py <seção>` (diff visual).
