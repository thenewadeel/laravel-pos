#!/bin/bash

# Laravel POS 2026 Upgrade - Test Runner (TDD Approach)
# This script follows TDD principles: Red-Green-Refactor cycle

set -e

echo "🧪 Laravel POS 2026 Upgrade - Test Runner (TDD)"
echo "================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker containers are running
check_containers() {
    if ! docker-compose ps | grep -q "Up"; then
        print_error "Docker containers are not running. Please run './scripts/setup-dev.sh' first."
        exit 1
    fi
}

# Step 1: Static Analysis (Red Phase)
run_static_analysis() {
    print_status "Running static analysis (Red Phase)..."
    
    if docker-compose exec app php -v > /dev/null 2>&1; then
        print_status "Running PHPStan..."
        if docker-compose exec app vendor/bin/phpstan analyse --memory-limit=2G; then
            print_success "Static analysis passed"
        else
            print_warning "Static analysis found issues. Review and fix before proceeding."
        fi
    else
        print_error "Cannot connect to app container. Check Docker setup."
        exit 1
    fi
}

# Step 2: Unit Tests (Green Phase)
run_unit_tests() {
    print_status "Running unit tests (Green Phase)..."
    
    if docker-compose exec app vendor/bin/phpunit tests/Unit --coverage-text --min=80; then
        print_success "Unit tests passed with required coverage"
    else
        print_error "Unit tests failed or coverage below 80%"
        echo ""
        echo "TDD Tip: Write failing tests first (RED), then make them pass (GREEN)"
        exit 1
    fi
}

# Step 3: Feature Tests
run_feature_tests() {
    print_status "Running feature tests..."
    
    if docker-compose exec app vendor/bin/phpunit tests/Feature --coverage-text --min=80; then
        print_success "Feature tests passed with required coverage"
    else
        print_error "Feature tests failed or coverage below 80%"
        exit 1
    fi
}

# Step 4: Integration Tests
run_integration_tests() {
    print_status "Running integration tests..."
    
    if docker-compose exec app vendor/bin/phpunit tests/Integration --coverage-text --min=80; then
        print_success "Integration tests passed with required coverage"
    else
        print_error "Integration tests failed or coverage below 80%"
        exit 1
    fi
}

# Step 5: End-to-End Tests (Browser Tests)
run_e2e_tests() {
    print_status "Running end-to-end tests..."
    
    if docker-compose exec app php artisan dusk; then
        print_success "E2E tests passed"
    else
        print_error "E2E tests failed"
        exit 1
    fi
}

# Step 6: Refactoring Check (Refactor Phase)
run_refactoring_check() {
    print_status "Running refactoring checks (Refactor Phase)..."
    
    # Check code style
    if docker-compose exec app vendor/bin/phpcs --standard=PSR12 app/; then
        print_success "Code style is PSR-12 compliant"
    else
        print_warning "Code style issues found. Consider refactoring."
    fi
    
    # Check for code smells
    if docker-compose exec app vendor/bin/phpcpd app/; then
        print_success "No code duplication detected"
    else
        print_warning "Code duplication found. Consider refactoring."
    fi
}

# Step 7: Performance Tests
run_performance_tests() {
    print_status "Running performance tests..."
    
    # Test critical operations
    echo "Testing order creation performance..."
    if docker-compose exec app php artisan tinker --execute="
        \$start = microtime(true);
        factory(App\\Models\\Order::class)->create();
        \$duration = microtime(true) - \$start;
        if (\$duration > 0.5) {
            echo 'PERFORMANCE WARNING: Order creation took ' . \$duration . ' seconds (target: <0.5s)';
        } else {
            echo 'PERFORMANCE OK: Order creation took ' . \$duration . ' seconds';
        }
    "; then
        print_success "Performance tests completed"
    else
        print_warning "Performance issues detected"
    fi
}

# Generate coverage report
generate_coverage_report() {
    print_status "Generating coverage report..."
    
    docker-compose exec app vendor/bin/phpunit --coverage-html storage/coverage
    
    if [ -f "storage/coverage/index.html" ]; then
        print_success "Coverage report generated: storage/coverage/index.html"
    fi
}

# Main execution
main() {
    print_status "Starting TDD test cycle..."
    echo ""
    
    check_containers
    echo ""
    
    run_static_analysis
    echo ""
    
    run_unit_tests
    echo ""
    
    run_feature_tests
    echo ""
    
    run_integration_tests
    echo ""
    
    if [ "$1" = "--full" ]; then
        run_e2e_tests
        echo ""
    fi
    
    run_refactoring_check
    echo ""
    
    run_performance_tests
    echo ""
    
    generate_coverage_report
    echo ""
    
    print_success "🎉 All tests completed successfully!"
    echo ""
    echo "📊 Coverage Report: storage/coverage/index.html"
    echo "📋 Next TDD Cycle:"
    echo "   1. Write failing test (RED)"
    echo "   2. Write minimum code to pass (GREEN)" 
    echo "   3. Refactor while tests pass (REFACTOR)"
    echo ""
    echo "Happy TDD coding! 🚀"
}

# Show usage
if [ "$1" = "--help" ]; then
    echo "Usage: ./scripts/test-runner.sh [--full]"
    echo ""
    echo "Options:"
    echo "  --full    Include end-to-end (browser) tests"
    echo "  --help    Show this help message"
    echo ""
    echo "TDD Process:"
    echo "  1. RED: Write a failing test"
    echo "  2. GREEN: Write code to make test pass"
    echo "  3. REFACTOR: Improve code while tests pass"
    echo ""
    exit 0
fi

# Run main function
main "$@"