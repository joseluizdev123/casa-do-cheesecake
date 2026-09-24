"""Servidor de desenvolvimento sem cache.

GET /                → página completa montada on-the-fly a partir de src/
GET /s/<slug>        → preview isolado de uma seção (ex. /s/01-hero), sem header/footer
GET /s/footer        → preview isolado do footer
qualquer outro path  → arquivo estático

Todas as respostas saem com Cache-Control: no-store (evita screenshot stale).
Uso: python tools/server.py [porta]   (ou variável de ambiente PORT)
"""
import os
import sys
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import urlparse

sys.path.insert(0, str(Path(__file__).resolve().parent))
import assemble  # noqa: E402

ROOT = Path(__file__).resolve().parent.parent


class Handler(SimpleHTTPRequestHandler):
    def __init__(self, *a, **kw):
        super().__init__(*a, directory=str(ROOT), **kw)

    def end_headers(self):
        self.send_header("Cache-Control", "no-store, max-age=0")
        super().end_headers()

    def _html(self, body: str):
        data = body.encode("utf-8")
        self.send_response(200)
        self.send_header("Content-Type", "text/html; charset=utf-8")
        self.send_header("Content-Length", str(len(data)))
        self.end_headers()
        self.wfile.write(data)

    def do_GET(self):
        path = urlparse(self.path).path
        if path in ("/", "/index.html"):
            return self._html(assemble.assemble())
        if path.startswith("/s/"):
            slug = path[3:].strip("/")
            if slug == "footer":
                html = assemble.assemble(only="__none__", base="/")
                html = html.replace("</main>", "</main>\n" + (ROOT / "src/partials/footer.html").read_text(encoding="utf-8"))
                css = '  <link rel="stylesheet" href="assets/css/sections/footer.css">\n</head>'
                html = html.replace("</head>", css, 1)
                return self._html(html)
            if slug == "header":
                html = assemble.assemble(only="__none__", base="/")
                html = html.replace("<main", (ROOT / "src/partials/header.html").read_text(encoding="utf-8") + "\n<main", 1)
                return self._html(html)
            return self._html(assemble.assemble(only=slug, base="/"))
        return super().do_GET()

    def log_message(self, fmt, *args):
        sys.stderr.write("%s\n" % (fmt % args))


if __name__ == "__main__":
    port = int(os.environ.get("PORT") or (sys.argv[1] if len(sys.argv) > 1 else 5500))
    print(f"Serving {ROOT} on http://localhost:{port}")
    ThreadingHTTPServer(("127.0.0.1", port), Handler).serve_forever()
