"""Monta o HTML estático a partir de src/.

src/layout.html            → shell com marcadores <!-- @... -->
src/partials/header.html   → header compartilhado
src/partials/footer.html   → footer compartilhado (opcional)
src/sections/NN-slug.html  → seções do <main>, em ordem numérica
assets/css/sections/NN-slug.css / assets/js/sections/NN-slug.js → injetados se existirem
"""
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "src"


def _read(p: Path) -> str:
    return p.read_text(encoding="utf-8") if p.exists() else ""


REPEAT = re.compile(r"^\s*<!--\s*@repeat\s+(\S+)(.*?)-->\s*$", re.S)


def _section_body(p: Path) -> str:
    """Corpo da seção. Um arquivo com só `<!-- @repeat NN-slug -->` repete aquela
    seção (mesmo conteúdo, um lugar só para editar) com ids e aria-labelledby
    sufixados pelo nome deste arquivo, para não duplicar ids na página.
    Linhas `antigo => novo` dentro do comentário trocam trechos da cópia
    (ex.: as fotos)."""
    body = _read(p)
    m = REPEAT.match(body)
    if not m:
        return body
    suffix = "-" + p.stem.split("-", 1)[0]          # ex. 03b-... → "-03b"
    src = _read(SRC / "sections" / f"{m.group(1)}.html")
    src = re.sub(r'\b(id|aria-labelledby)="([^"]+)"', lambda g: f'{g.group(1)}="{g.group(2)}{suffix}"', src)
    for line in m.group(2).splitlines():
        if "=>" in line:
            old, new = (s.strip() for s in line.split("=>", 1))
            if old not in src:
                raise ValueError(f"{p.name}: trecho não encontrado em {m.group(1)}: {old!r}")
            src = src.replace(old, new)
    return src


def section_files():
    return sorted((SRC / "sections").glob("*.html"))


def assemble(only: str | None = None, base: str | None = None) -> str:
    """only = slug de uma seção (ex. '01-hero') para preview isolado."""
    layout = _read(SRC / "layout.html")
    sections = section_files()
    if only:
        sections = [p for p in sections if p.stem == only]

    css_links, js_tags, bodies = [], [], []
    for p in sections:
        bodies.append(_section_body(p))
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

    html = layout
    html = html.replace("<!-- @section-css -->", "\n".join(css_links))
    html = html.replace("<!-- @section-js -->", "\n".join(js_tags))
    html = html.replace("<!-- @header -->", "" if only else _read(SRC / "partials/header.html"))
    html = html.replace("<!-- @footer -->", "" if only else _read(SRC / "partials/footer.html"))
    html = html.replace("<!-- @sections -->", "\n".join(bodies))
    if base:
        html = html.replace("<head>", f'<head>\n  <base href="{base}">', 1)
    return html


if __name__ == "__main__":
    out = ROOT / "index.html"
    out.write_text(assemble(), encoding="utf-8")
    print(f"wrote {out}")
