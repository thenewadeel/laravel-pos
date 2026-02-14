#!/bin/bash

# Get the directory where this script is located
APP_DIR=$(cd "$(dirname "$0")" && pwd)
LOG_FILE="$APP_DIR/storage/logs/deploy.log"

cd $APP_DIR

# 1. Fetch from GitHub
git fetch origin main

LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse @{u})

if [ $LOCAL != $REMOTE ]; then
    echo "[$(date)] 🚀 New changes detected. Deploying..." >> $LOG_FILE

    # Pull latest code
    git pull origin main

    # Install dependencies
    composer install --no-dev --optimize-autoloader
    npm install && npm run build

    # Laravel Housekeeping
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Fix Permissions & SELinux
    sudo chown -R gg:nginx storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache
    sudo restorecon -Rv storage bootstrap/cache

    echo "[$(date)] ✅ Deployment successful." >> $LOG_FILE
else
    exit 0
fi
