#!/bin/sh
set -e

# Cache configuration, routes, and views if environment is production
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration and routes for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Disable conflicting Apache MPM modules at runtime to prevent start failure
if [ -f /usr/sbin/a2dismod ]; then
    echo "Ensuring only prefork MPM is loaded..."
    a2dismod mpm_event mpm_worker || true
    a2enmod mpm_prefork || true
fi

# Run database migrations if requested
if [ "$DB_AUTO_MIGRATE" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Execute the container's main process
exec "$@"
