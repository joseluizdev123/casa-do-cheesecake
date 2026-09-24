"""Remove assets duplicados (mesmo conteúdo binário) e reescreve as referências.

Mantém o primeiro nome em ordem alfabética de cada grupo idêntico e troca as
referências em src/, assets/css/, assets/js/ e theme/casadocheesecake/ (PHP).
Uso: python tools/dedupe_assets.py [--dry]
"""
import hashlib
import sys
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
IMAGES = ROOT / "assets" / "images"
TEXT_DIRS = [ROOT / "src", ROOT / "assets" / "css", ROOT / "assets" / "js", ROOT / "theme" / "casadocheesecake" / "template-parts", ROOT / "theme" / "casadocheesecake" / "inc"]
TEXT_EXT = {".html", ".css", ".js", ".php"}


def main(dry=False):
    groups = defaultdict(list)
    for p in sorted(IMAGES.iterdir()):
        if p.is_file():
            groups[hashlib.sha256(p.read_bytes()).hexdigest()].append(p)
    renames = {}
    for files in groups.values():
        if len(files) > 1:
            keep, *dups = sorted(files, key=lambda p: p.name)
            for d in dups:
                renames[d.name] = keep.name
    if not renames:
        print("nenhum duplicado")
        return
    texts = [p for d in TEXT_DIRS if d.exists() for p in d.rglob("*") if p.suffix in TEXT_EXT]
    for old, new in sorted(renames.items()):
        print(f"{old} -> {new}")
    if dry:
        return
    for p in texts:
        s = p.read_text(encoding="utf-8")
        t = s
        for old, new in renames.items():
            t = t.replace(f"images/{old}", f"images/{new}")
        if t != s:
            p.write_text(t, encoding="utf-8")
            print(f"  refs atualizadas: {p.relative_to(ROOT)}")
    for old in renames:
        (IMAGES / old).unlink()


if __name__ == "__main__":
    main(dry="--dry" in sys.argv)
