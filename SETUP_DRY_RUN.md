# Shree Axar ERP - Project Setup Dry Run Guide

## Project Overview

**Shree Axar Furniture Quotation ERP** is a lightweight PHP-based ERP system with minimal dependencies. The project uses:
- **Language**: PHP 8.2+ (98.7%)
- **Architecture**: Lightweight MVC with custom routing
- **Database Support**: MySQL 8.4 or SQLite
- **Frontend**: Vanilla CSS/JS
- **Containerization**: Docker Compose

---

## Prerequisites

### System Requirements
- **Docker & Docker Compose** (for containerized setup) OR
- **PHP 8.2+** with extensions: `pdo`, `json`, `mbstring` (for local development)
- **Node.js 18+** (for asset building)
- **MySQL 8.4+** or SQLite (database)
- **Composer** (for PHP dependency management)

### Required PHP Extensions
```
- ext-pdo (PDO database abstraction)
- ext-json (JSON support)
- ext-mbstring (Multi-byte string functions)
- ext-pdo_mysql (for MySQL) or ext-pdo_sqlite (for SQLite)
```

---

## Project Structure Analysis

```
shreeaxar-erp/
├── app/                          # Application code
│   ├── Core/                      # Core framework components (Router, Database, etc.)
│   ├── Controllers/               # Request handlers
│   ├── Repositories/              # Data access layer
│   ├── Services/                  # Business logic
│   ├── Support/                   # Helper classes (Auth, CSRF, Flash, View)
│   └── Middleware/                # Request/response middleware
├── assets/                        # Source CSS/JS files
├── bootstrap/                     # Application bootstrap
│   └── app.php                    # Bootstrap file (loads helpers & Composer autoload)
├── config/                        # Configuration files
├── database/
│   ├── migrations/                # Database schema migrations
│   ├── schema.sql                 # SQL schema reference
│   └── seeders/                   # Database seeders
├── docker/                        # Docker configuration
│   ├── php/Dockerfile             # PHP container definition
│   ├── php/entrypoint.sh          # Container startup script
│   └── nginx/default.conf         # Nginx configuration
├── public/                        # Web root
│   ├── index.php                  # Application entry point
│   └── assets/                    # Generated CSS/JS (from build process)
├── resources/                     # Blade-like PHP views/templates
├── storage/                       # Writable storage (logs, uploads, exports, SQLite DB)
├── tests/                         # Automated tests
│   └── run.php                    # Test suite
├── .env.example                   # Environment template
├── artisan                        # CLI tool (custom implementation)
├── build-assets.mjs               # Asset build script (Node.js)
├── composer.json                  # PHP dependencies
├── docker-compose.yml             # Docker Compose configuration
├── package.json                   # Node.js dependencies
└── README.md                      # Documentation
```

---

## Setup Methods

### Method 1: Docker Compose (Recommended for Production)

#### Step 1: Clone and Configure

```bash
# Clone repository (if needed)
git clone https://github.com/keyur5390/shreeaxar-erp.git
cd shreeaxar-erp

# Copy environment template
cp .env.example .env

# Optional: Customize .env settings
# - APP_PORT (default: 8080)
# - DB_DATABASE (default: shreeaxar_erp)
# - DB_USERNAME (default: erp_user)
# - DB_PASSWORD (default: secret)
```

#### Step 2: Dry Run - Verify Configuration

```bash
# Check .env configuration
echo "=== Environment Configuration ==="
cat .env | grep -E "^(APP_|DB_|REDIS_|MAIL_)"

# Verify Docker is available
echo "=== Docker Version ==="
docker --version
docker compose version

# Verify docker-compose.yml syntax
echo "=== Validating docker-compose.yml ==="
docker compose config > /dev/null && echo "✓ docker-compose.yml is valid"

# Check required services are configured
echo "=== Services in docker-compose.yml ==="
docker compose config --services
```

#### Step 3: Build and Start Services

```bash
# Build images and start containers
docker compose up -d --build

# Monitor startup progress
echo "=== Waiting for services to be healthy ==="
docker compose ps

# Check app container logs
echo "=== App Container Logs ==="
docker compose logs app --tail=50

# Wait for app to be healthy
docker compose exec app php artisan doctor
```

#### Step 4: Access Application

```
URL: http://localhost:8080
Default Credentials:
  Email: admin@example.com
  Password: Password@123
```

#### Step 5: Manage Database

```bash
# Run migrations and seeders
docker compose exec app php artisan migrate --seed

# Fresh database (destructive)
docker compose exec app php artisan migrate --fresh --seed

# Backup database (MySQL)
docker compose exec mysql mysqldump -u erp_user -psecret shreeaxar_erp > backup.sql
```

---

### Method 2: Local Development (SQLite)

#### Step 1: Install Dependencies

```bash
# Clone repository
git clone https://github.com/keyur5390/shreeaxar-erp.git
cd shreeaxar-erp

# Copy and configure .env for SQLite
cp .env.example .env
cat >> .env << 'EOF'
DB_CONNECTION=sqlite
DB_DATABASE=/full/path/to/shreeaxar-erp/storage/database.sqlite
EOF

# Install PHP dependencies
composer install

# Install Node.js dependencies (optional, if npm packages are added)
npm ci

# Build assets
npm run build

# Create storage directories
mkdir -p storage/uploads storage/exports storage/logs
chmod 775 storage storage/uploads storage/exports storage/logs
```

#### Step 2: Generate Application Key

```bash
php artisan key:generate --write
```

#### Step 3: Run Database Setup

```bash
# Run migrations and seeders
php artisan migrate --seed

# Or fresh database (destructive)
php artisan migrate --fresh --seed
```

#### Step 4: Start Development Server

```bash
php -S 127.0.0.1:8080 -t public
```

#### Step 5: Access Application

```
URL: http://127.0.0.1:8080
Default Credentials:
  Email: admin@example.com
  Password: Password@123
```

---

## Dry Run Checklist

### Environment & Dependencies

- [ ] `composer.json` exists and specifies PHP 8.2+
- [ ] Required PHP extensions available: pdo, json, mbstring
- [ ] Docker & Docker Compose installed (for containerized setup)
- [ ] Node.js 18+ available (for asset building)
- [ ] `.env.example` file exists and is complete

### Configuration

- [ ] `.env` file created from `.env.example`
- [ ] `APP_KEY` is set (either via `php artisan key:generate` or Docker startup)
- [ ] Database connection configured (MySQL or SQLite)
- [ ] Redis cache configured (if using Docker)
- [ ] Storage directories exist: `storage/`, `storage/uploads/`, `storage/exports/`

### Bootstrap & Application

- [ ] `bootstrap/app.php` loads helpers and Composer autoload
- [ ] Composer autoload properly configured for `App\` and `Database\Seeders\` namespaces
- [ ] `public/index.php` initializes the router and applies middleware
- [ ] Custom `artisan` CLI is executable and has correct commands

### Database Setup

- [ ] Database migrations file exists: `database/migrations/2026_06_15_000000_create_quotation_erp_schema.sql`
- [ ] Seeders configured in `database/seeders/DatabaseSeeder.php`
- [ ] Admin user seeded with `admin@example.com` / `Password@123`
- [ ] Permission matrix and default roles seeded
- [ ] Master data (countries, states, currencies, etc.) seeded

### Frontend & Assets

- [ ] `assets/app.css` and `assets/app.js` exist (source files)
- [ ] `build-assets.mjs` copies assets from `assets/` to `public/assets/`
- [ ] `npm run build` executes successfully
- [ ] `public/assets/` directory created with built CSS/JS

### Docker Setup (if using containers)

- [ ] `docker-compose.yml` valid and all services defined
- [ ] PHP Dockerfile builds without errors
- [ ] MySQL service healthcheck passes
- [ ] Nginx service proxies requests to PHP-FPM
- [ ] Redis service available for cache/queue
- [ ] Queue and scheduler services configured
- [ ] Entrypoint script handles Composer and npm installs

### Testing & Validation

- [ ] `php artisan doctor` passes all checks
- [ ] `php artisan test` or test suite runs successfully
- [ ] Application accessible at configured URL
- [ ] Login works with default credentials
- [ ] Dashboard loads without errors
- [ ] Database queries execute successfully
- [ ] Activity logs recorded for actions

---

## Common Issues & Fixes

### Issue 1: Missing Storage Directories
**Symptom**: Error about `storage/`, `storage/uploads/`, etc.
**Fix**:
```bash
mkdir -p storage/uploads storage/exports storage/logs public/assets
chmod 775 storage storage/uploads storage/exports storage/logs public/assets
```

### Issue 2: Composer Autoload Not Loaded
**Symptom**: Class not found errors
**Fix**:
```bash
composer dump-autoload -o
# Or with Docker:
docker compose exec app composer dump-autoload -o
```

### Issue 3: Assets Not Building
**Symptom**: CSS/JS missing from `public/assets/`
**Fix**:
```bash
npm run build
# Verify:
ls -la public/assets/
```

### Issue 4: Database Connection Failed
**Symptom**: PDO connection error
**For MySQL**:
```bash
# Check MySQL is running:
docker compose ps mysql

# Verify credentials in .env:
cat .env | grep DB_

# Test connection:
docker compose exec app php artisan doctor
```

**For SQLite**:
```bash
# Check database file permissions:
ls -la storage/database.sqlite
chmod 666 storage/database.sqlite
```

### Issue 5: Permission Denied on Artisan
**Symptom**: `Permission denied` when running `php artisan`
**Fix**:
```bash
chmod +x artisan
```

### Issue 6: Redis Connection Issues (Docker)
**Symptom**: Redis connection refused
**Fix**:
```bash
# Check Redis is running:
docker compose ps redis

# Test connection:
docker compose exec redis redis-cli ping

# Check credentials in .env:
grep REDIS .env
```

---

## Validation Commands

### PHP Environment Check
```bash
php artisan doctor
```

### Database Connectivity
```bash
# Docker:
docker compose exec app php artisan doctor

# Local:
php artisan migrate --dry-run
```

### Asset Build Verification
```bash
npm run build && ls -la public/assets/
```

### Test Suite Execution
```bash
php artisan test
# Or with Docker:
docker compose exec app php artisan test
```

### Application Health Check
```bash
# Docker:
docker compose exec app php artisan doctor

# Local:
curl -I http://127.0.0.1:8080/dashboard
```

---

## Performance Tuning (Optional)

### PHP Optimization
```bash
# Optimize Composer autoload
composer dump-autoload -o

# Enable OPcache (already configured in docker/php/opcache.ini)
```

### Database Optimization
```bash
# For MySQL, enable query optimization:
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE quotations ADD INDEX idx_customer_id (customer_id);
```

### Docker Resource Limits
Edit `docker-compose.yml` to limit resources:
```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '1'
          memory: 512M
```

---

## Production Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate strong `APP_KEY`
- [ ] Set strong database credentials
- [ ] Set strong Redis password
- [ ] Configure HTTPS/SSL in Nginx
- [ ] Set up proper log rotation
- [ ] Configure backup strategy for MySQL
- [ ] Set up monitoring for container health
- [ ] Review and restrict file permissions
- [ ] Test login with fresh credentials
- [ ] Verify all email settings in `.env`

---

## Next Steps

1. **Run the dry-run script** (see section below)
2. **Review any issues** and apply fixes from "Common Issues & Fixes"
3. **Execute full setup** using Docker or local method
4. **Test application** with default credentials
5. **Customize configuration** as needed for your environment
6. **Deploy to production** following the deployment checklist

---

## Automated Dry Run Script

Create and run `dry-run.sh`:

```bash
#!/bin/bash
set -e

echo "========================================="
echo "Shree Axar ERP - Setup Dry Run"
echo "========================================="
echo

# Step 1: Environment Check
echo "Step 1: Checking Environment..."
echo "✓ PHP version:"
php -v | head -1
echo "✓ PHP extensions:"
php -m | grep -E "(PDO|json|mbstring)" || echo "WARNING: Some extensions missing"
echo

# Step 2: File Structure Check
echo "Step 2: Checking Project Structure..."
files_to_check=(
  ".env.example"
  "bootstrap/app.php"
  "public/index.php"
  "database/seeders/DatabaseSeeder.php"
  "docker-compose.yml"
  "Dockerfile"
  "artisan"
  "composer.json"
  "package.json"
)

for file in "${files_to_check[@]}"; do
  if [ -f "$file" ] || [ -d "$file" ]; then
    echo "✓ $file exists"
  else
    echo "✗ $file missing"
  fi
done
echo

# Step 3: Composer Check
echo "Step 3: Checking Composer..."
if [ ! -d "vendor" ]; then
  echo "✗ vendor/ not found - run: composer install"
else
  echo "✓ vendor/ exists"
fi
echo

# Step 4: Environment Configuration
echo "Step 4: Checking Environment Configuration..."
if [ ! -f ".env" ]; then
  echo "✗ .env not found - creating from .env.example"
  cp .env.example .env
fi
echo "✓ .env exists"
echo

# Step 5: Storage Directories
echo "Step 5: Checking Storage Directories..."
dirs=("storage" "storage/uploads" "storage/exports" "storage/logs" "public/assets")
for dir in "${dirs[@]}"; do
  if [ -d "$dir" ]; then
    echo "✓ $dir exists"
  else
    echo "- Creating $dir"
    mkdir -p "$dir"
  fi
done
echo

# Step 6: Database Check (SQLite)
echo "Step 6: Checking Database Setup..."
if grep -q "DB_CONNECTION=sqlite" .env; then
  db_path=$(grep "DB_DATABASE=" .env | cut -d'=' -f2 | tr -d '\n')
  if [ ! -f "$db_path" ]; then
    echo "- SQLite database will be created during migration"
  else
    echo "✓ SQLite database exists at $db_path"
  fi
else
  echo "✓ Using MySQL (verify connection in .env)"
fi
echo

# Step 7: Assets Check
echo "Step 7: Checking Assets..."
if [ -f "assets/app.css" ] && [ -f "assets/app.js" ]; then
  echo "✓ Source assets exist"
else
  echo "✗ Source assets missing (assets/app.css or assets/app.js)"
fi

if npm run build 2>/dev/null; then
  echo "✓ Asset build successful"
else
  echo "⚠ Asset build failed - check build-assets.mjs"
fi
echo

# Step 8: Artisan CLI Check
echo "Step 8: Checking Artisan CLI..."
if [ -x "artisan" ]; then
  echo "✓ artisan is executable"
  php artisan doctor
else
  echo "✗ artisan is not executable - run: chmod +x artisan"
fi
echo

# Step 9: Docker Check (if applicable)
echo "Step 9: Checking Docker Setup (if using containers)..."
if command -v docker-compose &> /dev/null; then
  if docker-compose config > /dev/null 2>&1; then
    echo "✓ docker-compose.yml is valid"
    echo "✓ Services:"
    docker-compose config --services | sed 's/^/  - /'
  else
    echo "✗ docker-compose.yml has errors"
  fi
else
  echo "⚠ docker-compose not installed (optional for local development)"
fi
echo

echo "========================================="
echo "Dry Run Complete!"
echo "========================================="
echo "Next steps:"
echo "1. Review any errors above"
echo "2. Run full setup: docker compose up -d --build (Docker) or php -S 127.0.0.1:8080 -t public (local)"
echo "3. Access: http://localhost:8080 or http://127.0.0.1:8080"
echo "4. Login: admin@example.com / Password@123"
echo
```

**Run the script**:
```bash
chmod +x dry-run.sh
./dry-run.sh
```

---

## Support & Documentation

- **Full Blueprint**: `docs/quotation-erp-blueprint.md`
- **Database Schema**: `database/schema.sql`
- **API Documentation**: See `public/index.php` for routing
- **Issues & Bug Reports**: Create a GitHub issue in the repository
