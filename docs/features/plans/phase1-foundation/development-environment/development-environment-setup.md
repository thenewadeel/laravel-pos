# Task: Development Environment Setup

**Task ID**: P1-DE-001
**Phase**: Phase 1 - Foundation (Weeks 1-2)
**Priority**: Critical
**Assigned Agent**: DevOps Agent
**Estimated Hours**: 40 hours
**Dependencies**: None

## Description

Set up comprehensive development environment for Laravel POS 2026 upgrade project, including local development, staging, testing, and CI/CD pipeline infrastructure.

## Acceptance Criteria

- [ ] Local development environment configured
- [ ] Staging environment deployed
- [ ] CI/CD pipeline implemented
- [ ] Automated testing pipeline functional
- [ ] Code quality tools configured
- [ ] Development database setup
- [ ] Environment documentation completed
- [ ] Team onboarding materials prepared

## Deliverables

- [ ] Docker development environment
- [ ] Staging environment infrastructure
- [ ] CI/CD pipeline configuration
- [ ] Development database setup scripts
- [ ] Environment variable management
- [ ] Development documentation
- [ ] Team onboarding guide

## Implementation Tasks

### 1. Local Development Environment (12 hours)

#### Docker Configuration
```yaml
# docker-compose.yml structure
services:
  - Laravel Application (PHP 8.1+)
  - MySQL Database
  - Redis Cache
  - Nginx Web Server
  - Node.js Build Tools
  - Mailhog (email testing)
```

#### Development Tools Setup
- [ ] Laravel Sail configuration
- [ ] IDE configuration files
- [ ] Debugging tools setup
- [ ] Testing database configuration
- [ ] Asset compilation pipeline

### 2. Staging Environment (16 hours)

#### Infrastructure Setup
- [ ] Cloud server provisioning
- [ ] Database server configuration
- [ ] Web server setup
- [ ] SSL certificate installation
- [ ] Backup system configuration

#### Application Deployment
- [ ] Application deployment
- [ ] Database migration
- [ ] Environment configuration
- [ ] Performance optimization
- [ ] Monitoring setup

### 3. CI/CD Pipeline (12 hours)

#### Continuous Integration
```yaml
# .github/workflows/ci.yml
stages:
  - Code Quality Checks
  - Automated Testing
  - Security Scanning
  - Build Assets
  - Deploy to Staging
```

#### Code Quality Tools
- [ ] PHP_CodeSniffer configuration
- [ ] PHPStan static analysis
- [ ] ESLint for JavaScript
- [ ] StyleCI integration
- [ ] Automated code formatting

## Technical Specifications

### Development Environment Requirements

#### Hardware Requirements
- CPU: 4+ cores
- RAM: 16GB+ recommended
- Storage: 50GB+ SSD
- Network: Stable internet connection

#### Software Requirements
- Docker Desktop
- Git 2.30+
- PHP 8.1+
- Composer 2.0+
- Node.js 18+
- VS Code or equivalent IDE

### Docker Configuration

#### docker-compose.yml
```yaml
version: '3.8'
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.dev
    volumes:
      - ./:/var/www/html
    ports:
      - "8080:80"
    depends_on:
      - mysql
      - redis

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: laravel_pos
      MYSQL_ROOT_PASSWORD: secret
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - app
```

#### Dockerfile.dev
```dockerfile
FROM php:8.1-fpm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql bcmath zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist

# Set permissions
RUN chown -R www-data:www-data /var/www/html
```

### CI/CD Pipeline Configuration

#### GitHub Actions Workflow
```yaml
name: Laravel POS CI/CD

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: secret
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: bcmath, pdo_mysql, zip
        
    - name: Copy Environment File
      run: cp .env.example .env
      
    - name: Install Dependencies
      run: composer install --no-interaction --prefer-dist
      
    - name: Generate Key
      run: php artisan key:generate
      
    - name: Run Tests
      run: vendor/bin/phpunit
      
    - name: Run Security Audit
      run: composer audit
      
    - name: Deploy to Staging
      if: github.ref == 'refs/heads/develop'
      run: |
        # Deployment script here
```

## Environment Configuration

### Environment Variables Management

#### .env.example Structure
```env
# Application
APP_NAME="Laravel POS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_pos
DB_USERNAME=root
DB_PASSWORD=secret

# Cache
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null

# Development
DEBUGBAR_ENABLED=true
CLOCKWORK_ENABLED=true
```

### Database Setup Scripts

#### Database Seeder Configuration
```php
// database/seeders/DevelopmentSeeder.php
class DevelopmentSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            ShopSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
        ]);
        
        // Create test orders
        Order::factory()->count(50)->create();
    }
}
```

## Quality Assurance

### Code Quality Tools Configuration

#### PHP_CodeSniffer
```xml
<!-- phpcs.xml -->
<?xml version="1.0"?>
<ruleset name="Laravel POS">
    <description>Laravel POS coding standard</description>
    
    <file>app</file>
    
    <rule ref="PSR12"/>
    
    <rule ref="Generic.Files.LineLength">
        <properties>
            <property name="lineLimit" value="120"/>
            <property name="absoluteLineLimit" value="0"/>
        </properties>
    </rule>
</ruleset>
```

#### PHPStan Configuration
```neon
# phpstan.neon
parameters:
    level: 6
    paths:
        - app
    - database/factories
    - database/seeders
    
    checkMissingIterableValueType: false
    checkGenericClassInNonGenericObjectType: false
```

## Monitoring and Logging

### Development Monitoring
- [ ] Laravel Telescope installation
- [ ] Debug bar configuration
- [ ] Clockwork integration
- [ ] Error reporting setup
- [ ] Performance monitoring

### Logging Configuration
```php
// config/logging.php
'development' => [
    'driver' => 'single',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
],

'error_tracking' => [
    'driver' => 'sentry',
    'level' => 'error',
],
```

## Documentation Requirements

### Development Documentation
- [ ] Environment setup guide
- [ ] Database configuration guide
- [ ] Testing procedures documentation
- [ ] Deployment instructions
- [ ] Troubleshooting guide

### Team Onboarding
- [ ] New developer checklist
- [ ] Git workflow documentation
- [ ] Code review guidelines
- [ ] Development best practices
- [ ] Communication protocols

## Completion Report Location

**docs/features/complete/P1-DE-001-development-environment-setup.md**

## Success Metrics

- Environment setup time: <2 hours per developer
- CI/CD pipeline success rate: >95%
- Test execution time: <10 minutes
- Deployment to staging: <15 minutes
- Developer onboarding time: <4 hours

## Dependencies

None - This is a foundational task

## Next Steps

After completion, this setup enables:
- Comprehensive testing implementation (P1-TS-001)
- Security vulnerability assessment (P1-SA-001)
- Performance benchmarking (P1-PB-001)
- All subsequent development tasks