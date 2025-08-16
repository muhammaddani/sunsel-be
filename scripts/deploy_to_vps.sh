#!/bin/bash
# deploy_to_vps.sh - Deploy updated Laravel app to VPS

# VPS Configuration
VPS_USER="root"           # Atau username VPS Anda
VPS_HOST="your_server_ip" # Ganti dengan IP server VPS Anda
VPS_PATH="/var/www/sunsel-be"

echo "🚀 Starting deployment to VPS..."

# 1. Create deployment package (exclude unnecessary files)
echo "📦 Creating deployment package..."
tar --exclude='.env' \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='.gitignore' \
    --exclude='README.md' \
    --exclude='tests' \
    --exclude='phpunit.xml' \
    --exclude='*.log' \
    -czf sunsel-be-deploy.tar.gz .

echo "✅ Deployment package created: sunsel-be-deploy.tar.gz"

# 2. Upload to VPS
echo "⬆️ Uploading to VPS..."
scp sunsel-be-deploy.tar.gz $VPS_USER@$VPS_HOST:/tmp/

# 3. Upload .env.production file
echo "⬆️ Uploading environment file..."
scp .env.production $VPS_USER@$VPS_HOST:/tmp/

echo "✅ Files uploaded successfully!"
echo "📋 Next steps:"
echo "1. SSH to your VPS: ssh $VPS_USER@$VPS_HOST"
echo "2. Run the deployment script on server"

# Clean up local deployment package
rm sunsel-be-deploy.tar.gz
