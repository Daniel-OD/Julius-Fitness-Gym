#!/bin/sh
set -e

export PORT="${PORT:-10000}"

echo "[start-web] Configuring nginx to listen on port ${PORT}"
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

echo "[start-web] Starting PHP-FPM (background)"
php-fpm -D

echo "[start-web] Starting nginx (foreground on port ${PORT})"
exec nginx -g "daemon off;"
