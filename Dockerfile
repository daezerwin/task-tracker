# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1: build frontend assets (Tailwind via Vite)
# ---------------------------------------------------------------------------
# Built natively on the runner's own platform — the output is plain CSS/JS,
# identical for every target architecture, so there's no need to run npm
# under QEMU emulation for the arm64 image.
FROM --platform=$BUILDPLATFORM node:20-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
# Tailwind also scans app/Enums for the category colour classes.
COPY app/Enums ./app/Enums
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2: install PHP dependencies
# ---------------------------------------------------------------------------
FROM --platform=$BUILDPLATFORM composer:2 AS vendor
WORKDIR /app

COPY database ./database
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# ---------------------------------------------------------------------------
# Stage 3: runtime image (Apache + PHP)
# ---------------------------------------------------------------------------
FROM php:8.4-apache AS app

ARG APP_VERSION=dev
ENV APP_VERSION=${APP_VERSION}

RUN apt-get update && apt-get install -y --no-install-recommends \
        libsqlite3-dev \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_sqlite \
        mbstring \
        bcmath \
        zip \
        xml \
        dom \
        curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Regenerate the package-discovery cache against this (--no-dev) vendor
# install — a stale cache copied in from local dev would reference dev-only
# packages (e.g. laravel/pail, laravel/breeze) that don't exist here and
# crash on boot.
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && php artisan package:discover --ansi

# The SQLite file lives in its own directory outside the app tree
# (/var/lib/task-tracker), never inside database/ — that directory also
# holds migrations/seeders/factories, which are code and must always come
# fresh from the image. Docker only seeds a named volume from image content
# the first time it's empty, so mounting a volume over database/ would
# permanently shadow those PHP files behind a stale, code-less volume.
RUN mkdir -p /var/lib/task-tracker \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        bootstrap/cache \
    && touch /var/lib/task-tracker/database.sqlite \
    && chown -R www-data:www-data /var/lib/task-tracker storage bootstrap/cache \
    && chmod -R 775 /var/lib/task-tracker storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
