FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_sqlite zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Даём права на запись нужным папкам
RUN chmod -R 777 storage bootstrap/cache

# Создаём .env из .env.example (в нём уже есть APP_KEY=)
RUN cp .env.example .env

# Устанавливаем зависимости и генерируем ключ
RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && php artisan key:generate

EXPOSE 10000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]