#!/bin/bash

# Laravel POS Development Environment Setup Script
# P1-DE-001: Development Environment Setup

set -e

echo "🚀 Setting up Laravel POS Development Environment..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Create environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    
    # Generate application key
    php artisan key:generate
    
    echo "✅ .env file created. Please update your database configuration."
fi

# Build and start Docker containers
echo "🐳 Building Docker containers..."
docker-compose build

echo "🔄 Starting Docker containers..."
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Install dependencies
echo "📦 Installing PHP dependencies..."
docker-compose exec app composer install

echo "📦 Installing Node dependencies..."
docker-compose exec app npm install

echo "🔨 Building frontend assets..."
docker-compose exec app npm run build

# Run database migrations
echo "🗄️ Running database migrations..."
docker-compose exec app php artisan migrate

# Seed database with sample data
echo "🌱 Seeding database with sample data..."
docker-compose exec app php artisan db:seed

# Cache configuration
echo "💾 Caching configuration..."
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache

# Set proper permissions
echo "🔐 Setting proper permissions..."
sudo chmod -R 775 storage bootstrap/cache

echo "✅ Development environment setup complete!"
echo ""
echo "🌐 Your application is now available at: http://localhost:8000"
echo "🐳 Docker containers are running in the background."
echo "📝 To stop containers: docker-compose down"
echo "📝 To view logs: docker-compose logs -f"
echo ""
echo "🧪 To run tests: docker-compose exec app php artisan test"
echo "🔍 To run static analysis: docker-compose exec app ./vendor/bin/phpstan analyse"
echo ""
echo "Happy coding! 🎉"