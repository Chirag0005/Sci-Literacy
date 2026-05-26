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

# Install system dependencies (including nodejs and npm for the MongoDB bridge CLI)
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql gd bcmath mbstring xml

# Copy Composer from latest image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy dependency definition files and install dependencies
COPY composer*.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy project files and compiled node_modules/assets
COPY . .
COPY --from=assets-builder /app/node_modules ./node_modules
COPY --from=assets-builder /app/public/build ./public/build

# Run post-autoload dump
RUN composer run post-autoload-dump

# Copy custom Nginx, Supervisor, and entrypoint files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Normalize line endings to prevent Windows CRLF syntax errors in Alpine
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh /etc/supervisord.conf /etc/nginx/nginx.conf

# Make entrypoint script executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Configure directory permissions for storage
RUN mkdir -p storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache

# Allow PHP-FPM to read system environment variables (vital for cloud hosting env vars)
RUN echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
