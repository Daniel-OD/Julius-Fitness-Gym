#!/bin/sh
set -e

cd /var/www/html

export PORT="${PORT:-10000}"

echo "[start-web] Binding HTTP on 0.0.0.0:${PORT} for Render port detection"

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

nginx -t

echo "[start-web] Starting PHP-FPM (background)"
php-fpm -D

echo "[start-web] Starting Laravel bootstrap in background"
chmod +x /usr/local/bin/laravel-bootstrap.sh
/usr/local/bin/laravel-bootstrap.sh >> storage/logs/render-bootstrap.log 2>&1 &

echo "[start-web] Starting nginx (foreground)"
exec nginx -g "daemon off;"
