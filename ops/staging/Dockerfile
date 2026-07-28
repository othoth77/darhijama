# syntax=docker/dockerfile:1.7

FROM node:22-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
COPY Modules ./Modules
COPY Applications ./Applications
RUN npm run build

FROM php:8.3-fpm-bookworm AS php-base
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl default-mysql-client git libfreetype6-dev libicu-dev libjpeg62-turbo-dev \
        libpng-dev libwebp-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl opcache pcntl pdo_mysql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html

FROM php-base AS app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev --no-interaction --no-progress --prefer-dist \
    --classmap-authoritative --no-scripts
COPY . .
COPY --from=assets /build/public/build ./public/build
COPY ops/staging/php.ini /usr/local/etc/php/conf.d/99-mythos.ini
COPY ops/staging/opcache.ini /usr/local/etc/php/conf.d/10-opcache.ini
COPY ops/staging/entrypoint.sh /usr/local/bin/mythos-entrypoint
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction \
    && chmod +x /usr/local/bin/mythos-entrypoint \
    && mkdir -p storage/app/private storage/app/public storage/framework/cache \
       storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
ENTRYPOINT ["mythos-entrypoint"]
CMD ["php-fpm", "-F"]

FROM nginx:1.26-alpine AS web
COPY public /var/www/html/public
COPY --from=assets /build/public/build /var/www/html/public/build
COPY ops/staging/nginx.conf /etc/nginx/conf.d/default.conf
HEALTHCHECK --interval=15s --timeout=5s --start-period=20s --retries=5 \
    CMD wget -qO- http://127.0.0.1/up >/dev/null || exit 1
