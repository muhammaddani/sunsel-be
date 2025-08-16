#!/bin/bash
# Script to clear Laravel caches on production

# Clear all Laravel caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear

# Regenerate optimizations
php artisan config:cache
php artisan route:cache
