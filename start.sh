#!/bin/bash
mkdir -p /var/data
if [ ! -f /var/data/database.sqlite ]; then
    touch /var/data/database.sqlite
    chmod 777 /var/data/database.sqlite
fi
chmod -R 777 storage bootstrap/cache

# Удаляем старый .env и создаём новый с правильными URL
rm -f .env
cp .env.example .env
php artisan key:generate

# Принудительно прописываем HTTPS
sed -i 's|APP_URL=.*|APP_URL=https://online-test-vyo8.onrender.com|g' .env
echo "ASSET_URL=https://online-test-vyo8.onrender.com" >> .env

php artisan migrate --force

# Полная очистка кэша
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan serve --host=0.0.0.0 --port=10000