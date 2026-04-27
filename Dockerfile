FROM php:8.1-apache

# ✅ Install required tools + PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html/

# Create .env file from example if it doesn't exist
RUN if [ ! -f /var/www/html/.env ] && [ -f /var/www/html/.env.example ]; then \
        cp /var/www/html/.env.example /var/www/html/.env; \
    fi

# Install PHP dependencies (safe version)
RUN cd /var/www/html && if [ -f "composer.json" ]; then composer install --no-interaction; fi

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/vendor 2>/dev/null || true

# Expose Apache port
EXPOSE 80