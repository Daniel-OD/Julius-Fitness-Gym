#!/bin/sh
set -e

cd /var/www/html

echo "[entrypoint] Julius Fitness Gym — container startup (role=${CONTAINER_ROLE:-app})"

# ── Render web: open HTTP port immediately (before DB wait / migrate) ────────
if [ "${CONTAINER_ROLE}" = "web" ]; then
    if [ ! -f .env ] && [ -n "${RENDER:-}" ]; then
        echo "[entrypoint] Render — using platform environment variables"
    fi

    if [ ! -f vendor/autoload.php ] && [ -d /.image/vendor ]; then
        mkdir -p vendor
        cp -a /.image/vendor/. vendor/
    fi

    if [ ! -f public/build/manifest.json ] && [ -d /.image/public/build ]; then
        mkdir -p public/build
        cp -a /.image/public/build/. public/build/
    fi

    if [ -n "${APP_KEY:-}" ] && [ "${APP_KEY#base64:}" != "${APP_KEY}" ]; then
        echo "[entrypoint] APP_KEY provided via environment"
    elif [ -f .env ] && grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
        echo "[entrypoint] APP_KEY present in .env"
    else
        echo "[entrypoint] Generating APP_KEY"
        php artisan key:generate --force --no-interaction
    fi

    if [ ! -f storage/data/settingsData.json ] && [ -f storage/data/settingsData.json.example ]; then
        cp storage/data/settingsData.json.example storage/data/settingsData.json
    fi

    mkdir -p storage/framework/{cache/data,sessions,testing,views} \
        storage/app/public \
        storage/data \
        storage/logs \
        bootstrap/cache

    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

    rm -f public/hot

    echo "[entrypoint] Handing off to HTTP server (nginx on PORT=${PORT:-10000})"
    exec /usr/local/bin/start-web.sh
fi

# ── Environment file (compose / worker) ───────────────────────────────────────
if [ ! -f .env ]; then
    if [ -n "${RENDER:-}" ]; then
        echo "[entrypoint] Render deployment — using platform environment variables"
    elif [ -f .env.docker.example ]; then
        echo "[entrypoint] Creating .env from .env.docker.example"
        cp .env.docker.example .env
    else
        echo "[entrypoint] ERROR: .env missing and .env.docker.example not found"
        exit 1
    fi
fi

# ── Restore vendor/build from image backup (bind-mount scenarios) ─────────────
if [ ! -f vendor/autoload.php ] && [ -d /.image/vendor ]; then
    echo "[entrypoint] Restoring vendor from image backup"
    mkdir -p vendor
    cp -a /.image/vendor/. vendor/
fi

if [ ! -f public/build/manifest.json ] && [ -d /.image/public/build ]; then
    echo "[entrypoint] Restoring frontend build from image backup"
    mkdir -p public/build
    cp -a /.image/public/build/. public/build/
fi

# ── Wait for database ────────────────────────────────────────────────────────
db_connection="${DB_CONNECTION:-mysql}"

if [ "$db_connection" = "mysql" ] && [ -n "${DB_HOST:-}" ]; then
    echo "[entrypoint] Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    until php -r "
        try {
            new PDO(
                'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                [PDO::ATTR_TIMEOUT => 3]
            );
            exit(0);
        } catch (Throwable \$e) {
            exit(1);
        }
    " 2>/dev/null; do
        sleep 2
    done
    echo "[entrypoint] MySQL is ready"
elif [ "$db_connection" = "pgsql" ] && [ -n "${DB_HOST:-}" ]; then
    echo "[entrypoint] Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT:-5432}..."
    until php -r "
        try {
            new PDO(
                'pgsql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '5432') . ';dbname=' . getenv('DB_DATABASE'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                [PDO::ATTR_TIMEOUT => 3]
            );
            exit(0);
        } catch (Throwable \$e) {
            exit(1);
        }
    " 2>/dev/null; do
        sleep 2
    done
    echo "[entrypoint] PostgreSQL is ready"
fi

# ── Composer (fallback if backup missing) ────────────────────────────────────
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] Installing Composer dependencies..."
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
fi

# ── Application key ──────────────────────────────────────────────────────────
if [ -n "${APP_KEY:-}" ] && [ "${APP_KEY#base64:}" != "${APP_KEY}" ]; then
    echo "[entrypoint] APP_KEY provided via environment"
elif [ -f .env ] && grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    echo "[entrypoint] APP_KEY present in .env"
else
    echo "[entrypoint] Generating APP_KEY"
    php artisan key:generate --force --no-interaction
fi

# ── Settings JSON ────────────────────────────────────────────────────────────
if [ ! -f storage/data/settingsData.json ] && [ -f storage/data/settingsData.json.example ]; then
    echo "[entrypoint] Creating storage/data/settingsData.json"
    cp storage/data/settingsData.json.example storage/data/settingsData.json
fi

# ── Storage structure & permissions ──────────────────────────────────────────
mkdir -p storage/framework/{cache/data,sessions,testing,views} \
    storage/app/public \
    storage/data \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

# ── Storage link ─────────────────────────────────────────────────────────────
if [ ! -e public/storage ]; then
    echo "[entrypoint] Creating storage link"
    php artisan storage:link --force --no-interaction 2>/dev/null || true
fi

rm -f public/hot

# ── App / queue: migrate & cache ─────────────────────────────────────────────
if [ "${CONTAINER_ROLE:-app}" = "app" ]; then
    echo "[entrypoint] Running migrations"
    php artisan migrate --force --no-interaction

    if [ "${APP_ENV:-production}" = "production" ]; then
        php artisan app:cache --no-interaction
    fi
fi

echo "[entrypoint] Ready — exec: $*"

exec "$@"
