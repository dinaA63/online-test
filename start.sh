#!/bin/bash
set -e
set -x

echo "=== Starting deployment script ==="

chmod -R 777 storage bootstrap/cache

# Создаём .env, если его нет
if [ ! -f .env ]; then
    cp .env.example .env
fi

# 🔥 Важно: обновляем автозагрузку (так как в Dockerfile был --no-scripts)
composer dump-autoload --optimize

# Генерация ключа
php artisan key:generate --no-interaction --force

# Установка APP_URL и ASSET_URL
sed -i 's|APP_URL=.*|APP_URL=https://online-test-vyo8.onrender.com|g' .env
if ! grep -q "^ASSET_URL=" .env; then
    echo "ASSET_URL=https://online-test-vyo8.onrender.com" >> .env
fi

# Убеждаемся, что используется PostgreSQL
# (строка ниже не обязательна, если DB_URL уже задана в .env)
# echo "DB_CONNECTION=pgsql" >> .env

# Выполняем миграции
php artisan migrate --force

# Очистка кэша
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Запуск сервера
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}