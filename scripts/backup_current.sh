#!/bin/bash
# backup_current.sh - Backup existing application before update

echo "🔄 Creating backup of current application..."

# Create backup directory with timestamp
BACKUP_DIR="/var/www/backups/sunsel-be-$(date +%Y%m%d-%H%M%S)"
sudo mkdir -p $BACKUP_DIR

# Backup current application
sudo cp -r /var/www/sunsel-be $BACKUP_DIR/

# Backup current database
sudo mysqldump -u dani -p@Dani159901 db_sunsel > $BACKUP_DIR/database_backup.sql

echo "✅ Backup created at: $BACKUP_DIR"
echo "📁 Backup contents:"
ls -la $BACKUP_DIR/
