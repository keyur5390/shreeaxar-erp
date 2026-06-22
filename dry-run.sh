#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
PASSED=0
FAILED=0
WARNINGS=0

# Helper functions
print_header() {
    echo -e "\n${BLUE}=========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}=========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
    ((PASSED++))
}

print_error() {
    echo -e "${RED}✗${NC} $1"
    ((FAILED++))
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
    ((WARNINGS++))
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Main script
print_header "Shree Axar ERP - Complete Setup Dry Run & Validation"

# Step 1: PHP Environment Check
print_header "Step 1: PHP Environment Validation"

php_version=$(php -v | head -1)
print_info "PHP Version: $php_version"

php_version_num=$(php -r 'echo PHP_VERSION;')
required_version="8.2"

if [[ $(printf '%s\n' "$required_version" "$php_version_num" | sort -V | head -n1) == "$required_version" ]]; then
    print_success "PHP version $php_version_num meets minimum requirement ($required_version)"
else
    print_error "PHP version $php_version_num does not meet minimum requirement ($required_version)"
fi

# Check required PHP extensions
echo -e "\nChecking required PHP extensions:"
required_extensions=("pdo" "json" "mbstring")
missing_extensions=()

for ext in "${required_extensions[@]}"; do
    if php -m | grep -qi "^${ext}$"; then
        print_success "Extension '$ext' is loaded"
    else
        print_error "Extension '$ext' is missing"
        missing_extensions+=("$ext")
    fi
done

# Check database extensions
echo -e "\nChecking database extensions:"
if php -m | grep -qi "pdo_mysql"; then
    print_success "PDO MySQL extension available"
else
    print_warning "PDO MySQL extension not available (required for MySQL backend)"
fi

if php -m | grep -qi "pdo_sqlite"; then
    print_success "PDO SQLite extension available"
else
    print_warning "PDO SQLite extension not available (required for SQLite backend)"
fi

# Step 2: Project Structure Validation
print_header "Step 2: Project Structure Validation"

declare -a required_files=(
    ".env.example"
    "artisan"
    "bootstrap/app.php"
    "public/index.php"
    "composer.json"
    "package.json"
    "build-assets.mjs"
    "docker-compose.yml"
    "Dockerfile"
    "docker/php/Dockerfile"
    "docker/php/entrypoint.sh"
    "docker/nginx/default.conf"
    "config/app.php"
    "database/seeders/DatabaseSeeder.php"
)

echo "Checking required files and directories:"
for item in "${required_files[@]}"; do
    if [ -f "$item" ] || [ -d "$item" ]; then
        print_success "$item exists"
    else
        print_error "$item is missing"
    fi
done

# Step 3: Environment Configuration
print_header "Step 3: Environment Configuration"

if [ ! -f ".env" ]; then
    print_warning ".env file not found - creating from .env.example"
    cp .env.example .env
    print_success ".env created from .env.example"
else
    print_success ".env file already exists"
fi

# Validate .env configuration
echo -e "\nValidating .env configuration:"
required_env_vars=("APP_NAME" "APP_KEY" "APP_ENV" "DB_CONNECTION" "DB_DATABASE" "DB_USERNAME" "DB_PASSWORD")

for var in "${required_env_vars[@]}"; do
    if grep -q "^${var}=" .env; then
        value=$(grep "^${var}=" .env | cut -d'=' -f2 | head -c 30)
        if [ -z "$value" ] || [ "$value" == "=" ]; then
            print_warning "$var is set but may be empty"
        else
            print_success "$var is configured"
        fi
    else
        print_warning "$var is not set in .env"
    fi
done

# Step 4: Dependencies Check
print_header "Step 4: Dependency Management"

# Composer check
if command -v composer &> /dev/null; then
    composer_version=$(composer --version | awk '{print $3}')
    print_success "Composer found (version $composer_version)"
    
    if [ ! -d "vendor" ]; then
        print_warning "vendor/ directory not found"
        print_info "Run 'composer install' to install PHP dependencies"
    else
        print_success "vendor/ directory exists"
        if [ -f "vendor/autoload.php" ]; then
            print_success "Composer autoload file exists"
        else
            print_error "Composer autoload file missing"
        fi
    fi
else
    print_error "Composer is not installed"
fi

# Node.js check
if command -v node &> /dev/null; then
    node_version=$(node --version)
    npm_version=$(npm --version)
    print_success "Node.js found ($node_version, npm $npm_version)"
    
    if [ ! -d "node_modules" ]; then
        print_warning "node_modules/ directory not found (optional)"
    else
        print_success "node_modules/ directory exists"
    fi
else
    print_warning "Node.js is not installed (required for asset building)"
fi

# Step 5: Storage Directories
print_header "Step 5: Storage Directories Validation"

required_dirs=("storage" "storage/uploads" "storage/exports" "storage/logs" "public/assets")
for dir in "${required_dirs[@]}"; do
    if [ -d "$dir" ]; then
        if [ -w "$dir" ]; then
            print_success "$dir exists and is writable"
        else
            print_warning "$dir exists but is not writable"
        fi
    else
        print_info "Creating directory: $dir"
        mkdir -p "$dir" 2>/dev/null || print_error "Failed to create $dir"
        [ -d "$dir" ] && print_success "$dir created" || print_error "$dir creation failed"
    fi
done

# Step 6: Artisan CLI
print_header "Step 6: Artisan CLI Validation"

if [ -f "artisan" ]; then
    if [ -x "artisan" ]; then
        print_success "artisan file is executable"
    else
        print_warning "artisan file exists but is not executable"
        print_info "Run 'chmod +x artisan' to make it executable"
        chmod +x artisan
    fi
    
    # Test artisan doctor command
    if php artisan doctor > /tmp/artisan_doctor.log 2>&1; then
        print_success "php artisan doctor passed"
        cat /tmp/artisan_doctor.log
    else
        print_error "php artisan doctor failed"
        cat /tmp/artisan_doctor.log
    fi
else
    print_error "artisan file not found"
fi

# Step 7: Database Configuration
print_header "Step 7: Database Configuration"

db_connection=$(grep "^DB_CONNECTION=" .env | cut -d'=' -f2)
print_info "Database connection type: $db_connection"

if [ "$db_connection" == "sqlite" ]; then
    db_path=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2)
    print_info "SQLite database path: $db_path"
    
    if [ ! -f "$db_path" ]; then
        print_warning "SQLite database file does not exist (will be created during migration)"
    else
        print_success "SQLite database file exists"
        if [ -w "$db_path" ]; then
            print_success "SQLite database file is writable"
        else
            print_warning "SQLite database file is not writable"
        fi
    fi
else
    db_host=$(grep "^DB_HOST=" .env | cut -d'=' -f2)
    db_port=$(grep "^DB_PORT=" .env | cut -d'=' -f2)
    db_name=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2)
    db_user=$(grep "^DB_USERNAME=" .env | cut -d'=' -f2)
    
    print_info "MySQL configuration:"
    print_info "  Host: $db_host:$db_port"
    print_info "  Database: $db_name"
    print_info "  Username: $db_user"
    
    # Try to test connection
    if command -v mysql &> /dev/null; then
        if mysql -h "$db_host" -P "$db_port" -u "$db_user" -p"$(grep '^DB_PASSWORD=' .env | cut -d'=' -f2)" -e "SELECT 1;" > /dev/null 2>&1; then
            print_success "MySQL connection test passed"
        else
            print_warning "MySQL connection test failed (database may not be running)"
        fi
    else
        print_warning "mysql client not available (skipping connection test)"
    fi
fi

# Step 8: Assets Build
print_header "Step 8: Assets Build Validation"

if [ -f "assets/app.css" ] && [ -f "assets/app.js" ]; then
    print_success "Source assets found (assets/app.css and assets/app.js)"
else
    print_error "Source assets missing"
fi

if [ -f "build-assets.mjs" ]; then
    print_success "build-assets.mjs exists"
    
    if npm run build > /tmp/npm_build.log 2>&1; then
        print_success "npm run build executed successfully"
        
        if [ -f "public/assets/app.css" ] && [ -f "public/assets/app.js" ]; then
            print_success "Built assets generated in public/assets/"
        else
            print_error "Built assets not found in public/assets/"
        fi
    else
        print_warning "npm run build encountered issues"
        cat /tmp/npm_build.log | head -5
    fi
else
    print_error "build-assets.mjs not found"
fi

# Step 9: Docker Setup (if applicable)
print_header "Step 9: Docker Configuration"

if command -v docker &> /dev/null; then
    docker_version=$(docker --version)
    print_success "Docker found ($docker_version)"
else
    print_warning "Docker is not installed (required for containerized deployment)"
fi

if command -v docker-compose &> /dev/null || command -v docker &> /dev/null && docker compose --version &> /dev/null; then
    docker_compose_cmd="docker compose"
    if ! command -v docker &> /dev/null || ! docker compose --version &> /dev/null; then
        docker_compose_cmd="docker-compose"
    fi
    
    print_success "Docker Compose is available"
    
    # Validate docker-compose.yml
    if $docker_compose_cmd config > /dev/null 2>&1; then
        print_success "docker-compose.yml is valid"
        
        echo -e "\nConfigured services:"
        $docker_compose_cmd config --services | while read service; do
            print_info "  - $service"
        done
    else
        print_error "docker-compose.yml has validation errors"
        $docker_compose_cmd config 2>&1 | head -10
    fi
    
    # Check Dockerfile
    if [ -f "docker/php/Dockerfile" ]; then
        print_success "docker/php/Dockerfile exists"
        
        # Basic Dockerfile syntax check
        if grep -q "FROM php:" docker/php/Dockerfile; then
            print_success "Dockerfile appears to have valid syntax"
        else
            print_error "Dockerfile syntax may be invalid"
        fi
    else
        print_error "docker/php/Dockerfile not found"
    fi
    
    # Check entrypoint script
    if [ -f "docker/php/entrypoint.sh" ]; then
        if [ -x "docker/php/entrypoint.sh" ]; then
            print_success "entrypoint.sh is executable"
        else
            print_warning "entrypoint.sh exists but is not executable"
            chmod +x docker/php/entrypoint.sh
        fi
    else
        print_error "entrypoint.sh not found"
    fi
else
    print_warning "Docker Compose is not installed (required for containerized deployment)"
fi

# Step 10: Test Suite
print_header "Step 10: Test Suite Validation"

if [ -f "tests/run.php" ]; then
    print_success "tests/run.php exists"
    
    # Only run tests if dependencies are installed
    if [ -d "vendor" ]; then
        if php artisan test > /tmp/test_results.log 2>&1; then
            print_success "Tests passed"
            cat /tmp/test_results.log
        else
            print_error "Some tests failed"
            tail -20 /tmp/test_results.log
        fi
    else
        print_warning "Skipping tests - vendor/ not found (run 'composer install' first)"
    fi
else
    print_error "tests/run.php not found"
fi

# Step 11: Application Bootstrap
print_header "Step 11: Application Bootstrap Check"

if [ -f "bootstrap/app.php" ]; then
    print_success "bootstrap/app.php exists"
    
    # Validate PHP syntax
    if php -l bootstrap/app.php > /dev/null 2>&1; then
        print_success "bootstrap/app.php has valid PHP syntax"
    else
        print_error "bootstrap/app.php has PHP syntax errors"
        php -l bootstrap/app.php
    fi
fi

if [ -f "public/index.php" ]; then
    print_success "public/index.php exists"
    
    # Validate PHP syntax
    if php -l public/index.php > /dev/null 2>&1; then
        print_success "public/index.php has valid PHP syntax"
    else
        print_error "public/index.php has PHP syntax errors"
        php -l public/index.php
    fi
fi

# Step 12: Configuration Files
print_header "Step 12: Configuration Files Validation"

if [ -f "config/app.php" ]; then
    print_success "config/app.php exists"
    
    if php -l config/app.php > /dev/null 2>&1; then
        print_success "config/app.php has valid PHP syntax"
    else
        print_error "config/app.php has PHP syntax errors"
    fi
fi

# Step 13: Database Migrations and Seeders
print_header "Step 13: Database Setup Components"

if [ -f "database/seeders/DatabaseSeeder.php" ]; then
    print_success "DatabaseSeeder.php exists"
    
    if php -l database/seeders/DatabaseSeeder.php > /dev/null 2>&1; then
        print_success "DatabaseSeeder.php has valid PHP syntax"
    else
        print_error "DatabaseSeeder.php has PHP syntax errors"
    fi
else
    print_error "DatabaseSeeder.php not found"
fi

# Check for migration files
migration_files=$(find database/migrations -name "*.php" 2>/dev/null | wc -l)
migration_sql_files=$(find database/migrations -name "*.sql" 2>/dev/null | wc -l)
print_info "Found $migration_files PHP migration files"
print_info "Found $migration_sql_files SQL migration files"

if [ $migration_files -gt 0 ] || [ $migration_sql_files -gt 0 ]; then
    print_success "Database migration files exist"
else
    print_warning "No migration files found (may be expected for fresh setup)"
fi

# Step 14: Nginx Configuration (for Docker setup)
print_header "Step 14: Web Server Configuration"

if [ -f "docker/nginx/default.conf" ]; then
    print_success "docker/nginx/default.conf exists"
    
    if grep -q "fastcgi_pass app:9000" docker/nginx/default.conf; then
        print_success "Nginx configured to proxy to PHP-FPM"
    else
        print_warning "Nginx FastCGI configuration may be missing"
    fi
    
    if grep -q "root /var/www/html/public" docker/nginx/default.conf; then
        print_success "Nginx root directory correctly configured"
    else
        print_warning "Nginx root directory may not be correctly configured"
    fi
else
    print_error "docker/nginx/default.conf not found"
fi

# Summary
print_header "Dry Run Summary"

total_checks=$((PASSED + FAILED + WARNINGS))
echo -e "Total Checks: ${BLUE}$total_checks${NC}"
echo -e "Passed:       ${GREEN}$PASSED${NC}"
echo -e "Failed:       ${RED}$FAILED${NC}"
echo -e "Warnings:     ${YELLOW}$WARNINGS${NC}"

echo -e "\n${BLUE}=========================================${NC}"
if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All critical checks passed!${NC}"
    echo -e "${BLUE}=========================================${NC}\n"
    
    echo "Next steps to run the project:"
    echo ""
    echo "Option 1: Using Docker (Recommended)"
    echo "  $ docker compose up -d --build"
    echo "  $ docker compose logs -f app"
    echo "  $ open http://localhost:8080"
    echo ""
    echo "Option 2: Local Development (SQLite)"
    echo "  $ composer install"
    echo "  $ php artisan migrate --seed"
    echo "  $ php -S 127.0.0.1:8080 -t public"
    echo "  $ open http://127.0.0.1:8080"
    echo ""
    echo "Default Login Credentials:"
    echo "  Email:    admin@example.com"
    echo "  Password: Password@123"
    echo ""
else
    echo -e "${RED}✗ Some critical checks failed!${NC}"
    echo -e "${BLUE}=========================================${NC}\n"
    
    echo "Please address the errors above before proceeding."
    echo ""
    exit 1
fi
