# Laravel Production Deployment Checklist

## ✅ YANG SUDAH SIAP

### 1. Struktur Aplikasi
- ✅ Routes API sudah benar dan berfungsi (`/api/galleries`, `/api/posts`, `/api/pages`)
- ✅ Controllers lengkap dan berfungsi
- ✅ Models dan relationships sudah tepat
- ✅ Database migrations tersedia
- ✅ Storage link sudah dikonfigurasi
- ✅ File upload directories sudah ada

### 2. Security
- ✅ Laravel Sanctum untuk authentication
- ✅ CSRF protection
- ✅ Input validation di controllers
- ✅ File upload validation (size, type)

### 3. File Struktur
- ✅ .htaccess file sudah benar
- ✅ Public directory structure
- ✅ Storage directories

## ⚠️ YANG PERLU DIPERBAIKI UNTUK PRODUCTION

### 1. Environment Configuration
- ❌ `.env` masih dalam mode development
- ❌ `APP_DEBUG=true` (harus `false` di production)
- ❌ `APP_ENV=local` (harus `production`)
- ❌ Database credentials masih local

### 2. CORS Configuration
- ✅ SUDAH DIPERBAIKI: Added production domain ke CORS

### 3. Session & Cookie Security
- ❌ `SESSION_SECURE_COOKIE` tidak di-set untuk HTTPS
- ❌ `SESSION_DOMAIN` tidak dikonfigurasi

### 4. Logging
- ❌ `LOG_LEVEL=debug` (sebaiknya `error` di production)

## 🚀 LANGKAH DEPLOYMENT

### 1. File yang Harus Di-upload ke Server:
```
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/ (dengan permissions yang benar)
vendor/ (atau run composer install di server)
.env (gunakan .env.production template)
composer.json
composer.lock
artisan
```

### 2. Commands yang Harus Dijalankan di Server:
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Generate application key (jika belum ada)
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Cache configurations for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Web Server Configuration

#### Apache (.htaccess sudah benar)
- ✅ Rewrite rules sudah ada
- ✅ Authorization headers handled

#### Nginx (jika menggunakan Nginx):
```nginx
server {
    listen 80;
    listen 443 ssl;
    server_name sundataselatan.com www.sundataselatan.com;
    root /path/to/your/app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🔧 FIXES YANG SUDAH DITERAPKAN

1. **CORS Configuration**: Menambahkan domain production
2. **Production Environment Template**: Dibuat `.env.production`

## 📋 CHECKLIST SEBELUM GO-LIVE

- [ ] Update `.env` dengan credentials production
- [ ] Test database connection di server
- [ ] Test file uploads
- [ ] Test API endpoints
- [ ] Setup SSL certificate
- [ ] Configure email settings
- [ ] Setup backup strategy
- [ ] Monitor logs

## 🚨 URGENT FIXES NEEDED

1. **Environment**: Ganti `.env` dengan settingan production
2. **Database**: Setup database di hosting provider
3. **HTTPS**: Pastikan SSL certificate aktif
4. **File Permissions**: Set permissions yang benar di server
