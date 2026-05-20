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

# Копируем только composer.json и composer.lock (если есть) для установки зависимостей
COPY composer.json composer.lock* /app/

ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_NO_SCRIPTS=1

# Устанавливаем production-зависимости (без dev-пакетов)
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# Теперь копируем остальной код
COPY . /app

# Если нужно doctrine/dbal — он уже должен быть в composer.json!
# Не используйте composer require в Dockerfile. Добавьте пакет локально.
# RUN composer require doctrine/dbal --no-interaction --no-scripts || true  # НЕ ДЕЛАЙТЕ ТАК

RUN chmod -R 777 storage bootstrap/cache

COPY start.sh /app/start.sh
RUN chmod +x /app/start.sh

EXPOSE 10000

CMD ["/app/start.sh"]