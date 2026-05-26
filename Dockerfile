# Stage 1: Build front-end assets
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Serve the Laravel application
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql gd bcmath mbstring xml

# Copy Composer from latest image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy dependency definition files and install dependencies
COPY composer*.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy project files
COPY . .
COPY --from=assets-builder /app/public/build ./public/build

# Run post-autoload dump
RUN composer run post-autoload-dump

# Copy custom Nginx, Supervisor, and entrypoint files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Make entrypoint script executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Configure directory permissions for storage
RUN mkdir -p storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
