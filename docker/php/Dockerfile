FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    intl \
    opcache \
    mbstring \
    exif \
    pcntl \
    bcmath \
    xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Install Node.js and npm for React
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs

# Install Yarn
RUN npm install -g yarn

# Configure PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]