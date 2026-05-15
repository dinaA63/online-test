FROM php:8.4-cli

# Установка системных зависимостей и расширений PHP
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo_sqlite zip \
    && apt-get clean

# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /app

# Копируем все файлы проекта
COPY . /app

# Устанавливаем права на запись для storage и cache
RUN chmod -R 777 storage bootstrap/cache

# Копируем .env.example как .env (временный, будет заменён при запуске)
RUN cp .env.example .env

# Устанавливаем зависимости БЕЗ выполнения скриптов (--no-scripts)
# Флаг --optimize-autoloader создаёт оптимизированный автозагрузчик
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Копируем стартовый скрипт и даём права на выполнение
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 10000

# Запускаем стартовый скрипт через bash
CMD ["/bin/bash", "/start.sh"]