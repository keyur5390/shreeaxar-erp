# Shree Axar Furniture Quotation ERP

Enterprise blueprint and Docker foundation for a Laravel 12 quotation management ERP.

## What This Repository Contains

- Production-ready ERP documentation for business, product, architecture, database, API, roadmap, sprint, testing, deployment, and checklist planning.
- Dockerized Laravel infrastructure with PHP 8.4-FPM, Nginx, MySQL 8.4, Redis, queue worker, and scheduler containers.
- Environment template for Laravel, MySQL, Redis, sessions, queues, mail, and Docker port configuration.

## Documentation

Read the full implementation blueprint at [`docs/quotation-erp-blueprint.md`](docs/quotation-erp-blueprint.md).

The blueprint includes:

1. BRD
2. PRD
3. System Architecture
4. Database Design
5. Docker Setup
6. Laravel Folder Structure
7. API Design
8. Development Roadmap
9. Sprint Planning
10. Database Migrations
11. Model Relationships
12. Permission Matrix
13. Deployment Guide
14. Production Checklist

## Docker Services

| Service | Purpose |
| --- | --- |
| `nginx` | Serves the Laravel public directory and forwards PHP requests. |
| `app` | PHP 8.4-FPM Laravel application container. |
| `mysql` | MySQL 8.4 database with persistent volume. |
| `redis` | Redis cache, sessions, queue backend, and Horizon backend. |
| `queue` | Laravel Redis queue worker. |
| `scheduler` | Laravel scheduler runner. |

## Quick Start

```bash
cp .env.example .env
docker compose build
docker compose up -d
```

After creating the Laravel application inside this repository, run:

```bash
docker compose exec app composer create-project laravel/laravel:^12.0 . --prefer-dist
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

## Recommended Laravel Packages

```bash
docker compose exec app composer require laravel/breeze spatie/laravel-permission spatie/laravel-activitylog spatie/laravel-backup spatie/laravel-medialibrary maatwebsite/excel dedoc/scramble laravel/telescope laravel/horizon barryvdh/laravel-dompdf
```

Package rationale and implementation standards are documented in the blueprint.

## Core ERP Scope

- Authenticated dashboard
- Role and permission management
- User management
- Company settings
- Dynamic masters
- Customer management
- Product management
- Quotation lifecycle with PDF output
- Audit logs
- Reports and exports

## Development Commands

```bash
docker compose up -d
docker compose logs -f app nginx mysql redis
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan queue:restart
```

## Production Deployment Summary

```bash
git pull origin main
docker compose build --pull
docker compose up -d --remove-orphans
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app npm ci
docker compose exec app npm run build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan queue:restart
```
