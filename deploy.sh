#!/bin/bash
# Production Deployment Script
# Run this script on your production server after uploading files

echo "🚀 Starting Laravel Production Deployment..."

# Install dependencies (without dev packages)
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Cache configurations for better performance
echo "⚡ Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "🔐 Setting file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# If running as root, set ownership to web server user
if [ "$EUID" -eq 0 ]; then
    chown -R www-data:www-data storage
    chown -R www-data:www-data bootstrap/cache
    chown -R www-data:www-data public/storage
fi

echo "✅ Deployment completed successfully!"
echo "🌐 Your application should now be accessible at: https://sundataselatan.com"

# Test API endpoints
echo "🧪 Testing API endpoints..."
echo "Testing /api/galleries..."
curl -s -o /dev/null -w "%{http_code}" https://sundataselatan.com/api/galleries
echo ""

echo "Testing /api/posts..."
curl -s -o /dev/null -w "%{http_code}" https://sundataselatan.com/api/posts
echo ""

echo "If you see 200 responses above, your API is working! 🎉"
