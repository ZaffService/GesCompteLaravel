#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting API Banque Laravel Application..."

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
until pg_isready -h ${DB_HOST:-db} -p ${DB_PORT:-5432} -U ${DB_USERNAME:-banque_user}; do
  echo "Database is unavailable - sleeping"
  sleep 2
done
echo "✅ Database is ready!"

# Run database migrations
echo "📦 Running database migrations..."
php artisan migrate --force

# Run database seeders
echo "🌱 Running database seeders..."
php artisan db:seed --force

# Install Passport keys if not exists
if [ ! -f storage/oauth-public.key ]; then
    echo "🔐 Installing Passport keys..."
    php artisan passport:install --force
fi

# Generate application key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Generate Swagger documentation
echo "📚 Generating Swagger documentation..."
php artisan l5-swagger:generate

# Clear and cache config
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

echo "🎉 Application is ready! Starting services..."

# Start supervisord
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
