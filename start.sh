#!/bin/bash
mkdir -p /var/data
if [ ! -f /var/data/database.sqlite ]; then
    touch /var/data/database.sqlite
    chmod 777 /var/data/database.sqlite
fi
chmod -R 777 storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi


php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan serve --host=0.0.0.0 --port=10000