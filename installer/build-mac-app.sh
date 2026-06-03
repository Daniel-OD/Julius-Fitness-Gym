#!/usr/bin/env bash
# Creează Julius Fitness Gym.app în dist/

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TEMPLATE="${ROOT}/installer/mac-app"
OUTPUT="${ROOT}/dist/Julius Fitness Gym.app"

rm -rf "${OUTPUT}"
mkdir -p "${OUTPUT}/Contents/MacOS" "${OUTPUT}/Contents/Resources"

cp "${TEMPLATE}/Contents/Info.plist" "${OUTPUT}/Contents/Info.plist"
cp "${TEMPLATE}/Contents/MacOS/Julius Fitness Gym" "${OUTPUT}/Contents/MacOS/Julius Fitness Gym"
chmod +x "${OUTPUT}/Contents/MacOS/Julius Fitness Gym"

if [[ -f "${ROOT}/public/favicon.ico" ]]; then
    # macOS prefers .icns; .ico works on some versions via file icon
    cp "${ROOT}/public/favicon.ico" "${OUTPUT}/Contents/Resources/AppIcon.ico" 2>/dev/null || true
fi

echo "Creat: ${OUTPUT}"
echo "Copiază pe Desktop sau în Applications după instalarea proiectului în ~/Herd/julius-fitness-gym"
