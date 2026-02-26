FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install APCu via PECL
RUN pecl install apcu \
    && docker-php-ext-enable apcu \
    && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

# Enable Apache mod_rewrite (optional but commonly needed)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html