#!/bin/sh
set -e

cd /var/www/html

# Render sets RENDER, RENDER_SERVICE_ID, and/or RENDER_EXTERNAL_URL (not always all three).
if [ -n "${RENDER:-}" ] || [ -n "${RENDER_SERVICE_ID:-}" ] || [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export JULIUS_ON_RENDER=1
fi

echo "[entrypoint] Julius Fitness Gym — container startup (role=${CONTAINER_ROLE:-app})"

# Web services must use Docker target "production" (nginx on $PORT). Force web on Render if misconfigured.
if [ -n "${JULIUS_ON_RENDER:-}" ] && [ "${CONTAINER_ROLE:-app}" = "app" ]; then
    export CONTAINER_ROLE=web
    echo "[entrypoint] Render detected — forcing CONTAINER_ROLE=web (Dockerfile dockerTarget must be: production)"
fi

if [ -n "${JULIUS_ON_RENDER:-}" ] && [ "${DB_HOST:-}" = "mysql" ] && [ "${DB_CONNECTION:-}" = "mysql" ]; then
    echo "[entrypoint] WARNING: DB_HOST=mysql looks like docker-compose defaults — link PostgreSQL and set DB_CONNECTION=pgsql in Render"
fi

# Ensure .env exists with APP_KEY and Render env vars (php-fpm does not always inherit env).
ensure_app_key() {
    if [ -n "${JULIUS_ON_RENDER:-}" ]; then
        env | grep -E '^(APP_|DB_|LOG_|SESSION_|CACHE_|QUEUE_|FILESYSTEM_|MAIL_|RESEND_|DATABASE_URL|RENDER_EXTERNAL_URL|RENDER|RENDER_SERVICE_ID)=' \
            > /tmp/render-container.env 2>/dev/null || true
    fi

    php /usr/local/bin/ensure-env.php /var/www/html/.env

    if grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
        echo "[entrypoint] APP_KEY is configured in .env"
    else
        echo "[entrypoint] ERROR: APP_KEY missing after ensure-env.php"
        exit 1
    fi

    if [ -n "${JULIUS_ON_RENDER:-}" ]; then
        var_count=$(grep -cE '^[A-Z_]+=' .env 2>/dev/null || echo 0)
        echo "[entrypoint] .env: ${var_count} variables, APP_URL=$(grep '^APP_URL=' .env | cut -d= -f2- | head -1), DB_HOST=$(grep '^DB_HOST=' .env | cut -d= -f2- | head -1)"
        if [ "$var_count" -lt 10 ]; then
            echo "[entrypoint] ERROR: .env incomplete — redeploy with latest image (env/render.env.example must be in the image)"
            exit 1
        fi
    fi
}

run_render_migrations() {
    if [ ! -f .env ]; then
        echo "[entrypoint] ERROR: .env missing — cannot run migrations"
        exit 1
    fi

    set -a
    # shellcheck disable=SC1091
    . ./.env
    set +a

    # shellcheck disable=SC1091
    . /usr/local/bin/db-wait.sh
    wait_for_database

    echo "[entrypoint] Running database migrations (Render web)"
    php artisan migrate --force --no-interaction
    touch /tmp/julius-migrations-applied

    if [ ! -e public/storage ]; then
        php artisan storage:link --force --no-interaction 2>/dev/null || true
    fi
}

# ── Render web: migrate before HTTP, then nginx on $PORT ─────────────────────
if [ "${CONTAINER_ROLE}" = "web" ]; then
    if [ -n "${JULIUS_ON_RENDER:-}" ]; then
        echo "[entrypoint] Render — building .env from env/render.env.example + platform variables"
    fi

    if [ ! -f vendor/autoload.php ] && [ -d /.image/vendor ]; then
        mkdir -p vendor
        cp -a /.image/vendor/. vendor/
    fi

    if [ ! -f public/build/manifest.json ] && [ -d /.image/public/build ]; then
        echo "[entrypoint] Restoring Vite build from image backup"
        mkdir -p public/build
        cp -a /.image/public/build/. public/build/
    fi

    if [ ! -f public/build/manifest.json ]; then
        echo "[entrypoint] ERROR: public/build/manifest.json missing — image must include npm run build output"
        exit 1
    fi

    ensure_app_key

    php artisan config:clear --no-interaction 2>/dev/null || true

    if [ ! -f storage/data/settingsData.json ] && [ -f storage/data/settingsData.json.example ]; then
        cp storage/data/settingsData.json.example storage/data/settingsData.json
    fi

    mkdir -p storage/framework/{cache/data,sessions,testing,views} \
        storage/app/public \
        storage/app/private/livewire-tmp \
        storage/data \
        storage/logs \
        bootstrap/cache

    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

    rm -f public/hot

    if [ -n "${JULIUS_ON_RENDER:-}" ]; then
        run_render_migrations
    fi

    echo "[entrypoint] Handing off to HTTP server (nginx on PORT=${PORT:-10000})"
    exec /usr/local/bin/start-web.sh
fi

# ── Environment file (compose / worker) ───────────────────────────────────────
if [ ! -f .env ]; then
    if [ -n "${JULIUS_ON_RENDER:-}" ]; then
        echo "[entrypoint] Render deployment — building .env from env/render.env.example"
        php /usr/local/bin/ensure-env.php /var/www/html/.env
    elif [ -f env/docker.env.example ]; then
        echo "[entrypoint] Creating .env from env/docker.env.example"
        cp env/docker.env.example .env
    else
        echo "[entrypoint] ERROR: .env missing and env/docker.env.example not found"
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
ensure_app_key

# ── Settings JSON ────────────────────────────────────────────────────────────
if [ ! -f storage/data/settingsData.json ] && [ -f storage/data/settingsData.json.example ]; then
    echo "[entrypoint] Creating storage/data/settingsData.json"
    cp storage/data/settingsData.json.example storage/data/settingsData.json
fi

# ── Storage structure & permissions ──────────────────────────────────────────
mkdir -p storage/framework/{cache/data,sessions,testing,views} \
    storage/app/public \
    storage/app/private/livewire-tmp \
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
