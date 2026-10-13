#!/bin/sh
set -eu

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    database_path="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$database_path")"
    touch "$database_path"
fi

chown -R www-data:www-data storage bootstrap/cache database

php artisan migrate --force --isolated
php artisan storage:link --force
php artisan optimize

exec "$@"

