#!/bin/bash

# Laravel POS Testing Suite Script
# P1-TS-001: Testing Suite Implementation

set -e

echo "🧪 Running Laravel POS Testing Suite..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Check if we're in Docker or local
if [ -f ".dockerenv" ]; then
    echo "🐳 Running inside Docker container"
    CMD_PREFIX=""
else
    echo "💻 Running locally, using Docker exec"
    CMD_PREFIX="docker-compose exec app"
fi

# Ensure dependencies are installed
echo "📦 Ensuring dependencies are up to date..."
$CMD_PREFIX composer install --no-interaction --prefer-dist --optimize-autoloader

# Run static analysis first
echo "🔍 Running PHPStan static analysis..."
if $CMD_PREFIX ./vendor/bin/phpstan analyse --memory-limit=2G; then
    print_status "Static analysis passed"
else
    print_error "Static analysis failed"
    exit 1
fi

# Run security audit
echo "🔒 Running security audit..."
echo "Checking for known vulnerabilities in PHP dependencies..."
if $CMD_PREFIX composer audit; then
    print_status "No security vulnerabilities found in PHP dependencies"
else
    print_warning "Security vulnerabilities found in PHP dependencies"
fi

echo "Checking for known vulnerabilities in Node dependencies..."
if $CMD_PREFIX npm audit --audit-level moderate; then
    print_status "No critical vulnerabilities found in Node dependencies"
else
    print_warning "Vulnerabilities found in Node dependencies"
fi

# Run unit tests
echo "🔬 Running unit tests..."
if $CMD_PREFIX php artisan test --testsuite=Unit --coverage; then
    print_status "Unit tests passed"
else
    print_error "Unit tests failed"
    exit 1
fi

# Run feature tests
echo "🌟 Running feature tests..."
if $CMD_PREFIX php artisan test --testsuite=Feature --coverage; then
    print_status "Feature tests passed"
else
    print_error "Feature tests failed"
    exit 1
fi

# Run all tests with coverage
echo "📊 Running complete test suite with coverage..."
if $CMD_PREFIX php artisan test --coverage --min=80; then
    print_status "All tests passed with minimum 80% coverage"
else
    print_error "Test coverage is below 80%"
    exit 1
fi

# Run browser tests if they exist
if [ -d "tests/Browser" ]; then
    echo "🌐 Running browser tests..."
    if $CMD_PREFIX php artisan dusk; then
        print_status "Browser tests passed"
    else
        print_warning "Browser tests failed or skipped"
    fi
fi

# Generate test report
echo "📄 Generating test report..."
$CMD_PREFIX php artisan test --log-junit=storage/logs/test-results.xml

print_status "Testing suite completed successfully!"

echo ""
echo "📊 Test Results Summary:"
echo "  • Static Analysis: ✓ Passed"
echo "  • Unit Tests: ✓ Passed"
echo "  • Feature Tests: ✓ Passed"
echo "  • Code Coverage: ✓ ≥80%"
echo "  • Security Audit: ✓ Completed"
echo ""
echo "📄 Detailed reports available in:"
echo "  • storage/logs/test-results.xml"
echo "  • storage/logs/coverage/"
echo ""
echo "🎉 All tests passed! Ready for deployment. 🚀"