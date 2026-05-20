#!/bin/bash
set -e

echo "=== Starting deployment script ==="
chmod -R 777 storage bootstrap/cache

# Создаём минимальный .env с правильными кавычками
cat > .env <<EOF
APP_NAME="Система тестирования"
APP_URL=https://online-test-vyo8.onrender.com
APP_KEY=
DB_CONNECTION=pgsql
DB_URL=postgresql://postgres.ixyjygbbyuyarbznaifh:dinAgodd5054@aws-0-eu-west-1.pooler.supabase.com:5432/postgres
EOF

# Генерируем ключ
php artisan key:generate --no-interaction --force

# Миграции
php artisan migrate --force

# Очистка кэша
php artisan config:clear
php artisan route:clear
php artisan view:clear

 php artisan migrate:status 2>&1 # для вывода ошибок в лог

php artisan serve --host=0.0.0.0 --port=${PORT:-10000}