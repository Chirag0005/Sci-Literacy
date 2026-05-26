#!/bin/sh

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
