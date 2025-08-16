# Files to Upload to Production Server

## ✅ UPLOAD THESE FOLDERS/FILES:
- app/
- bootstrap/
- config/
- database/
- public/
- resources/
- routes/
- storage/
- vendor/ (atau run composer install di server)
- .htaccess (di root, jika ada)
- artisan
- composer.json
- composer.lock
- .env.production (rename ke .env setelah upload)

## ❌ JANGAN UPLOAD:
- .env (yang lama)
- node_modules/
- .git/
- .gitignore
- .env.example
- README.md
- tests/ (optional)
- phpunit.xml (optional)

## 📦 CARA COMPRESS:
1. Select semua folder/file yang perlu di-upload
2. Compress ke format .zip
3. Upload zip file ke server
4. Extract di server
