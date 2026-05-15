FROM php:8.2-cli

# Установка системных зависимостей и расширений PHP
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_sqlite zip

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && php artisan key:generate

EXPOSE 10000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]