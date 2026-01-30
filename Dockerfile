FROM php:8.2-fpm-alpine

# Install only essential PHP extensions quickly
RUN docker-php-ext-install pdo_mysql mbstring zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Just set permissions, copy via volume
RUN chown -R www-data:www-data /var/www

CMD ["php-fpm"]