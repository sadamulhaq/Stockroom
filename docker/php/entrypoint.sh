#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force

# Seed only if no users exist
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1)

if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "No users found — seeding database..."
    php artisan db:seed --force
else
    echo "Database already seeded ($USER_COUNT users found), skipping."
fi

echo "Caching config..."
php artisan config:cache
php artisan route:cache

echo "Starting PHP-FPM..."
exec php-fpm
