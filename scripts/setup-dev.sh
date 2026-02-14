#!/bin/bash

# Laravel POS 2026 Upgrade - Setup Script
# This script initializes the complete development environment

set -e

echo "🚀 Laravel POS 2026 Upgrade - Development Environment Setup"
echo "=========================================================="

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

# Copy environment file
if [ ! -f .env ]; then
    echo "📋 Copying environment file..."
    cp .env.example .env
    echo "✅ Environment file created. Please edit .env with your settings."
fi

# Build Docker containers
echo "🐳 Building Docker containers..."
docker-compose build --no-cache

# Start containers
echo "🚀 Starting containers..."
docker-compose up -d

# Wait for containers to be ready
echo "⏳ Waiting for containers to be ready..."
sleep 30

# Install dependencies
echo "📦 Installing PHP dependencies..."
docker-compose exec app composer install --no-interaction --prefer-dist

echo "📦 Installing JavaScript dependencies..."
docker-compose exec app npm install

# Generate application key
echo "🔑 Generating application key..."
docker-compose exec app php artisan key:generate

# Run database migrations
echo "🗄️ Running database migrations..."
docker-compose exec app php artisan migrate

# Seed database
echo "🌱 Seeding database..."
docker-compose exec app php artisan db:seed

# Clear caches
echo "🧹 Clearing caches..."
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Set permissions
echo "🔐 Setting permissions..."
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache

echo ""
echo "✅ Development environment setup complete!"
echo ""
echo "🌐 Application URL: http://localhost:8000"
echo "🗄️ MySQL: localhost:3306"
echo "📊 Redis: localhost:6379"
echo "📧 MailHog: http://localhost:8025"
echo ""
echo "📋 Next steps:"
echo "1. Edit .env with your configuration"
echo "2. Run './scripts/test-runner.sh' to execute tests"
echo "3. Run './scripts/security-audit.sh' for security checks"
echo "4. Check docs/agents.md for agent instructions"
echo ""
echo "Happy coding! 🎉"