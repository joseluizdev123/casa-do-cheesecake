"""Build: gera index.html estático e espelha assets/ no tema WordPress.

Uso: python tools/build.py
"""
import shutil
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
import assemble  # noqa: E402

ROOT = Path(__file__).resolve().parent.parent
THEME = ROOT / "theme" / "casadocheesecake"


def main():
    (ROOT / "index.html").write_text(assemble.assemble(), encoding="utf-8")
    print("index.html ok")

    dst = THEME / "assets"
    if dst.exists():
        shutil.rmtree(dst)
    shutil.copytree(ROOT / "assets", dst)
    n = sum(1 for p in dst.rglob("*") if p.is_file())
    print(f"theme assets synced ({n} files)")


if __name__ == "__main__":
    main()
