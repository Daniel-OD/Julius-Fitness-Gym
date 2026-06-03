#!/usr/bin/env bash
# Construiește DMG + .app pentru macOS (rulează pe Mac)

set -euo pipefail

VERSION="1.0"
APP_NAME="Julius Fitness Gym"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
STAGING="${ROOT}/dist/mac-staging"
DMG_PATH="${ROOT}/dist/Julius-Fitness-Gym-Setup-v${VERSION}.dmg"
VOLUME_NAME="${APP_NAME}"

cd "${ROOT}"

echo "Construire pachet ${APP_NAME} v${VERSION} (macOS)..."
echo ""

echo "Dependențe PHP (production)..."
composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader

echo "Compilare assets frontend..."
npm run build

echo "Pregătire staging..."
rm -rf "${STAGING}"
mkdir -p "${STAGING}"

rsync -a \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='dist' \
    --exclude='.env' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    --exclude='storage/app/install-credentials.txt' \
    --exclude='storage/app/.install-complete' \
    --exclude='installer/build-dmg.sh' \
    --exclude='installer/build-mac-app.sh' \
    --exclude='installer/build-installer.bat' \
    --exclude='installer/julius-fitness-gym.iss' \
    --exclude='installer/mac-app' \
    "${ROOT}/" "${STAGING}/"

chmod +x "${STAGING}/install.sh" \
    "${STAGING}/install.bat" \
    "${STAGING}/open.sh" \
    "${STAGING}/open.command" \
    "${STAGING}/installer/post-install.sh" \
    "${STAGING}/installer/post-install.bat" \
    "${STAGING}/installer/check-prerequisites.sh" \
    "${STAGING}/installer/check-prerequisites.bat"

bash "${ROOT}/installer/build-mac-app.sh"
cp -R "${ROOT}/dist/Julius Fitness Gym.app" "${STAGING}/"

cat > "${STAGING}/INSTALARE-macOS.txt" <<'EOF'
Julius Fitness Gym — Instalare macOS (automatizată)
==================================================

1. Instalează Laravel Herd: https://herd.laravel.com
   (Composer și Node NU sunt necesare — sunt incluse în pachet.)

2. Copiază tot conținutul DMG în: ~/Herd/julius-fitness-gym

3. Dublu-click "Julius Fitness Gym.app" (configurează + deschide admin)
   SAU în Terminal: ./install.sh

4. Trage "Julius Fitness Gym.app" pe Desktop pentru acces rapid.

Site:  http://julius-fitness-gym.test
Admin: http://julius-fitness-gym.test/admin

Credențiale: storage/app/install-credentials.txt
(Implicit: admin@julius.test / julius2024 — schimbă parola după login)
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
echo "Gata!"
echo "  DMG: ${DMG_PATH}"
echo "  App: ${ROOT}/dist/Julius Fitness Gym.app"
echo ""
