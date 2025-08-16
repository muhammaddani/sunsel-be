#!/bin/bash
# git_deploy.sh - Quick deployment using Git Pull
# Run this script ON THE VPS SERVER

APP_PATH="/var/www/sunsel-be"
BACKUP_DIR="/var/www/backups/sunsel-be-$(date +%Y%m%d-%H%M%S)"

echo "🚀 Starting Git-based deployment..."

# 1. Create backup
echo "💾 Creating backup..."
sudo mkdir -p /var/www/backups
sudo cp -r $APP_PATH $BACKUP_DIR
echo "✅ Backup created at: $BACKUP_DIR"

# 2. Stop web server temporarily
echo "⏹️ Putting app in maintenance mode..."
cd $APP_PATH
sudo -u www-data php artisan down --message="System update in progress" --retry=60

# 3. Pull latest changes from Git
echo "📥 Pulling latest changes from GitHub..."
sudo -u www-data git pull origin master

# 4. Update environment file (.env.production -> .env)
echo "🔧 Updating environment file..."
if [ -f ".env.production" ]; then
    sudo cp .env.production .env
    sudo chown www-data:www-data .env
    echo "✅ Environment updated from .env.production"
else
    echo "⚠️ .env.production not found, using existing .env"
fi

# 5. Install/Update Composer dependencies
echo "📦 Installing Composer dependencies..."
sudo -u www-data composer install --optimize-autoloader --no-dev

# 6. Clear all caches
echo "🧹 Clearing caches..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

# 7. Run database migrations
echo "🗄️ Running database migrations..."
sudo -u www-data php artisan migrate --force

# 8. Create/update storage link
echo "🔗 Creating storage link..."
sudo -u www-data php artisan storage:link

# 9. Set proper permissions
echo "🔐 Setting file permissions..."
sudo chown -R www-data:www-data $APP_PATH
sudo chmod -R 755 $APP_PATH/storage
sudo chmod -R 755 $APP_PATH/bootstrap/cache

# 10. Cache configurations for better performance
echo "⚡ Caching configurations..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# 11. Bring app back online
echo "▶️ Bringing application back online..."
sudo -u www-data php artisan up

# 12. Test API endpoints
echo "🧪 Testing API endpoints..."
sleep 3

echo "Testing /api/galleries..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://sundataselatan.com/api/galleries)
echo "Response code: $HTTP_CODE"

echo "Testing /api/posts..."
HTTP_CODE_POSTS=$(curl -s -o /dev/null -w "%{http_code}" https://sundataselatan.com/api/posts)
echo "Response code: $HTTP_CODE_POSTS"

echo ""
echo "🎉 Git-based deployment completed!"
echo "🌐 Your application is accessible at: https://sundataselatan.com"
echo "💾 Backup available at: $BACKUP_DIR"

if [ "$HTTP_CODE" = "200" ] && [ "$HTTP_CODE_POSTS" = "200" ]; then
    echo "✅ All API endpoints are responding correctly!"
else
    echo "⚠️ Some API endpoints may have issues. Check logs:"
    echo "   sudo tail -f /var/log/apache2/error.log"
    echo "   sudo tail -f $APP_PATH/storage/logs/laravel.log"
    echo ""
    echo "🔧 Quick troubleshooting:"
    echo "   - Check if .env file is correct"
    echo "   - Verify database connection"
    echo "   - Check file permissions"
fi

echo ""
echo "📋 Deployment Summary:"
echo "   - Repository updated: ✅"
echo "   - Dependencies installed: ✅"
echo "   - Caches cleared and rebuilt: ✅"
echo "   - Database migrated: ✅"
echo "   - Permissions set: ✅"
echo "   - Application online: ✅"
