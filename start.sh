#!/bin/bash
set -e
set -x

echo "=== Starting deployment script ==="

chmod -R 777 storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Ключевое исправление: отключаем выполнение скриптов при дампе автозагрузки
composer dump-autoload --optimize --no-scripts

# Далее всё без изменений
php artisan key:generate --no-interaction --force

sed -i 's|APP_URL=.*|APP_URL=https://online-test-vyo8.onrender.com|g' .env
if ! grep -q "^ASSET_URL=" .env; then
    echo "ASSET_URL=https://online-test-vyo8.onrender.com" >> .env
fi

php artisan migrate --force

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan serve --host=0.0.0.0 --port=${PORT:-10000}