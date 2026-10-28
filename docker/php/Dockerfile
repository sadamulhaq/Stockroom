FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    libzip-dev \
    oniguruma-dev \
    mysql-client \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY api/composer.json api/composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY api/ .

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

CMD ["/entrypoint.sh"]
