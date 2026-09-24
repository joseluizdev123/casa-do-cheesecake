#!/usr/bin/env bash
# Lint de sintaxe PHP 7.4 de todo o tema, via PHP-WASM (WordPress Playground) — não precisa de PHP/Docker.
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WIN_ROOT="$(cygpath -w "$ROOT")"
MSYS_NO_PATHCONV=1 npx -y @wp-playground/cli@latest php --php 7.4 \
  --wordpress-install-mode do-not-attempt-installing --skip-sqlite-setup --verbosity quiet \
  --mount-dir "$WIN_ROOT\theme\casadocheesecake" /theme \
  --mount-dir "$WIN_ROOT\tools" /tools -- /tools/php-lint.php 2>&1 | grep -v "npm warn"
