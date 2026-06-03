#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

echo
echo "  Încălzire cache aplicație (pagini mai rapide)..."
echo

php artisan app:cache --no-interaction

echo
echo "  Gata! Repornește serverul dacă rulează: php artisan serve"
echo
