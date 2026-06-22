#!/usr/bin/env sh
set -eu

cd /var/www/html

log() {
  printf '[entrypoint] %s\n' "$*"
}

if [ ! -f .env ] && [ -f .env.example ]; then
  log 'Creating .env from .env.example'
  cp .env.example .env
fi

if [ ! -d vendor ]; then
  log 'Installing Composer dependencies'
  composer install --no-interaction --prefer-dist --no-progress --optimize-autoloader
else
  log 'Composer dependencies already installed; refreshing optimized autoload files'
  composer dump-autoload --no-interaction --optimize
fi

if [ -f package.json ]; then
  if [ -f package-lock.json ] && [ ! -d node_modules ]; then
    log 'Installing npm dependencies with npm ci'
    npm ci --no-audit --no-fund
  fi
  log 'Building public assets'
  npm run build
fi

if [ "${1:-}" = "php-fpm" ]; then
  if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    log "Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}"
    until mysqladmin ping -h"${DB_HOST:-mysql}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME:-erp_user}" -p"${DB_PASSWORD:-secret}" --silent; do
      sleep 2
    done
  fi

  if [ -f .env ] && ! grep -Eq '^APP_KEY=base64:.+' .env; then
    log 'Generating application key'
    php artisan key:generate --write --force
  fi

  log 'Clearing cached runtime state'
  php artisan optimize:clear

  log 'Checking runtime requirements'
  php artisan doctor

  if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    log 'Running database migrations and seeders'
    php artisan migrate --seed
  else
    log 'Skipping migrations because RUN_MIGRATIONS is not true'
  fi
else
  log 'Non PHP-FPM command detected; startup bootstrap already belongs to the app service'
fi

log "Starting: $*"
exec "$@"
