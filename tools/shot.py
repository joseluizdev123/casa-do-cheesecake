"""Screenshot headless + diff visual contra o recorte do Figma.

Uso:
  python tools/shot.py <slug> [--height H] [--path /s/<slug>] [--wait MS]

- Tira screenshot de http://localhost:5500/s/<slug> em 1440×H (H = altura do
  recorte _ref/figma-<slug>.png por padrão) com Chrome headless, DPR 1.
- Compara com _ref/figma-<slug>.png e grava em _shots/:
    <slug>.png        implementação
    <slug>-side.png   Figma (esq) | implementação (dir)
    <slug>-diff.png   mapa de diferenças (vermelho = pixel diverge > 48/255)
- Imprime: % de pixels divergentes, diferença média, e faixas horizontais
  (y) com maior divergência — use para achar onde está o desvio.
"""
import argparse
import sys
import os
import subprocess
import tempfile
from pathlib import Path

from PIL import Image, ImageChops

ROOT = Path(__file__).resolve().parent.parent
REF = ROOT / "_ref"
OUT = ROOT / "_shots"
CHROME_CANDIDATES = [
    r"C:\Program Files\Google\Chrome\Application\chrome.exe",
    r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe",
]


def chrome():
    for c in CHROME_CANDIDATES:
        if os.path.exists(c):
            return c
    raise SystemExit("Chrome/Edge não encontrado")


def shoot(url, width, height, out, wait):
    with tempfile.TemporaryDirectory() as profile:
        cmd = [
            chrome(), "--headless=new", "--disable-gpu", "--hide-scrollbars",
            "--force-device-scale-factor=1", f"--window-size={width},{height}",
            f"--user-data-dir={profile}", "--no-first-run", "--no-default-browser-check",
            "--run-all-compositor-stages-before-draw", f"--screenshot={out}", url,
        ]
        # sem --virtual-time-budget: trava o processo com listeners de scroll/rAF
        subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, stdin=subprocess.DEVNULL, timeout=90)


def main():
    sys.stdout.reconfigure(encoding="utf-8")
    ap = argparse.ArgumentParser()
    ap.add_argument("slug")
    ap.add_argument("--height", type=int)
    ap.add_argument("--path")
    ap.add_argument("--wait", type=int, default=4000)
    ap.add_argument("--ref", help="recorte de referência alternativo (caminho)")
    a = ap.parse_args()

    OUT.mkdir(exist_ok=True)
    ref_path = Path(a.ref) if a.ref else REF / f"figma-{a.slug}.png"
    ref = Image.open(ref_path).convert("RGB") if ref_path.exists() else None
    height = a.height or (ref.height if ref else 900)
    url = "http://localhost:5500" + (a.path or f"/s/{a.slug}")
    shot_path = OUT / f"{a.slug}.png"
    shoot(url, 1440, height, str(shot_path), a.wait)
    impl = Image.open(shot_path).convert("RGB")
    print(f"impl {impl.size} <- {url}")
    if not ref:
        print("sem referência Figma para comparar")
        return

    h = min(ref.height, impl.height)
    r, i = ref.crop((0, 0, 1440, h)), impl.crop((0, 0, 1440, h))
    diff = ImageChops.difference(r, i).convert("L")
    px = list(diff.get_flattened_data() if hasattr(diff, "get_flattened_data") else diff.getdata())
    bad = sum(1 for v in px if v > 48)
    print(f"ref {ref.size} | pixels divergentes (>48): {bad / len(px) * 100:.2f}% | diff médio: {sum(px) / len(px):.2f}")

    # faixas de 20px com mais divergência
    bands = []
    for y in range(0, h, 20):
        band = diff.crop((0, y, 1440, min(y + 20, h)))
        data = list(band.get_flattened_data() if hasattr(band, "get_flattened_data") else band.getdata())
        bands.append((sum(1 for v in data if v > 48) / len(data) * 100, y))
    top = sorted(bands, reverse=True)[:8]
    print("faixas mais divergentes (y: %):", ", ".join(f"{y}-{y+20}: {p:.1f}%" for p, y in top if p > 0.5) or "nenhuma > 0.5%")

    mask = diff.point(lambda v: 255 if v > 48 else 0)
    heat = Image.blend(i, Image.new("RGB", i.size, (255, 255, 255)), 0.6)
    heat.paste((230, 0, 0), mask=mask)
    heat.save(OUT / f"{a.slug}-diff.png")

    side = Image.new("RGB", (1440 * 2 + 20, max(ref.height, impl.height)), (40, 40, 40))
    side.paste(ref, (0, 0))
    side.paste(impl, (1460, 0))
    side.save(OUT / f"{a.slug}-side.png")
    print(f"gravado: _shots/{a.slug}-side.png, _shots/{a.slug}-diff.png")


if __name__ == "__main__":
    main()
