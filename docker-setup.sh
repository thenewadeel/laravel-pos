#!/bin/bash
# Docker setup script for Laravel POS

echo "🐳 Setting up Laravel POS in Docker..."

# Install Composer
echo "📦 Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions
echo "🔧 Installing PHP extensions..."
apk add --no-cache \
    zlib-dev \
    libpng-dev \
    freetype-dev \
    jpeg-dev \
    libzip-dev \
    oniguruma-dev

docker-php-ext-install gd pdo_mysql mbstring zip

# Verify extensions
echo "✅ Verifying PHP extensions..."
php -m | grep -E "(gd|pdo_mysql|mbstring|zip)"

# Install dependencies
echo "📥 Installing Composer dependencies..."
cd /var/www
composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data /var/www

# Create .env if not exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Run tests
echo "🧪 Running tests..."
php artisan test

echo "✅ Setup complete!"