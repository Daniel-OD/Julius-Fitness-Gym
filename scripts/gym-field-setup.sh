#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo ""
echo " Setup pentru test în sală..."
echo ""

mkdir -p database
touch database/database.sqlite

composer install --no-interaction --prefer-dist

[[ -f .env ]] || cp .env.example .env
[[ -f storage/data/settingsData.json ]] || cp storage/data/settingsData.json.example storage/data/settingsData.json

php artisan key:generate --force
php artisan migrate --force
php artisan storage:link 2>/dev/null || true

if command -v node >/dev/null 2>&1; then
    npm install --ignore-scripts
    npm run build
else
    echo " [ATENȚIE] Node.js lipsește — rulează npm run build pe alt PC sau instalează Node.js"
fi

echo ""
echo " Creare admin de test (parolă temporară)..."
php artisan app:install --no-interaction --force \
    --email=admin@julius.test \
    --password='GymTest2026!' \
    --url=http://127.0.0.1:8000

echo ""
echo " Încărcare date demo..."
php artisan db:seed --force

echo ""
echo " ========================================"
echo "  Setup finalizat!"
echo " ========================================"
echo "  Pornire:  php artisan serve"
echo "  Admin:    http://127.0.0.1:8000/admin/login"
echo "  Office:   http://127.0.0.1:8000/office/login"
echo "  Email:    admin@julius.test"
echo "  Parolă:   GymTest2026!  (TEMPORARĂ — schimbă după login)"
echo ""
