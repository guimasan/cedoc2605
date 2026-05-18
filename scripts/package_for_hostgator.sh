#!/usr/bin/env bash
# Empacota o tema e os protótipos em um zip pronto para upload
set -e
ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT_DIR/cedoc-hostgator-package.zip"
echo "Empacotando para: $OUT"
cd "$ROOT_DIR"
zip -r "$OUT" tainacan-theme-master site-demo README-HOSTGATOR.md -x "*/node_modules/*" -x "*/vendor/twbs/bootstrap/site/*" -x "*/.git/*" -x "*/.DS_Store"
echo "Pronto: $OUT"
