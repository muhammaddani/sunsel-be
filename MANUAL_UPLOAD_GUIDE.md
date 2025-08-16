# CARA MANUAL UPLOAD VIA SCP/SFTP

## LANGKAH 1: Compress Files (di Windows)
# Compress semua folder kecuali yang excluded
# Bisa pakai WinRAR, 7zip, atau command line

## LANGKAH 2: Upload via SCP (dari Windows PowerShell/CMD)
# Install OpenSSH Client jika belum ada

# Upload compressed file
scp sunsel-be.zip root@your_server_ip:/tmp/

# Upload environment file
scp .env.production root@your_server_ip:/tmp/

## LANGKAH 3: Extract dan Deploy di Server
# SSH ke server
ssh root@your_server_ip

# Backup existing
cp -r /var/www/sunsel-be /var/www/sunsel-be-backup-$(date +%Y%m%d)

# Extract new files
cd /var/www/sunsel-be
unzip -o /tmp/sunsel-be.zip

# Copy environment
cp /tmp/.env.production .env

# Set permissions
chown -R www-data:www-data /var/www/sunsel-be
chmod -R 755 storage bootstrap/cache

# Run Laravel commands
php artisan config:clear
php artisan cache:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache

# Restart web server
systemctl restart apache2
# atau nginx
systemctl restart nginx
