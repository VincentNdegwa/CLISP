# FROM node:iron-alpine3.21 AS node-build
# WORKDIR /app
# COPY package.json package-lock.json ./
# RUN npm install
# COPY . .
# RUN NODE_OPTIONS="--max-old-space-size=2048" npm run build

FROM php:8.2-fpm-alpine3.18

RUN apk add --no-cache \
    libzip \
    libzip-dev \
    freetype \
    jpeg \
    libpng \
    freetype-dev \
    jpeg-dev \
    libpng-dev \
    nodejs \
    npm \
    icu-libs \
    icu-dev \
    zlib-dev \
    bzip2-dev \
    xz-dev \
    pkgconf \
    gcc \
    g++ \
    make \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install zip pdo pdo_mysql intl bcmath \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


ENV NODE_OPTIONS="--max-old-space-size=4096"

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN php artisan migrate --force \
    && php artisan route:cache

EXPOSE 9000

CMD ["php-fpm"]