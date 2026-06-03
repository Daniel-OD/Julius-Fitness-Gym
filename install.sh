#!/usr/bin/env bash
# Instalare Julius Fitness Gym (macOS / Linux cu Herd)

set -euo pipefail

cd "$(dirname "$0")"

echo ""
echo "  Instalare Julius Fitness Gym..."
echo ""

bash "$(dirname "$0")/installer/check-prerequisites.sh"

if [[ ! -f database/database.sqlite ]]; then
    echo "  Creare bază de date SQLite..."
    mkdir -p database
    : > database/database.sqlite
fi

echo "  Composer install..."
composer install --no-interaction --prefer-dist

if [[ ! -f .env ]]; then
    echo "  Copiere .env.example -> .env"
    cp .env.example .env
fi

echo "  Generare cheie aplicație..."
php artisan key:generate --force

echo "  Migrări bază de date..."
php artisan migrate --force

echo "  NPM install..."
npm install --ignore-scripts

echo "  Build assets..."
npm run build

echo ""
echo "  Instalare finalizată cu succes."
echo "  Deschide: http://julius-fitness-gym.test"
echo "  (Asigură-te că folderul este legat în Laravel Herd.)"
echo ""
