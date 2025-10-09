FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    libssl-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install MongoDB PHP extension (with SSL support)
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app and install dependencies
COPY . /var/www/html
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
