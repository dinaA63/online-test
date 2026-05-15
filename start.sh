#!/bin/bash
set -e  # остановка при любой ошибке
set -x  # вывод каждой команды (полезно для отладки)

echo "=== Starting deployment script ==="

# Права на папки
chmod -R 777 storage bootstrap/cache

# Создание базы данных SQLite
mkdir -p /var/data
if [ ! -f /var/data/database.sqlite ]; then
    touch /var/data/database.sqlite
    chmod 777 /var/data/database.sqlite
fi

# Настройка .env – если нет, копируем из .env.example
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Генерация ключа приложения
php artisan key:generate --no-interaction --force

# Принудительная установка HTTPS URL
sed -i 's|APP_URL=.*|APP_URL=https://online-test-vyo8.onrender.com|g' .env
if grep -q "^ASSET_URL=" .env; then
    sed -i 's|ASSET_URL=.*|ASSET_URL=https://online-test-vyo8.onrender.com|g' .env
else
    echo "ASSET_URL=https://online-test-vyo8.onrender.com" >> .env
fi

# Указываем путь к SQLite базе (если используется)
echo "DB_CONNECTION=sqlite" >> .env
echo "DB_DATABASE=/var/data/database.sqlite" >> .env

# Выполнение миграций (без остановки при ошибке)
php artisan migrate --force || echo "Migrate failed, but continuing..."

# Очистка кэша конфигурации, маршрутов и представлений
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Опционально: кэширование конфигурации для продакшена (раскомментировать при необходимости)
# php artisan config:cache
# php artisan route:cache

echo "=== Starting PHP built-in server ==="
# Запуск сервера на порту, который ожидает Render
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}