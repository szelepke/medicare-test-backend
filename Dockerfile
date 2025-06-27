FROM php:8.3-fpm-alpine

RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    jpegoptim \
    pngquant \
    gifsicle \
    vim \
    nano \
    mysql-client \
    oniguruma-dev \
    bash \
    autoconf \
    make \
    gcc \
    g++

RUN docker-php-ext-install pdo_mysql gd zip mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 -S sail && \
    adduser -u 1000 -S sail -G sail

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R sail:sail /var/www

USER sail

EXPOSE 9000
CMD ["php-fpm"]
