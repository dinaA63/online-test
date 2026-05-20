FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo_sqlite pdo_pgsql zip \
    && apt-get clean

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Права на запись
RUN chmod -R 777 storage bootstrap/cache

# Создаём .env и устанавливаем зависимости с отключением скриптов
RUN cp .env.example .env \
    && composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
    && composer require doctrine/dbal --no-interaction \
    && php artisan key:generate

# Копируем стартовый скрипт и делаем его исполняемым
COPY start.sh /app/start.sh
RUN chmod +x /app/start.sh

EXPOSE 10000

CMD ["/app/start.sh"]