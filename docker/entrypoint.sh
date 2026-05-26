#!/bin/sh

# Create or overwrite the .env file dynamically from container environment variables
ENV_FILE="/var/www/html/.env"
echo "Creating .env configuration file from system environment..."
echo "APP_NAME=Laravel" > "$ENV_FILE"
echo "APP_ENV=production" >> "$ENV_FILE"
echo "APP_KEY=$APP_KEY" >> "$ENV_FILE"
echo "APP_DEBUG=false" >> "$ENV_FILE"
echo "APP_URL=$APP_URL" >> "$ENV_FILE"
echo "DB_CONNECTION=sqlite" >> "$ENV_FILE"
echo "SESSION_DRIVER=file" >> "$ENV_FILE"
echo "MONGODB_URI=$MONGODB_URI" >> "$ENV_FILE"
echo "GEMINI_API_KEY=$GEMINI_API_KEY" >> "$ENV_FILE"
chown www-data:www-data "$ENV_FILE"

# Ensure SQLite database directory and file exist with correct permissions
mkdir -p /var/www/html/database
DB_FILE="/var/www/html/database/database.sqlite"
if [ ! -f "$DB_FILE" ]; then
    echo "Creating SQLite database file at $DB_FILE..."
    touch "$DB_FILE"
fi
chown -R www-data:www-data /var/www/html/database

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database
echo "Seeding database..."
php artisan db:seed --force

# Cache configuration, routes, and views for optimal performance
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Start supervisor
echo "Starting Nginx and PHP-FPM via Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
