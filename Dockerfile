FROM php:8.4-cli

# Install system dependencies and PHP extensions in a single layer to avoid filesystem caching bugs
RUN apt-get update && apt-get install -y \
    git \
    curl \
    ca-certificates \
    openssl \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    pkg-config \
    && docker-php-ext-install bcmath zip pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Set build-time environment variables to prevent Laravel bootstrap exceptions
ENV APP_KEY=base64:jAffLs/XwXF2fSDBrXKaDYXts+HTGeL7Np9aKqD5ido=
ENV APP_ENV=local

# Copy application directory contents first so composer autoload scripts can run
COPY . /var/www

# Clear bootstrap cache copied from host to prevent package discovery issues
RUN rm -f bootstrap/cache/*.php

# Install dependencies
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 8000
EXPOSE 8000

# Set entrypoint & command default
ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
