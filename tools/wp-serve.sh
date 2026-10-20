#!/usr/bin/env bash
# Sobe um WordPress local (Playground/PHP-WASM) em http://127.0.0.1:9400 (ou PORT=…) com o tema montado e o seeder executado.
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WIN_ROOT="$(cygpath -w "$ROOT")"
MSYS_NO_PATHCONV=1 npx -y @wp-playground/cli@3.1.59 server --port "${PORT:-9400}" --php 8.3 \
  --mount-dir "$WIN_ROOT\theme\casadocheesecake" /wordpress/wp-content/themes/casadocheesecake \
  --mount-dir "$WIN_ROOT\tools" /tools \
  --blueprint "$WIN_ROOT\tools\wp-blueprint.json" --login
