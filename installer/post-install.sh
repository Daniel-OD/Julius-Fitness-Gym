#!/usr/bin/env bash
# Configurare completă Julius Fitness Gym (după copierea fișierelor)

set -euo pipefail

APP_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "${APP_ROOT}"

echo ""
echo "  Configurare Julius Fitness Gym..."
echo ""

bash "${APP_ROOT}/installer/check-prerequisites.sh" "${APP_ROOT}"

if [[ ! -f database/database.sqlite ]]; then
    echo "  Creare bază de date SQLite..."
    mkdir -p database
    : > database/database.sqlite
fi

if [[ ! -f vendor/autoload.php ]]; then
    echo "  Composer install..."
    composer install --no-interaction --prefer-dist
else
    echo "  [OK] Dependențe PHP deja incluse"
fi

if [[ ! -f public/build/manifest.json ]]; then
    echo "  NPM install și build..."
    npm install --ignore-scripts
    npm run build
else
    echo "  [OK] Assets frontend deja compilate"
fi

if [[ ! -f .env ]]; then
    echo "  Copiere .env.example -> .env"
    cp .env.example .env
fi

echo "  Generare cheie aplicație..."
php artisan key:generate --force

echo "  Migrări bază de date..."
php artisan migrate --force

echo "  Legătură storage..."
php artisan storage:link 2>/dev/null || true

if command -v herd >/dev/null 2>&1; then
    echo "  Configurare site în Laravel Herd..."
    herd link julius-fitness-gym 2>/dev/null || herd link 2>/dev/null || true
    herd init --no-interaction 2>/dev/null || true
else
    echo "  [INFO] Herd CLI indisponibil — asigură-te că folderul e în ~/Herd"
fi

echo "  Creare utilizator admin..."
php artisan app:install --no-interaction

echo ""
echo "  ========================================"
echo "   Instalare finalizată cu succes!"
echo "  ========================================"
echo "   Site:  http://julius-fitness-gym.test"
echo "   Admin: http://julius-fitness-gym.test/admin"
echo ""

if [[ -f storage/app/install-credentials.txt ]]; then
    echo "  Credențiale:"
    cat storage/app/install-credentials.txt
fi

echo ""
