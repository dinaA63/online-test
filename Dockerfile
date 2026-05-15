FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_sqlite zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Права на запись
RUN chmod -R 777 storage bootstrap/cache

# Создаём .env и устанавливаем зависимости
RUN cp .env.example .env \
    && composer install --no-interaction --prefer-dist --optimize-autoloader \
    && php artisan key:generate

# Копируем стартовый скрипт и делаем его исполняемым
COPY start.sh /start.sh
RUN chmod +x /start.sh
EXPOSE 10000

CMD ["/app/start.sh"]