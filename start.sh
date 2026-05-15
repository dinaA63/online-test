#!/bin/bash
set -e  # Останавливаемся при любой ошибке
set -x  # Выводим каждую команду (отладка)

echo "=== Starting deployment script ==="

# Права на папки
chmod -R 777 storage bootstrap/cache

# Создание базы данных SQLite
mkdir -p /var/data
if [ ! -f /var/data/database.sqlite ]; then
    touch /var/data/database.sqlite
    chmod 777 /var/data/database.sqlite
fi

# Настройка .env
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Генерация ключа
php artisan key:generate --no-interaction --force

# Установка APP_URL и ASSET_URL
sed -i 's|APP_URL=.*|APP_URL=https://online-test-vyo8.onrender.com|g' .env
if grep -q "^ASSET_URL=" .env; then
    sed -i 's|ASSET_URL=.*|ASSET_URL=https://online-test-vyo8.onrender.com|g' .env
else
    echo "ASSET_URL=https://online-test-vyo8.onrender.com" >> .env
fi

# Миграции (без фатальной остановки, если таблицы уже есть)
php artisan migrate --force || echo "Migrate failed, but continuing..."

# Очистка кэша
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Запуск сервера
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}