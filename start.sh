#!/bin/bash
set -e

echo "=== Starting deployment script ==="

# Права уже установлены в Dockerfile, но на всякий случай
chmod -R 777 storage bootstrap/cache

# Генерируем APP_KEY, если .env нет (на самом деле он уже должен быть)
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --no-interaction --force
fi

# Устанавливаем APP_URL и ASSET_URL (можно и в .env руками)
sed -i 's|APP_URL=.*|APP_URL=https://online-test-vyo8.onrender.com|g' .env
if ! grep -q "^ASSET_URL=" .env; then
    echo "ASSET_URL=https://online-test-vyo8.onrender.com" >> .env
fi

# Запускаем миграции (не fresh, чтобы не удалять данные)
php artisan migrate --force -v

# Кэшируем конфигурацию и маршруты
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Запускаем сервер
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}