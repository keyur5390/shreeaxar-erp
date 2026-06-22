# Project Setup & Troubleshooting Guide

## Quick Start

### Docker Setup (Recommended)
```bash
cp .env.example .env
docker compose up -d --build
# Access at http://localhost:8080
# Login: admin@example.com / Password@123
```

### Local Development (SQLite)
```bash
cp .env.example .env
printf '\nDB_CONNECTION=sqlite\nDB_DATABASE=%s/storage/database.sqlite\n' "$PWD" >> .env
composer install
npm run build
php artisan migrate --seed
php -S 127.0.0.1:8080 -t public
# Access at http://127.0.0.1:8080
```

---

## Running the Dry Run

Before setting up the project, validate your environment:

```bash
chmod +x dry-run.sh
./dry-run.sh
```

This script performs comprehensive checks on:
- PHP version and extensions
- Project structure and files
- Environment configuration
- Dependencies (Composer, Node.js)
- Storage directories and permissions
- Artisan CLI functionality
- Database configuration
- Asset build pipeline
- Docker and containerization setup
- Test suite readiness
- Application bootstrap
- Configuration file validity
- Database migration components
- Web server configuration

---

## Troubleshooting Common Issues

### 1. Missing PHP Extensions

**Error**: `Missing extensions: pdo, json, mbstring`

**Solution**:
```bash
# Ubuntu/Debian
sudo apt-get install php-cli php-pdo php-json php-mbstring

# macOS with Homebrew
brew install php@8.2

# Docker (already included in provided Dockerfile)
docker compose up -d --build
```

### 2. Storage Directory Permissions

**Error**: `storage/uploads is not writable`

**Solution**:
```bash
mkdir -p storage/uploads storage/exports storage/logs public/assets
chmod 775 storage storage/uploads storage/exports storage/logs public/assets

# For Docker (run inside container):
docker compose exec app chmod 775 storage storage/uploads storage/exports storage/logs public/assets
```

### 3. Composer Dependencies Not Installed

**Error**: `vendor/autoload.php not found`

**Solution**:
```bash
# Local development
composer install

# Docker (automatic on startup)
docker compose up -d --build
```

### 4. Assets Not Building

**Error**: `public/assets/app.css or app.js missing`

**Solution**:
```bash
# Ensure source files exist
ls -la assets/app.css assets/app.js

# Build manually
npm run build

# Verify build output
ls -la public/assets/
```

### 5. Database Connection Failed

**For MySQL**:
```bash
# Check .env configuration
grep "^DB_" .env

# Test connection
docker compose exec app php artisan doctor

# Manually test MySQL
mysql -h mysql -u erp_user -psecret -e "SELECT 1;"
```

**For SQLite**:
```bash
# Create empty database file if missing
touch storage/database.sqlite
chmod 666 storage/database.sqlite

# Verify connectivity
php artisan doctor
```

### 6. Artisan Not Executable

**Error**: `Permission denied: ./artisan`

**Solution**:
```bash
chmod +x artisan
```

### 7. Docker Service Won't Start

**Error**: `Error response from daemon` or `port already in use`

**Solution**:
```bash
# Check if ports are in use
lsof -i :8080  # nginx port
lsof -i :3306  # mysql port
lsof -i :6379  # redis port

# Stop and remove existing containers
docker compose down
docker compose rm -f

# Rebuild and start fresh
docker compose up -d --build

# Check logs
docker compose logs app
```

### 8. Application Won't Start After Migration

**Error**: `RuntimeException` or `Error during migration`

**Solution**:
```bash
# Fresh migration (destructive - clears all data)
php artisan migrate --fresh --seed

# Or with Docker:
docker compose exec app php artisan migrate --fresh --seed

# Check migrations status
php artisan doctor
```

### 9. Login Not Working

**Error**: `Invalid credentials` or `user not found`

**Solution**:
```bash
# Ensure seeders ran successfully
php artisan migrate --seed

# Verify admin user exists
sqlite3 storage/database.sqlite "SELECT email FROM users WHERE email='admin@example.com';"

# Or with MySQL:
mysql -h mysql -u erp_user -psecret shreeaxar_erp -e "SELECT email FROM users WHERE email='admin@example.com';"
```

### 10. Redis Connection Issues (Docker)

**Error**: `Redis connection refused`

**Solution**:
```bash
# Check Redis is running
docker compose ps redis

# Test Redis connection
docker compose exec redis redis-cli ping

# Check credentials in .env
grep REDIS .env

# Verify Redis password in compose file
grep -A 5 "redis:" docker-compose.yml
```

### 11. Nginx Configuration Error

**Error**: `502 Bad Gateway` or `upstream connect error`

**Solution**:
```bash
# Check app container is healthy
docker compose ps app

# Verify PHP-FPM is running
docker compose exec app ps aux | grep php-fpm

# Check Nginx configuration
docker compose exec nginx nginx -t

# View Nginx logs
docker compose logs nginx
```

### 12. Asset Build Fails with "Source Files Not Found"

**Error**: `ENOENT: no such file or directory, open 'assets/app.css'`

**Solution**:
```bash
# Create minimal asset files if missing
mkdir -p assets
touch assets/app.css
touch assets/app.js

# Or provide actual content:
echo "/* Application styles */" > assets/app.css
echo "// Application scripts" > assets/app.js

# Rebuild
npm run build
```

---

## Environment Variables Reference

### Application Settings
```env
APP_NAME="Shree Axar ERP"           # Application name
APP_ENV=local                        # Environment (local, staging, production)
APP_KEY=base64:...                   # Generated by php artisan key:generate
APP_DEBUG=true                       # Debug mode (false in production)
APP_URL=http://localhost:8080        # Application URL
APP_PORT=8080                        # Port for nginx
```

### Database Configuration
```env
DB_CONNECTION=mysql                  # Database driver (mysql or sqlite)
DB_HOST=mysql                        # Database host
DB_PORT=3306                         # Database port
DB_DATABASE=shreeaxar_erp            # Database name
DB_USERNAME=erp_user                 # Database user
DB_PASSWORD=secret                   # Database password
DB_ROOT_PASSWORD=root_secret         # MySQL root password
DB_FORWARD_PORT=3306                 # Port mapping from host
```

### Redis Cache/Queue
```env
CACHE_STORE=redis                    # Cache driver
QUEUE_CONNECTION=redis               # Queue driver
SESSION_DRIVER=redis                 # Session driver
SESSION_LIFETIME=120                 # Session lifetime (minutes)
REDIS_CLIENT=phpredis                # Redis client
REDIS_HOST=redis                     # Redis host
REDIS_PASSWORD=redis_secret          # Redis password
REDIS_PORT=6379                      # Redis port
REDIS_FORWARD_PORT=6379              # Port mapping from host
```

### Mail Configuration
```env
MAIL_MAILER=smtp                     # Mail driver
MAIL_HOST=mailpit                    # SMTP host
MAIL_PORT=1025                       # SMTP port
MAIL_USERNAME=null                   # SMTP username
MAIL_PASSWORD=null                   # SMTP password
MAIL_ENCRYPTION=null                 # SMTP encryption
MAIL_FROM_ADDRESS=noreply@shreeaxar.local  # From address
MAIL_FROM_NAME="${APP_NAME}"         # From name
```

### File Storage
```env
FILESYSTEM_DISK=local                # Storage disk driver
BROADCAST_CONNECTION=log             # Broadcasting driver
```

---

## Health Check Commands

```bash
# Application health
php artisan doctor

# Database connectivity
php artisan migrate --dry-run

# Test suite
php artisan test

# Docker services status
docker compose ps

# Docker service logs
docker compose logs -f app nginx mysql redis

# Check specific service
docker compose logs app --tail=100
```

---

## Performance Optimization

### Composer Autoload Optimization
```bash
composer dump-autoload -o
```

### PHP OPcache (Already configured in Dockerfile)
Located in `docker/php/opcache.ini`

### Database Query Optimization
```sql
-- Add indexes for frequently queried columns
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE quotations ADD INDEX idx_customer_id (customer_id);
ALTER TABLE quotations ADD INDEX idx_status (status);
```

### Asset Minification
Source CSS/JS are copied as-is. For production, consider:
```bash
# Install build tools
npm install --save-dev minify

# Update build-assets.mjs with minification
```

---

## Production Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate cryptographically strong `APP_KEY`
- [ ] Use strong database credentials
- [ ] Use strong Redis password
- [ ] Configure HTTPS/SSL certificates
- [ ] Set up reverse proxy (nginx) with SSL
- [ ] Enable file permissions hardening
- [ ] Configure log rotation
- [ ] Set up automated backups for MySQL
- [ ] Test login with strong credentials
- [ ] Review file permissions (644 for files, 755 for directories)
- [ ] Enable security headers in nginx
- [ ] Test all critical functionality
- [ ] Set up monitoring and alerting
- [ ] Document deployment procedure

---

## Development Workflow

### Local Development with Docker

```bash
# Start services in background
docker compose up -d

# Watch application logs
docker compose logs -f app

# Run artisan commands
docker compose exec app php artisan <command>

# Execute PHP code
docker compose exec app php -r "code here"

# Access database
docker compose exec mysql mysql -u erp_user -psecret shreeaxar_erp

# Stop services
docker compose stop

# Restart services
docker compose restart

# Full cleanup
docker compose down
```

### Local Development with SQLite

```bash
# Install dependencies
composer install
npm ci

# Build assets
npm run build

# Create database
php artisan migrate --seed

# Start development server
php -S 127.0.0.1:8080 -t public

# Run tests
php artisan test

# Watch for changes (manual restart needed)
```

---

## Additional Resources

- **Full Documentation**: See `docs/quotation-erp-blueprint.md`
- **Database Schema**: See `database/schema.sql`
- **Application Routes**: See `public/index.php`
- **Configuration Files**: See `config/` directory
- **Database Seeders**: See `database/seeders/` directory
- **GitHub Issues**: Report bugs or feature requests

---

## Support

For issues or questions:
1. Run `./dry-run.sh` to validate your environment
2. Check this troubleshooting guide
3. Review logs: `docker compose logs app` or check `storage/logs/`
4. Create a GitHub issue with error details and environment info

