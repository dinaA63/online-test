FROM php:8.4-cli
ENV COMPOSER_NO_SCRIPTS=1

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

RUN chmod -R 777 storage bootstrap/cache

RUN cp .env.example .env \
    && composer install --no-interaction --prefer-dist --optimize-autoloader \
    && php artisan key:generate

COPY start.sh /app/start.sh
RUN chmod +x /app/start.sh

EXPOSE 10000

CMD ["/app/start.sh"]