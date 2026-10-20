"""Monta o HTML estático a partir de src/.

src/layout.html            → shell com marcadores <!-- @... -->
src/partials/header.html   → header compartilhado
src/partials/footer.html   → footer compartilhado (opcional)
src/sections/NN-slug.html  → seções do <main> da home, em ordem numérica
src/paginas/<pagina>/NN-slug.html → seções das páginas internas (ver PAGES)
assets/css/sections/NN-slug.css / assets/js/sections/NN-slug.js → injetados se existirem
"""
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "src"


def _read(p: Path) -> str:
    return p.read_text(encoding="utf-8") if p.exists() else ""


REPEAT = re.compile(r"^\s*<!--\s*@repeat\s+(\S+)(.*?)-->\s*$", re.S)


def _section_body(p: Path, sufixo_ids: bool = True) -> str:
    """Corpo da seção. Um arquivo com só `<!-- @repeat NN-slug -->` repete aquela
    seção da home (mesmo conteúdo, um lugar só para editar). Na home a cópia
    ganha ids e aria-labelledby sufixados pelo nome deste arquivo, para não
    duplicar ids; nas outras páginas a original não está lá, então os ids ficam
    iguais e o CSS da home preso a eles (ex. #primeira-fatia) vale igual.
    Linhas `antigo => novo` dentro do comentário trocam trechos da cópia
    (ex.: as fotos)."""
    body = _read(p)
    m = REPEAT.match(body)
    if not m:
        return body
    suffix = "-" + p.stem.split("-", 1)[0]          # ex. 03b-... → "-03b"
    src = _read(SRC / "sections" / f"{m.group(1)}.html")
    if sufixo_ids:
        src = re.sub(r'\b(id|aria-labelledby)="([^"]+)"', lambda g: f'{g.group(1)}="{g.group(2)}{suffix}"', src)
    for line in m.group(2).splitlines():
        if "=>" in line:
            old, new = (s.strip() for s in line.split("=>", 1))
            if old not in src:
                raise ValueError(f"{p.name}: trecho não encontrado em {m.group(1)}: {old!r}")
            src = src.replace(old, new)
    return src


# Páginas do site. As internas não têm Figma: são montadas só com as seções
# e estilos da home (css/js = caminhos em assets/css e assets/js; os de
# sections/ são os da home reaproveitados, os de paginas/ só valem na página).
PAGES = {
    "index": {"dir": SRC / "sections", "out": "index.html"},
    "cardapio": {
        "dir": SRC / "paginas" / "cardapio",
        "out": "cardapio.html",
        "title": "Cardápio — A Casa do Cheesecake",
        "description": "Cardápio completo d'A Casa do Cheesecake: cheesecakes inteiros e em fatia, especiais, bolos, brownie, velas e cartão, com entrega em São Paulo.",
        "css": ["sections/02-primeira-fatia", "sections/03-cardapio", "paginas/cardapio"],
        "js": ["sections/02-primeira-fatia", "sections/03-cardapio"],
    },
    "sobre": {
        "dir": SRC / "paginas" / "sobre",
        "out": "sobre.html",
        "title": "Sobre nós — A Casa do Cheesecake",
        "description": "Desde 2001 fazendo cheesecake e desde 2004 em São Paulo: a história d'A Casa do Cheesecake, pioneira no autêntico NY Cheesecake no Brasil.",
        "css": ["sections/06-sobre", "sections/08-entrega", "sections/09-restaurantes", "paginas/sobre"],
        "js": ["sections/06-sobre", "sections/08-entrega", "sections/09-restaurantes"],
    },
    "contato": {
        "dir": SRC / "paginas" / "contato",
        "out": "contato.html",
        "title": "Contato — A Casa do Cheesecake",
        "description": "Fale com A Casa do Cheesecake: WhatsApp, pedidos delivery, pedidos para restaurantes, e-mail, Instagram e Facebook.",
        "css": ["sections/06-sobre", "sections/03-cardapio", "sections/10-perguntas", "paginas/contato"],
        "js": ["sections/06-sobre", "sections/10-perguntas", "paginas/contato"],
    },
    "blog": {
        "dir": SRC / "paginas" / "blog",
        "out": "blog.html",
        "title": "Blog — A Casa do Cheesecake",
        "description": "Dicas para escolher, pedir, presentear e conservar o seu cheesecake, além de receitas e curiosidades sobre o autêntico NY Cheesecake.",
        "css": ["sections/05-diferenciais", "sections/06-sobre", "paginas/blog"],
        "js": ["sections/05-diferenciais", "sections/06-sobre"],
    },
}


def section_files(page: str = "index"):
    return sorted(PAGES[page]["dir"].glob("*.html"))


def _local_anchors(html: str, page_html: str, logo_home: bool) -> str:
    """Página interna: âncoras do header/footer que não existem nela apontam
    para a seção na home (index.html#id). O logo do header vai para a home."""
    ids = set(re.findall(r'\bid="([^"]+)"', page_html)) | {"topo", "conteudo"}  # do layout
    if logo_home:
        html = html.replace('class="site-logo" data-figma-node="7073:234" href="#topo"',
                            'class="site-logo" data-figma-node="7073:234" href="index.html"')
    return re.sub(r'href="#([^"]+)"',
                  lambda m: m.group(0) if m.group(1) in ids else f'href="index.html#{m.group(1)}"', html)


def assemble(only: str | None = None, base: str | None = None, page: str = "index") -> str:
    """only = slug de uma seção (ex. '01-hero') para preview isolado."""
    cfg = PAGES[page]
    layout = _read(SRC / "layout.html")
    sections = section_files(page)
    if only:
        sections = [p for p in sections if p.stem == only]

    css_links, js_tags, bodies = [], [], []
    for name in cfg.get("css", []):
        css_links.append(f'  <link rel="stylesheet" href="assets/css/{name}.css">')
    for name in cfg.get("js", []):
        js_tags.append(f'  <script src="assets/js/{name}.js" defer></script>')
    for p in sections:
        bodies.append(_section_body(p, sufixo_ids=page == "index"))
        if (ROOT / "assets/css/sections" / f"{p.stem}.css").exists():
            css_links.append(f'  <link rel="stylesheet" href="assets/css/sections/{p.stem}.css">')
        if (ROOT / "assets/js/sections" / f"{p.stem}.js").exists():
            js_tags.append(f'  <script src="assets/js/sections/{p.stem}.js" defer></script>')

    footer_css = ROOT / "assets/css/sections/footer.css"
    if footer_css.exists() and not only:
        css_links.append('  <link rel="stylesheet" href="assets/css/sections/footer.css">')
    footer_js = ROOT / "assets/js/sections/footer.js"
    if footer_js.exists() and not only:
        js_tags.append('  <script src="assets/js/sections/footer.js" defer></script>')

    header = "" if only else _read(SRC / "partials/header.html")
    footer = "" if only else _read(SRC / "partials/footer.html")
    main = "\n".join(bodies)
    if page != "index":
        page_html = main + footer
        header = _local_anchors(header, page_html, logo_home=True)
        # o item do menu que leva a esta página fica marcado (mesmo estilo do scrollspy da home)
        header = re.sub(rf'class="(btn-link|mobile-nav__link)" href="{re.escape(cfg["out"])}"',
                        lambda m: f'class="{m.group(1)} is-active" aria-current="page" href="{cfg["out"]}"', header)
        footer = _local_anchors(footer, page_html, logo_home=False)

    html = layout
    if "title" in cfg:
        html = re.sub(r"<title>.*?</title>", lambda m: f"<title>{cfg['title']}</title>", html, count=1)
        html = re.sub(r'(<meta name="description" content=")[^"]*', lambda m: m.group(1) + cfg["description"], html, count=1)
    html = html.replace("<!-- @section-css -->", "\n".join(css_links))
    html = html.replace("<!-- @section-js -->", "\n".join(js_tags))
    html = html.replace("<!-- @header -->", header)
    html = html.replace("<!-- @footer -->", footer)
    html = html.replace("<!-- @sections -->", main)
    if base:
        html = html.replace("<head>", f'<head>\n  <base href="{base}">', 1)
    return html


if __name__ == "__main__":
    for name, cfg in PAGES.items():
        out = ROOT / cfg["out"]
        out.write_text(assemble(page=name), encoding="utf-8")
        print(f"wrote {out}")
