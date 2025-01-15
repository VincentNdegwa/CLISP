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

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .


RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache


COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader

ENV NODE_OPTIONS="--max-old-space-size=4096"

COPY package.json package-lock.json /var/www/html/

WORKDIR /var/www/html

RUN npm install && npm run build

EXPOSE 9000

CMD ["php-fpm"]