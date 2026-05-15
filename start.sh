#!/bin/bash

# Создаём директорию для базы данных, если её нет
mkdir -p /var/data

# Если файла базы данных нет — создаём и даём права
if [ ! -f /var/data/database.sqlite ]; then
    touch /var/data/database.sqlite
    chmod 777 /var/data/database.sqlite
fi

# Даём права на запись в storage и кеш
chmod -R 777 storage bootstrap/cache

# Копируем .env.example в .env, если .env не существует
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi


php artisan migrate --force

# Очищаем кэш маршрутов и конфигов
php artisan route:clear
php artisan config:clear

# Запускаем сервер
php artisan serve --host=0.0.0.0 --port=10000