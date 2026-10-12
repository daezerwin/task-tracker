#!/bin/sh
set -e

cd /var/www/html

mkdir -p /var/lib/task-tracker storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
touch /var/lib/task-tracker/database.sqlite

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set. Generate one with:"
    echo "  docker run --rm php:8.4-cli php -r \"echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;\""
    echo "and set it as the APP_KEY environment variable."
    exit 1
fi

php artisan migrate --force

# Syncs the starter chore templates (idempotent — see ChoreTemplateSeeder),
# so changes to that list reach this deployment on every upgrade.
php artisan db:seed --force

chown -R www-data:www-data /var/lib/task-tracker storage bootstrap/cache

exec "$@"
