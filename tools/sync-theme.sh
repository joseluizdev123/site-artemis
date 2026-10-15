#!/usr/bin/env bash
# Copia assets/ do estático para o tema (fonte da verdade: assets/ da raiz).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
rm -rf "$ROOT/theme/artemis/assets"
cp -r "$ROOT/assets" "$ROOT/theme/artemis/assets"
echo "assets sincronizados -> theme/artemis/assets"
