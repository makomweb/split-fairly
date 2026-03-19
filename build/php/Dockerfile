# -----------------------------------
# Base stage for Development (Debian-based)
# -----------------------------------
FROM php:8.4-fpm AS base-dev

WORKDIR /var/www/project

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential autoconf libicu-dev git libzip-dev zip librabbitmq-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo opcache pdo_mysql zip \
    && pecl install opentelemetry redis amqp \
    && docker-php-ext-enable opentelemetry redis amqp \
    && apt-get remove --purge -y build-essential autoconf \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

COPY build/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# -----------------------------------
# Base stage for Production (Alpine-based, minimal)
# -----------------------------------
FROM php:8.4.3-fpm-alpine3.20 AS base-prod

WORKDIR /var/www/project

RUN apk add --no-cache --virtual .build-deps \
    build-base autoconf icu-dev libzip-dev \
    && apk add --no-cache libintl git zip libzip icu-libs \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo opcache pdo_mysql zip \
    && pecl install opentelemetry redis \
    && docker-php-ext-enable opentelemetry redis \
    && apk del .build-deps icu-dev libzip-dev

COPY build/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# -----------------------------------
# Development-Stage (with Composer + XDebug, from Debian base)
# -----------------------------------
FROM base-dev AS dev

RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential autoconf git zip unzip \
    && pecl install xdebug && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && apt-get remove --purge -y build-essential autoconf \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------
# Vendor-Stage (from Debian base for build compatibility)
# -----------------------------------
FROM base-dev AS vendor

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY backend/composer.json .
COPY backend/composer.lock .
COPY backend/.env .env
COPY backend/bin ./bin
COPY backend/src ./src
COPY backend/public ./public

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev git zip unzip \
    && composer install \
        --no-dev \
        --optimize-autoloader \
        --classmap-authoritative \
    && apt-get remove --purge -y git zip unzip \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------
# Frontend-Stage (build React app)
# -----------------------------------
FROM node:25.2-alpine AS frontend

WORKDIR /frontend

COPY frontend .

RUN npm install && npm run build

RUN cp -r /backend/public/build /dist

# -----------------------------------
# Production-Stage (Alpine-based, lean runtime)
# -----------------------------------
FROM base-prod AS prod

COPY --from=vendor /var/www/project/vendor ./vendor
COPY --from=frontend /dist ./public/build

COPY backend/.env .
COPY backend/bin ./bin
COPY backend/composer.json .
COPY backend/config ./config
COPY backend/migrations ./migrations
COPY backend/public ./public
COPY backend/src ./src
COPY backend/templates ./templates

RUN mkdir -p var/cache var/log && chmod -R 777 var
