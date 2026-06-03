#!/usr/bin/env bash
# Construiește DMG pentru macOS (rulează pe Mac)

set -euo pipefail

VERSION="1.0"
APP_NAME="Julius Fitness Gym"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
STAGING="${ROOT}/dist/mac-staging"
DMG_PATH="${ROOT}/dist/Julius-Fitness-Gym-Setup-v${VERSION}.dmg"
VOLUME_NAME="${APP_NAME}"

cd "${ROOT}"

echo "Construire installer ${APP_NAME} v${VERSION} (macOS)..."
echo ""

echo "Compilare assets frontend..."
npm run build

echo "Pregătire staging..."
rm -rf "${STAGING}"
mkdir -p "${STAGING}"

rsync -a \
    --exclude='.git' \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='dist' \
    --exclude='.env' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    --exclude='installer/build-dmg.sh' \
    --exclude='installer/build-installer.bat' \
    --exclude='installer/julius-fitness-gym.iss' \
    "${ROOT}/" "${STAGING}/"

chmod +x "${STAGING}/install.sh" \
    "${STAGING}/open.sh" \
    "${STAGING}/open.command" \
    "${STAGING}/installer/check-prerequisites.sh"

cat > "${STAGING}/INSTALARE-macOS.txt" <<'EOF'
Julius Fitness Gym — Instalare macOS
====================================

1. Instalează Laravel Herd, Composer și Node.js (dacă nu le ai).
2. Copiază acest folder în: ~/Herd/julius-fitness-gym
   (înlocuiește folderul existent dacă e cazul.)
3. Deschide Terminal în folderul copiat și rulează:
     ./install.sh
4. Verifică în Herd că site-ul julius-fitness-gym.test este activ.
5. Dublu-click pe open.command sau deschide:
     http://julius-fitness-gym.test

Admin Filament: /admin
Creează utilizatorul admin după prima instalare (php artisan make:filament-user)
sau rulează seeder-ul dacă este configurat în proiect.
EOF

mkdir -p "${ROOT}/dist"
rm -f "${DMG_PATH}"

echo "Creare DMG..."
hdiutil create \
    -volname "${VOLUME_NAME}" \
    -srcfolder "${STAGING}" \
    -ov \
    -format UDZO \
    "${DMG_PATH}"

echo ""
echo "Gata! Installer creat în:"
echo "  ${DMG_PATH}"
echo ""
