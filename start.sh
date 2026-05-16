#!/bin/bash

# Create SQLite database just in case standard auth mechanisms check for it
touch /var/www/html/database/database.sqlite
chown www-data:www-data /var/www/html/database/database.sqlite

# Cache config and routes for production performance
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in foreground
apache2-foreground
