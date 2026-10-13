# syntax=docker/dockerfile:1

FROM node:22-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json* .npmrc* ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js ./vite.config.js
RUN npm run build

FROM php:8.4-fpm-alpine AS application

RUN apk add --no-cache \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libwebp \
        libzip \
        oniguruma \
        sqlite-libs \
    && apk add --no-cache --virtual .build-deps \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        linux-headers \
        oniguruma-dev \
        sqlite-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl mbstring opcache pcntl pdo_mysql pdo_sqlite zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --optimize-autoloader \
    && rm -rf storage/logs/* storage/framework/cache/data/* storage/framework/sessions/* storage/framework/views/* \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/php/entrypoint.sh /usr/local/bin/bicharoco-entrypoint
RUN chmod 0755 /usr/local/bin/bicharoco-entrypoint

ENTRYPOINT ["bicharoco-entrypoint"]
CMD ["php-fpm", "-F"]

FROM nginx:1.29-alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=application /var/www/html/public /var/www/html/public

