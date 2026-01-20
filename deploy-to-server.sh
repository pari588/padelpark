#!/bin/bash
#=============================================================================
# Sky Padel Deployment Script for pp.paritoshajmera.com
# Run this script on your server via SSH
#=============================================================================

# Configuration - UPDATE THESE VALUES
DB_HOST="localhost"
DB_USER="root"
DB_PASS=""                    # Set your MySQL password
DB_NAME="bombayengg"          # Database name
WEB_ROOT="/var/www/html"      # Or /home/username/public_html for cPanel
GITHUB_TOKEN=""               # Not needed for public repo

#=============================================================================
echo "=========================================="
echo "  Sky Padel Deployment Script"
echo "  Target: pp.paritoshajmera.com"
echo "=========================================="

# Step 1: Navigate to web root
echo ""
echo "[1/6] Navigating to web root..."
cd "$WEB_ROOT" || { echo "ERROR: Cannot access $WEB_ROOT"; exit 1; }

# Step 2: Clone the repository
echo ""
echo "[2/6] Cloning padelpark repository..."
if [ -d "padelpark" ]; then
    echo "  Directory exists, pulling latest changes..."
    cd padelpark
    git pull origin main
else
    git clone https://github.com/pari588/padelpark.git
    cd padelpark
fi

# Step 3: Create database if not exists
echo ""
echo "[3/6] Setting up database..."
if [ -z "$DB_PASS" ]; then
    mysql -h "$DB_HOST" -u "$DB_USER" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
else
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
fi

# Step 4: Import SQL backup
echo ""
echo "[4/6] Importing database backup..."
if [ -f "database_backup_20260120.sql" ]; then
    if [ -z "$DB_PASS" ]; then
        mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" < database_backup_20260120.sql
    else
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database_backup_20260120.sql
    fi
    echo "  Database imported successfully!"
else
    echo "  WARNING: SQL file not found!"
fi

# Step 5: Update configuration files
echo ""
echo "[5/6] Updating configuration..."

# Update xadmin database config
if [ -f "xadmin/core-admin/settings.inc.php" ]; then
    sed -i "s/define('DB_HOST', '.*');/define('DB_HOST', '$DB_HOST');/" xadmin/core-admin/settings.inc.php
    sed -i "s/define('DB_USER', '.*');/define('DB_USER', '$DB_USER');/" xadmin/core-admin/settings.inc.php
    sed -i "s/define('DB_PASS', '.*');/define('DB_PASS', '$DB_PASS');/" xadmin/core-admin/settings.inc.php
    sed -i "s/define('DB_NAME', '.*');/define('DB_NAME', '$DB_NAME');/" xadmin/core-admin/settings.inc.php
    echo "  Updated xadmin settings"
fi

# Update skypadel portal config
if [ -f "skypadel/core/config.php" ]; then
    sed -i "s/\$dbHost = '.*';/\$dbHost = '$DB_HOST';/" skypadel/core/config.php
    sed -i "s/\$dbUser = '.*';/\$dbUser = '$DB_USER';/" skypadel/core/config.php
    sed -i "s/\$dbPass = '.*';/\$dbPass = '$DB_PASS';/" skypadel/core/config.php
    sed -i "s/\$dbName = '.*';/\$dbName = '$DB_NAME';/" skypadel/core/config.php
    sed -i "s|\$baseUrl = '.*';|\$baseUrl = 'https://pp.paritoshajmera.com/skypadel';|" skypadel/core/config.php
    echo "  Updated skypadel config"
fi

# Step 6: Set permissions
echo ""
echo "[6/6] Setting file permissions..."
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 755 deploy-to-server.sh 2>/dev/null

echo ""
echo "=========================================="
echo "  Deployment Complete!"
echo "=========================================="
echo ""
echo "Access URLs:"
echo "  Admin Panel:    https://pp.paritoshajmera.com/padelpark/xadmin/"
echo "  Client Portal:  https://pp.paritoshajmera.com/padelpark/skypadel/"
echo ""
echo "Default Admin Login:"
echo "  Username: admin"
echo "  Password: (check your database or reset it)"
echo ""
