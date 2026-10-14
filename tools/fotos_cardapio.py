"""Converte os recortes do cliente (fotos_recortadas/sem_fundo/*.png) nas
imagens da página Cardápio: assets/images/cardapio-<produto>[-fatia].webp.

Nomes na pasta: "<produto> - inteiro.png", "<produto> - fatia.png" ou só
"<produto>.png"; as duas velinhas viram uma imagem só (cardapio-velas.webp).
Só converte o que mudou desde a última vez. Depois rode tools/gen_cardapio.py.

Uso: python tools/fotos_cardapio.py
"""
import re
import unicodedata
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "fotos_recortadas" / "sem_fundo"
OUT = ROOT / "assets" / "images"
MAX_W = 800      # ~2× a maior largura da foto no card (tablet)
APELIDOS = {"goiabada": "goiaba"}   # nome do arquivo na pasta → produto no site


def slug(s):
    s = unicodedata.normalize("NFKD", s).encode("ascii", "ignore").decode()
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def load(p):
    im = Image.open(p).convert("RGBA")
    a = im.getchannel("A").point(lambda v: 0 if v < 16 else v)  # resíduo quase invisível do fundo
    im.putalpha(a)
    return im.crop(a.point(lambda v: 255 if v else 0).getbbox())


def save(im, out):
    if im.width > MAX_W:
        im = im.resize((MAX_W, round(im.height * MAX_W / im.width)), Image.LANCZOS)
    im.save(out, quality=85, method=4)   # method 6 leva minutos por imagem
    print(out.name, im.size)


def novo(out, *srcs):
    return not out.exists() or out.stat().st_mtime < max(s.stat().st_mtime for s in srcs)


def main():
    velas = []
    for p in sorted(SRC.glob("*.png")):
        if p.stem.startswith("velinha"):
            velas.append(p)
            continue
        nome, _, tipo = p.stem.partition(" - ")
        nome = APELIDOS.get(slug(nome), slug(nome))
        out = OUT / f"cardapio-{nome}{'-fatia' if tipo.strip() == 'fatia' else ''}.webp"
        if novo(out, p):
            save(load(p), out)

    # velas: as duas lado a lado, mesma altura, apoiadas na base
    out = OUT / "cardapio-velas.webp"
    if len(velas) == 2 and novo(out, *velas):
        az, ro = (load(p) for p in velas)
        h = max(az.height, ro.height)
        az = az.resize((round(az.width * h / az.height), h), Image.LANCZOS)
        ro = ro.resize((round(ro.width * h / ro.height), h), Image.LANCZOS)
        canvas = Image.new("RGBA", (az.width * 2 + ro.width, h), (0, 0, 0, 0))
        canvas.alpha_composite(az, (0, 0))
        canvas.alpha_composite(ro, (az.width * 2, 0))
        save(canvas, out)


if __name__ == "__main__":
    main()
