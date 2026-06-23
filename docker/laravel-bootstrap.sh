#!/bin/sh
# Background Laravel setup for Render (runs after HTTP port is already open).

cd /var/www/html

if [ -f .env ]; then
    set -a
    # shellcheck disable=SC1091
    . ./.env
    set +a
fi

log() {
    echo "[bootstrap] $*"
}

# shellcheck disable=SC1091
. /usr/local/bin/db-wait.sh

log "Starting background setup"

if [ -f /tmp/julius-migrations-applied ]; then
    log "Migrations already applied by entrypoint — skipping"
else
    wait_for_database || log "database wait failed — migrate may fail"

    if [ ! -e public/storage ]; then
        php artisan storage:link --force --no-interaction 2>/dev/null || true
    fi

    php artisan migrate --force --no-interaction || log "migrate failed (will retry on next deploy)"
fi

if [ -f .env ] && grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan config:clear --no-interaction 2>/dev/null || true

    if [ "${APP_ENV:-production}" = "production" ]; then
        php artisan app:cache --no-interaction || log "app:cache failed"
    fi
else
    log "Skipping app:cache until APP_KEY is configured"
fi

log "Background setup complete"
