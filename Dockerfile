FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    mbstring \
    json

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY src/composer.json src/composer.lock* ./

# Install PHP dependencies
RUN composer install --no-scripts --no-dev

# Copy application code
COPY src .

# Run composer post-install scripts
RUN composer post-autoload-dump

# Create storage directories
RUN mkdir -p storage/logs

# Expose port
EXPOSE 8000

# Start application
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
