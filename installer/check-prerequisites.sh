#!/usr/bin/env bash

set -euo pipefail

ROOT="${1:-$(cd "$(dirname "$0")/.." && pwd)}"

echo ""
echo "  Verificare cerințe sistem..."
echo ""

missing=0
skip_composer=0
skip_node=0

[[ -f "${ROOT}/vendor/autoload.php" ]] && skip_composer=1
[[ -f "${ROOT}/public/build/manifest.json" ]] && skip_node=1

if command -v php >/dev/null 2>&1; then
    echo "  [OK] PHP detectat ($(php -r 'echo PHP_VERSION;'))"
else
    echo "  [LIPSESTE] PHP / Laravel Herd"
    echo "  Descarcă: https://herd.laravel.com"
    missing=1
fi

if [[ "${skip_composer}" -eq 1 ]]; then
    echo "  [OK] Vendor inclus în pachet — Composer opțional"
elif command -v composer >/dev/null 2>&1; then
    echo "  [OK] Composer detectat"
else
    echo "  [LIPSESTE] Composer"
    echo "  Descarcă: https://getcomposer.org"
    missing=1
fi

if [[ "${skip_node}" -eq 1 ]]; then
    echo "  [OK] Assets compilate — Node.js opțional"
elif command -v node >/dev/null 2>&1; then
    echo "  [OK] Node.js detectat ($(node -v))"
else
    echo "  [LIPSESTE] Node.js"
    echo "  Descarcă: https://nodejs.org"
    missing=1
fi

if command -v herd >/dev/null 2>&1; then
    echo "  [OK] Herd CLI detectat"
else
    echo "  [ATENȚIE] Herd CLI indisponibil — configurează site-ul în aplicația Herd"
fi

if [[ "${missing}" -eq 1 ]]; then
    echo ""
    echo "  Instalează componentele lipsă și rulează din nou instalarea."
    open "https://herd.laravel.com" 2>/dev/null || true
    exit 1
fi

echo ""
echo "  Cerințele sunt îndeplinite. Continuăm..."
exit 0
