# --- Stage 1: Build Assets ---
FROM node:20-alpine AS assets-builder
WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm install

# Copy source code and compile Vite assets
COPY . .
RUN npm run build

# --- Stage 2: Main Application ---
FROM php:8.3-apache

# Install system dependencies for PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        intl \
        opcache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy custom Apache configuration
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set working directory inside container
WORKDIR /var/www/html

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy only dependency files first to utilize Docker cache layers
COPY composer.json composer.lock ./

# Install dependencies without dev-dependencies, scripts, and autoloader first
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy the rest of the application files
COPY . .

# Copy the compiled Vite assets from Stage 1
COPY --from=assets-builder /app/public/build ./public/build

# Dump autoload and run optimizations
RUN composer dump-autoload --no-dev --optimize

# Set permissions for storage and bootstrap/cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy entrypoint script and set execution permissions
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 80
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
