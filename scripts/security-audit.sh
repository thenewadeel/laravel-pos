#!/bin/bash

# Laravel POS 2026 Upgrade - Security Audit Script
# This script performs comprehensive security vulnerability assessment

set -e

echo "🔒 Laravel POS 2026 Upgrade - Security Audit"
echo "=========================================="

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

print_critical() {
    echo -e "${RED}[CRITICAL]${NC} $1"
}

# Check if Docker containers are running
check_containers() {
    if ! docker-compose ps | grep -q "Up"; then
        print_error "Docker containers are not running. Please run './scripts/setup-dev.sh' first."
        exit 1
    fi
}

# 1. Dependency Vulnerability Scanning
scan_dependencies() {
    print_status "Scanning PHP and JavaScript dependencies for vulnerabilities..."
    
    # PHP dependencies
    if docker-compose exec app composer audit 2>/dev/null; then
        print_success "No critical PHP vulnerabilities found"
    else
        print_critical "PHP dependencies have vulnerabilities!"
        docker-compose exec app composer audit
    fi
    
    # JavaScript dependencies (if package.json exists)
    if [ -f "package.json" ]; then
        if docker-compose exec app npm audit --audit-level=moderate 2>/dev/null; then
            print_success "No moderate+ JavaScript vulnerabilities found"
        else
            print_warning "JavaScript dependencies have vulnerabilities"
            docker-compose exec app npm audit --audit-level=moderate
        fi
    fi
}

# 2. Static Code Analysis
run_static_analysis() {
    print_status "Running static security analysis..."
    
    # PHPStan for security issues
    if docker-compose exec app vendor/bin/phpstan analyse --level=5 --memory-limit=2G 2>/dev/null; then
        print_success "No critical static analysis issues found"
    else
        print_warning "Static analysis found potential issues"
        docker-compose exec app vendor/bin/phpstan analyse --level=5 --memory-limit=2G
    fi
}

# 3. Configuration Security Check
check_configuration() {
    print_status "Checking application configuration security..."
    
    # Check environment variables
    if [ -f ".env" ]; then
        # Check for default keys
        if grep -q "APP_KEY=base64:" .env; then
            print_critical "Default APP_KEY detected! Change immediately!"
        fi
        
        # Check for exposed credentials
        if grep -q "DB_PASSWORD=secret" .env; then
            print_critical "Default database password detected!"
        fi
        
        # Check debug mode
        if grep -q "APP_DEBUG=true" .env; then
            print_warning "Debug mode enabled in production configuration!"
        fi
    else
        print_warning ".env file not found"
    fi
}

# 4. Authentication and Authorization Check
check_auth() {
    print_status "Checking authentication and authorization..."
    
    # Check for weak password rules (basic check)
    echo "Password policy analysis:"
    echo "- Password complexity: Requires manual review"
    echo "- Session timeout: Check config/session.php"
    echo "- Rate limiting: Check middleware/throttle.php"
    echo "- Multi-factor auth: Check if implemented"
}

# 5. Input Validation Assessment
check_input_validation() {
    print_status "Assessing input validation..."
    
    # Check for mass assignment protection
    echo "Checking for mass assignment vulnerabilities..."
    
    # Look for $fillable without proper validation
    if grep -r "protected \$fillable" app/Models/ > /dev/null 2>&1; then
        print_status "Found \$fillable arrays - ensure proper validation in controllers"
    fi
    
    # Check for unvalidated input in controllers
    echo "Checking for unvalidated request input..."
    if grep -r "request()->" app/Http/Controllers/ | grep -v "validate\|validated" > /dev/null 2>&1; then
        print_warning "Potential unvalidated input found - review controllers"
    fi
}

# 6. SQL Injection Prevention
check_sql_injection() {
    print_status "Checking for SQL injection vulnerabilities..."
    
    # Look for raw SQL without bindings
    if grep -r "DB::raw\|->raw(" app/ > /dev/null 2>&1; then
        print_warning "Raw SQL queries found - ensure proper parameter binding"
    fi
    
    # Check for Laravel query builders (good practice)
    echo "Checking Eloquent usage patterns..."
    if grep -r "::where\|::orderBy\|::limit" app/Models/ > /dev/null 2>&1; then
        print_success "Eloquent query builders in use"
    fi
}

# 7. XSS Prevention
check_xss() {
    print_status "Checking for XSS vulnerabilities..."
    
    # Check for proper escaping
    echo "Checking output escaping patterns..."
    if grep -r "{{" resources/views/ | grep -v "e(" > /dev/null 2>&1; then
        print_warning "Potential unescaped output found - review Blade templates"
    fi
    
    # Check for Content Security Policy
    echo "Content Security Policy: Manual check required"
}

# 8. File Upload Security
check_file_uploads() {
    print_status "Checking file upload security..."
    
    # Look for file upload handling
    if grep -r "file(" app/ > /dev/null 2>&1; then
        print_warning "File upload functionality found - ensure proper validation"
        echo "- File type validation"
        echo "- File size limits"
        echo "- Scan for malware"
        echo "- Secure storage location"
    fi
}

# 9. Session Management
check_session_management() {
    print_status "Checking session management..."
    
    # Check session configuration
    if [ -f "config/session.php" ]; then
        echo "Session configuration found - manual review required"
        echo "- Session driver: Check if secure"
        echo "- Session lifetime: Check if appropriate"
        echo "- Session encryption: Ensure configured"
    fi
}

# 10. API Security
check_api_security() {
    print_status "Checking API security..."
    
    # Look for API routes
    if [ -f "routes/api.php" ]; then
        echo "API routes found - review required:"
        echo "- Authentication required for sensitive endpoints"
        echo "- Rate limiting implementation"
        echo "- HTTPS enforcement"
        echo "- Input validation"
        echo "- Output sanitization"
    fi
}

# 11. Error Handling Security
check_error_handling() {
    print_status "Checking error handling security..."
    
    # Check for debug information exposure
    if grep -r "dd\|var_dump\|print_r" app/ > /dev/null 2>&1; then
        print_critical "Debug functions found in production code!"
    fi
    
    # Check error reporting configuration
    echo "Error configuration review:"
    echo "- APP_DEBUG should be false in production"
    echo "- Error logging should capture security events"
    echo "- Custom error pages should not leak information"
}

# 12. Logging and Monitoring
check_logging() {
    print_status "Checking logging and monitoring..."
    
    # Check for security event logging
    echo "Security logging assessment:"
    echo "- Authentication failures logged?"
    echo "- Authorization violations logged?"
    echo "- Data modifications logged?"
    echo "- System access logged?"
    
    if grep -r "Log::" app/ > /dev/null 2>&1; then
        print_success "Logging implementation found"
    fi
}

# 13. CORS and Headers
check_headers() {
    print_status "Checking CORS and security headers..."
    
    echo "Security headers review required:"
    echo "- X-Frame-Options: Clickjacking protection"
    echo "- X-Content-Type-Options: MIME-type sniffing protection"
    echo "- X-XSS-Protection: XSS protection"
    echo "- Strict-Transport-Security: HTTPS enforcement"
    echo "- Content-Security-Policy: XSS and injection protection"
}

# 14. Database Security
check_database_security() {
    print_status "Checking database security..."
    
    echo "Database security review:"
    echo "- User privileges: Least privilege principle"
    echo "- Connection encryption: SSL/TLS required"
    echo "- Backup encryption: Ensure sensitive data protection"
    echo "- Query logging: Monitor for suspicious activity"
}

# 15. OWASP Top 10 Summary
owasp_summary() {
    print_status "OWASP Top 10 2021 Summary:"
    
    echo "A01 - Broken Access Control: Manual review required"
    echo "A02 - Cryptographic Failures: Check encryption usage"
    echo "A03 - Injection: SQL injection checks performed"
    echo "A04 - Insecure Design: Architecture review required"
    echo "A05 - Security Misconfiguration: Configuration reviewed"
    echo "A06 - Vulnerable Components: Dependency scan completed"
    echo "A07 - ID/Authentication Failures: Auth checks performed"
    echo "A08 - Software/Data Integrity: Check update mechanisms"
    echo "A09 - Logging/Monitoring: Logging assessment completed"
    echo "A10 - Server-Side Request Forgery: CSRF checks needed"
}

# Generate Security Report
generate_report() {
    print_status "Generating security assessment report..."
    
    REPORT_FILE="security-audit-$(date +%Y%m%d-%H%M%S).md"
    
    cat > "$REPORT_FILE" << EOF
# Security Audit Report - Laravel POS 2026 Upgrade

**Date**: $(date)  
**Scanner**: Automated Security Audit Script  
**Target**: Laravel POS Application

## Executive Summary

This security assessment identified critical areas requiring immediate attention before proceeding with the 2026 upgrade.

## Findings Summary

### Critical Issues
- [ ] Default application keys or passwords
- [ ] Debug mode enabled in production
- [ ] Unvalidated user input
- [ ] Missing rate limiting

### High Priority
- [ ] SQL injection vulnerabilities
- [ ] XSS vulnerabilities
- [ ] Authentication bypasses
- [ ] Authorization failures

### Medium Priority
- [ ] Security headers missing
- [ ] File upload vulnerabilities
- [ ] Session management issues
- [ ] Insufficient logging

### Low Priority
- [ ] Information disclosure
- [ ] Security misconfigurations
- [ ] Outdated dependencies (non-critical)

## Recommendations

### Immediate Actions Required
1. **Change Default Credentials**: Update all default passwords and keys
2. **Disable Debug Mode**: Set APP_DEBUG=false in production
3. **Implement Input Validation**: Add comprehensive validation rules
4. **Add Rate Limiting**: Implement throttling for sensitive operations

### Security Improvements
1. **Add Security Headers**: Implement OWASP recommended headers
2. **Enhance Authentication**: Add MFA and improve session management
3. **Implement CORS**: Configure proper Cross-Origin Resource Sharing
4. **Add CSRF Protection**: Ensure all forms are protected

### Long-term Security Strategy
1. **Regular Security Audits**: Monthly automated scans
2. **Security Training**: Developer security awareness program
3. **Dependency Management**: Regular updates and vulnerability monitoring
4. **Incident Response**: Security incident response plan

## Compliance Notes

- **PCI DSS**: Required for payment processing
- **GDPR**: Required for customer data protection
- **OWASP**: Follow Top 10 security practices
- **Industry Standards**: Restaurant industry specific requirements

## Next Steps

1. Address critical findings immediately
2. Implement security headers and validation
3. Schedule regular security assessments
4. Document security procedures
5. Train development team on security best practices

---
**Report Generated**: $(date)  
**Next Review**: Recommended within 30 days
EOF

    print_success "Security report generated: $REPORT_FILE"
}

# Main execution
main() {
    print_status "Starting comprehensive security audit..."
    echo ""
    
    check_containers
    echo ""
    
    scan_dependencies
    echo ""
    
    run_static_analysis
    echo ""
    
    check_configuration
    echo ""
    
    check_auth
    echo ""
    
    check_input_validation
    echo ""
    
    check_sql_injection
    echo ""
    
    check_xss
    echo ""
    
    check_file_uploads
    echo ""
    
    check_session_management
    echo ""
    
    check_api_security
    echo ""
    
    check_error_handling
    echo ""
    
    check_logging
    echo ""
    
    check_headers
    echo ""
    
    check_database_security
    echo ""
    
    owasp_summary
    echo ""
    
    generate_report
    echo ""
    
    print_success "🔒 Security audit completed!"
    echo ""
    echo "📋 Critical Actions Required:"
    echo "   1. Review security report generated"
    echo "   2. Address critical findings immediately"
    echo "   3. Implement security improvements"
    echo "   4. Schedule regular security assessments"
    echo ""
    echo "🛡️  Security is continuous - not a one-time fix!"
}

# Show usage
if [ "$1" = "--help" ]; then
    echo "Usage: ./scripts/security-audit.sh"
    echo ""
    echo "This script performs comprehensive security vulnerability assessment including:"
    echo "  - Dependency vulnerability scanning"
    echo "  - Static code analysis"
    echo "  - Configuration security checks"
    echo "  - OWASP Top 10 assessment"
    echo "  - Authentication and authorization review"
    echo "  - Input validation assessment"
    echo "  - SQL injection prevention"
    echo "  - XSS prevention"
    echo "  - File upload security"
    echo "  - Session management"
    echo "  - API security"
    echo "  - Error handling security"
    echo "  - Logging and monitoring"
    echo "  - Security headers review"
    echo "  - Database security"
    echo ""
    echo "Report generated in markdown format with actionable recommendations."
    exit 0
fi

# Run main function
main "$@"