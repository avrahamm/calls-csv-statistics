FROM php:8.4-fpm

# Create a non-root user with the same UID/GID as the host user
RUN apt-get update && apt-get install -y sudo
RUN groupadd --gid 1000 appuser \
    && useradd --uid 1000 --gid 1000 --shell /bin/bash --create-home appuser \
    && echo "appuser ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/appuser

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    gzip \
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
WORKDIR /app
RUN mkdir -p /app && chown -R appuser:appuser /app

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN find / -name symfony -type f 2>/dev/null | xargs -I {} cp {} /usr/local/bin/symfony || \
    (curl -sS https://github.com/symfony/cli/releases/latest/download/symfony_linux_amd64.gz | gzip -d > /usr/local/bin/symfony && chmod +x /usr/local/bin/symfony)

# Install Node.js and npm for React
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get install -y nodejs

# Verify Node.js and npm installation
RUN node --version && npm --version

# Install Yarn
RUN npm install -g yarn

# Configure PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Comment out user and group directives in www.conf to avoid warnings when running as non-root
RUN sed -i 's/^user = www-data/;user = www-data/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/^group = www-data/;group = www-data/' /usr/local/etc/php-fpm.d/www.conf

# Switch to appuser for subsequent operations
USER appuser

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
