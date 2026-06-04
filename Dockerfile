# syntax=docker/dockerfile:1

# ── Stage 1: Composer + Vite (composer MUST run before npm run build) ─────────
FROM php:8.4-cli-bookworm AS assets

RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    gnupg \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        xml \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

WORKDIR /app

# Dependency manifests first (Docker layer cache)
COPY composer.json composer.lock package.json package-lock.json ./

# 1. Composer FIRST — vendor/ required for Filament theme.css @import
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Application source (Vite @source paths, autoload classmap)
COPY . .

RUN composer dump-autoload --optimize

# 2. NPM after vendor/ exists
RUN npm ci --ignore-scripts \
    && npm run build

# ── Stage 2: Application (PHP-FPM 8.4) ───────────────────────────────────────
FROM php:8.4-fpm-bookworm AS app

LABEL org.opencontainers.image.title="Julius Fitness Gym"
LABEL org.opencontainers.image.description="Laravel 13 gym management application"

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        xml \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-julius-gym.ini

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=assets --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# Image backups for bind-mount dev (entrypoint restores if host dirs are empty)
COPY --from=assets --chown=www-data:www-data /app/vendor /.image/vendor
COPY --from=assets --chown=www-data:www-data /app/public/build /.image/public/build

RUN mkdir -p storage/framework/{cache/data,sessions,testing,views} \
    storage/app/public \
    storage/data \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=10s --start-period=90s --retries=3 \
    CMD php artisan about --no-interaction > /dev/null 2>&1 || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

# ── Stage 3: Render Web Service (nginx + PHP-FPM on $PORT) ───────────────────
FROM app AS production

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    gettext-base \
    wget \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default

COPY docker/nginx/render.conf.template /etc/nginx/templates/default.conf.template
COPY docker/start-web.sh /usr/local/bin/start-web.sh
COPY docker/laravel-bootstrap.sh /usr/local/bin/laravel-bootstrap.sh
RUN chmod +x /usr/local/bin/start-web.sh /usr/local/bin/laravel-bootstrap.sh

ENV CONTAINER_ROLE=web

# Render injects PORT (e.g. 10000) — nginx binds to it in start-web.sh
EXPOSE 10000

HEALTHCHECK --interval=30s --timeout=10s --start-period=120s --retries=3 \
    CMD sh -c 'port="${PORT:-10000}"; wget -q -O /dev/null "http://127.0.0.1:${port}/up" || exit 1'

# ENTRYPOINT runs setup; start-web.sh runs php-fpm -D + nginx foreground
CMD ["nginx", "-g", "daemon off;"]
