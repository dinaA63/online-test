#!/bin/bash
set -e
set -x

echo "=== Starting deployment script ==="

chmod -R 777 storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Переменная окружения отключает скрипты Composer
export COMPOSER_NO_SCRIPTS=1

# Установка зависимостей
composer install --no-interaction --prefer-dist --optimize-autoloader

# Пакет для изменения колонок (если нужен)
composer require doctrine/dbal --no-interaction --no-scripts || true

# Генерация ключа
php artisan key:generate --no-interaction --force

# Настройка URL
sed -i 's|APP_URL=.*|APP_URL=https://online-test-vyo8.onrender.com|g' .env
if ! grep -q "^ASSET_URL=" .env; then
    echo "ASSET_URL=https://online-test-vyo8.onrender.com" >> .env
fi

# Миграции
php artisan migrate:fresh --force -v

# Кэш
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Запуск сервера
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}