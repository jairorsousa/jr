FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    mysql-client \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        bcmath \
        gd \
        zip \
        intl \
        mbstring \
        opcache \
        pcntl

RUN pecl install redis && docker-php-ext-enable redis

# PHP performance config
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js for asset building
RUN apk add --no-cache nodejs npm

WORKDIR /var/www/html

RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

COPY . .

RUN chown -R www:www /var/www/html

USER www

EXPOSE 9000

CMD ["php-fpm"]
