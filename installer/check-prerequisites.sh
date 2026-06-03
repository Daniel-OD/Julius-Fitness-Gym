#!/usr/bin/env bash
# Verifică PHP (Herd), Composer și Node.js înainte de instalare.

set -euo pipefail

echo ""
echo "  Verificare cerințe sistem..."
echo ""

missing=0

if command -v php >/dev/null 2>&1; then
    echo "  [OK] PHP detectat ($(php -r 'echo PHP_VERSION;'))"
else
    echo "  [LIPSESTE] PHP / Laravel Herd"
    echo "  Instalează de la: https://herd.laravel.com"
    missing=1
fi

if command -v composer >/dev/null 2>&1; then
    echo "  [OK] Composer detectat"
else
    echo "  [LIPSESTE] Composer"
    echo "  Instalează de la: https://getcomposer.org"
    missing=1
fi

if command -v node >/dev/null 2>&1; then
    echo "  [OK] Node.js detectat ($(node -v))"
else
    echo "  [LIPSESTE] Node.js"
    echo "  Instalează de la: https://nodejs.org"
    missing=1
fi

if [[ "${missing}" -eq 1 ]]; then
    echo ""
    echo "  Instalează programele lipsă de mai sus,"
    echo "  repornește terminalul și rulează din nou instalarea."
    exit 1
fi

echo ""
echo "  Toate cerințele sunt instalate. Continuăm..."
exit 0
