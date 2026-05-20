#!/bin/bash
set -e

echo "=== Starting deployment script ==="

chmod -R 777 storage bootstrap/cache

# Создаём свежий .env из .env.example, убираем кавычки и лишнее
if [ ! -f .env.example ]; then
    echo "Ошибка: .env.example не найден"
    exit 1
fi

# Удаляем старый .env, если он есть (чтобы избежать накопления мусора)
rm -f .env

# Копируем чистый .env.example
cp .env.example .env

# Принудительно устанавливаем APP_URL (убираем кавычки, удаляем пробелы)
sed -i '/^APP_URL=/d' .env
echo "APP_URL=https://online-test-vyo8.onrender.com" >> .env

# Устанавливаем ASSET_URL отдельно, после APP_URL, с новой строки
sed -i '/^ASSET_URL=/d' .env
echo "ASSET_URL=https://online-test-vyo8.onrender.com" >> .env

# Убираем кавычки в APP_NAME, если они есть, и удаляем лишние символы
sed -i 's/^APP_NAME="\?\([^"]*\)"\?/APP_NAME=\1/' .env

# Генерируем ключ
php artisan key:generate --no-interaction --force

# Миграции
php artisan migrate --force

# Очистка кэша
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan serve --host=0.0.0.0 --port=${PORT:-10000}