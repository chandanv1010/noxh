FROM php:8.3-apache

# System libraries required by the PHP extensions below
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libicu-dev libonig-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
        libxml2-dev libmagickwand-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mysqli gd zip intl bcmath exif opcache

# imagick: needed by simplesoftwareio/simple-qrcode for PNG output
RUN pecl install imagick && docker-php-ext-enable imagick

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Laravel serves from public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

RUN printf '%s\n' \
    'upload_max_filesize=64M' \
    'post_max_size=64M' \
    'memory_limit=512M' \
    'max_execution_time=300' \
    > /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html
