FROM php:8.4-fpm-alpine

# Установка системных пакетов, Nginx и Chromium для парсинга
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    nginx \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont\
    tesseract-ocr \
    tesseract-ocr-data-rus  \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    linux-headers

# Скачиваем скрипт напрямую из релизов GitHub (вместо Docker Hub)
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Установка PHP расширений
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    dom \
    sockets


# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка рабочей директории
WORKDIR /var/www

# Копируем исходный код проекта (включая локальный vendor)
COPY . .

# RUN composer install --no-dev --optimize-autoloader --no-scripts

# Создаем структуру папок Laravel и выставляем безопасные права www-data
RUN mkdir -p /var/www/storage/framework/cache/data \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Настройка прав для Nginx и Chromium во временных папках Alpine
RUN chmod 777 /tmp \
    && chown -R www-data:www-data /tmp \
    && chown -R www-data:www-data /var/lib/nginx \
    && chown -R www-data:www-data /var/log/nginx

# Настройка Nginx
RUN rm -rf /etc/nginx/http.d/default.conf && rm -rf /etc/nginx/nginx.conf
COPY nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

# Стартуем от root: php-fpm и nginx сами сбросят права до www-data после старта
CMD php-fpm -D && nginx -g "daemon off;"
