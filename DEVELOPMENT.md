# Laravel POS Development Environment Setup

This document describes the development environment setup for the Laravel POS 2026 upgrade project.

## Phase 1 Foundation Tasks

### P1-DE-001: Development Environment Setup ✅

The development environment has been containerized using Docker and Docker Compose for consistency across all development machines.

#### Quick Start

1. **Prerequisites**
   ```bash
   # Ensure Docker and Docker Compose are installed
   docker --version
   docker-compose --version
   ```

2. **Setup Development Environment**
   ```bash
   # Run the automated setup script
   ./setup-dev.sh
   ```

3. **Access the Application**
   - Frontend: http://localhost:8000
   - PHPMyAdmin (if needed): http://localhost:8080

#### Docker Services

The development environment includes:

- **app**: PHP 8.2-FPM with all required extensions
- **mysql**: MySQL 8.0 database server
- **redis**: Redis 7 for caching and queues
- **nginx**: Web server with optimized configuration
- **queue**: Dedicated queue worker
- **npm**: Node.js for frontend asset compilation

#### Manual Docker Commands

```bash
# Build and start all containers
docker-compose up -d --build

# View logs
docker-compose logs -f

# Execute commands in the app container
docker-compose exec app php artisan migrate
docker-compose exec app php artisan test

# Stop containers
docker-compose down
```

### P1-TS-001: Testing Suite Implementation ✅

A comprehensive testing suite has been implemented with the following components:

#### Test Types

- **Unit Tests**: Model and service layer testing
- **Feature Tests**: HTTP endpoint and integration testing
- **Security Tests**: Security vulnerability and protection testing
- **Browser Tests**: UI interaction testing (Dusk)

#### Running Tests

```bash
# Run the complete testing suite
./test-runner.sh

# Or run individual test types
docker-compose exec app php artisan test --testsuite=Unit
docker-compose exec app php artisan test --testsuite=Feature
```

#### Coverage Requirements

- Minimum code coverage: 80%
- All tests must pass before deployment
- Static analysis must pass without errors

#### Test Configuration

- Database: SQLite in-memory for speed
- Coverage: Xdebug with XML reports
- Reports: Generated in `storage/logs/`

### P1-SA-001: Security Assessment ✅

Automated security assessment and monitoring has been implemented.

#### Security Features

- **Dependency Scanning**: Automatic vulnerability detection
- **Code Analysis**: Static analysis for security patterns
- **Configuration Audit**: Environment and permissions checking
- **Security Tests**: Built-in protection verification

#### Running Security Audit

```bash
# Run comprehensive security assessment
./security-audit.sh
```

#### Security Checklist

- [ ] Dependencies are up to date
- [ ] No known vulnerabilities
- [ ] File permissions are secure
- [ ] Debug mode is disabled in production
- [ ] Environment variables are properly configured
- [ ] CSRF protection is enabled
- [ ] Session handling is secure

## Development Workflow

### 1. Environment Setup

```bash
# Clone the repository
git clone <repository-url>
cd laravel-pos

# Run setup script
./setup-dev.sh

# Create your feature branch
git checkout -b feature/your-feature-name
```

### 2. Development Process

```bash
# Make your changes...

# Run tests frequently
./test-runner.sh

# Run security checks
./security-audit.sh

# Commit changes
git add .
git commit -m "feat: add new feature"
```

### 3. Quality Assurance

Before creating a pull request:

1. **Testing**: All tests must pass with ≥80% coverage
2. **Static Analysis**: PHPStan must pass without errors
3. **Security**: No critical vulnerabilities
4. **Code Style**: Follow Laravel coding standards

### 4. Deployment Ready

When your changes are ready:

1. **Push Changes**
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Create Pull Request**
   - Automated CI/CD pipeline will run
   - All checks must pass
   - Code review required

3. **Merge and Deploy**
   - Auto-deployment to staging on merge
   - Manual promotion to production

## Configuration

### Environment Variables

Key environment variables to configure in `.env`:

```bash
# Application
APP_NAME="Laravel POS"
APP_ENV=local
APP_DEBUG=false
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_pos
DB_USERNAME=laravel
DB_PASSWORD=secret

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### Performance Optimization

- **OPcache**: Enabled in PHP configuration
- **Redis**: Used for caching and queues
- **Nginx**: Optimized for Laravel applications
- **Database**: MySQL 8 with performance tuning

## Troubleshooting

### Common Issues

1. **Port Conflicts**
   ```bash
   # Check what's using port 8000
   lsof -i :8000
   # Or change port in docker-compose.yml
   ```

2. **Permission Issues**
   ```bash
   # Fix storage permissions
   sudo chmod -R 775 storage bootstrap/cache
   ```

3. **Dependency Issues**
   ```bash
   # Reinstall dependencies
   docker-compose exec app composer install
   docker-compose exec app npm install
   ```

4. **Database Connection**
   ```bash
   # Reset database
   docker-compose exec app php artisan migrate:fresh --seed
   ```

### Getting Help

- Check logs: `docker-compose logs -f`
- Run health check: `./security-audit.sh`
- Verify setup: `./test-runner.sh`

## Next Steps

After Phase 1 completion:

1. **Phase 2**: Backend Development
2. **Phase 3**: Frontend Development  
3. **Phase 4**: Integration & Testing
4. **Phase 5**: Deployment & Documentation

## Contact

For development environment issues:
- DevOps Team: devops@company.com
- Project Lead: lead@company.com