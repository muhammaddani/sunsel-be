#!/bin/bash
# server_deploy.sh - Run this script ON THE VPS SERVER

APP_PATH="/var/www/sunsel-be"
BACKUP_DIR="/var/www/backups/sunsel-be-$(date +%Y%m%d-%H%M%S)"

echo "🚀 Starting server deployment..."

# 1. Create backup of current application
echo "💾 Creating backup..."
sudo mkdir -p /var/www/backups
sudo cp -r $APP_PATH $BACKUP_DIR
echo "✅ Backup created at: $BACKUP_DIR"

# 2. Stop web server (if using Apache)
echo "⏹️ Stopping web server..."
sudo systemctl stop apache2 || sudo systemctl stop nginx

# 3. Extract new application files
echo "📦 Extracting new application..."
cd $APP_PATH
sudo tar -xzf /tmp/sunsel-be-deploy.tar.gz --overwrite

# 4. Update environment file
echo "🔧 Updating environment..."
sudo cp /tmp/.env.production $APP_PATH/.env

# 5. Set proper permissions
echo "🔐 Setting permissions..."
sudo chown -R www-data:www-data $APP_PATH
sudo chmod -R 755 $APP_PATH/storage
sudo chmod -R 755 $APP_PATH/bootstrap/cache

# 6. Install/Update Composer dependencies
echo "📦 Installing Composer dependencies..."
cd $APP_PATH
sudo -u www-data composer install --optimize-autoloader --no-dev

# 7. Clear caches
echo "🧹 Clearing caches..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

# 8. Run migrations
echo "🗄️ Running migrations..."
sudo -u www-data php artisan migrate --force

# 9. Create storage link (if not exists)
echo "🔗 Creating storage link..."
sudo -u www-data php artisan storage:link

# 10. Cache configurations for performance
echo "⚡ Caching configurations..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# 11. Start web server
echo "▶️ Starting web server..."
sudo systemctl start apache2 || sudo systemctl start nginx

# 12. Test API endpoints
echo "🧪 Testing API endpoints..."
sleep 3

echo "Testing /api/galleries..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://sundataselatan.com/api/galleries)
echo "Response code: $HTTP_CODE"

echo "Testing /api/posts..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://sundataselatan.com/api/posts)
echo "Response code: $HTTP_CODE"

# Clean up temporary files
sudo rm /tmp/sunsel-be-deploy.tar.gz
sudo rm /tmp/.env.production

echo ""
echo "🎉 Deployment completed!"
echo "🌐 Your application should be accessible at: https://sundataselatan.com"
echo "💾 Backup available at: $BACKUP_DIR"

if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ API endpoints are responding correctly!"
else
    echo "⚠️ API endpoints may have issues. Check logs:"
    echo "   sudo tail -f /var/log/apache2/error.log"
    echo "   sudo tail -f $APP_PATH/storage/logs/laravel.log"
fi
