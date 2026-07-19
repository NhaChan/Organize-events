#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

if [ -f artisan ] && [ -f .env ]; then
    if ! grep -Eq '^APP_KEY=base64:.+' .env; then
        php artisan key:generate --force --no-interaction
    fi

    php artisan storage:link --no-interaction >/dev/null 2>&1 || true
fi

exec "$@"
