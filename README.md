# Shree Axar Furniture Quotation ERP

Ready-to-use quotation management ERP for Shree Axar Furniture, generated from the business requirement blueprint in [`docs/quotation-erp-blueprint.md`](docs/quotation-erp-blueprint.md).

## Included Modules

- Secure login/logout with active-user enforcement and seeded administrator account.
- Dashboard with customers, products, quotations, users, projected revenue, recent quotations, and recent audit events.
- Role and permission management with a seeded permission matrix and default roles.
- User management with activation status, password reset by admin, employee code, phone, and role assignment.
- Company settings used by quotation print/PDF output.
- Dynamic masters for countries, states, cities, address types, units, taxes, currencies, and quotation statuses.
- Customer and product CRUD with searchable lists and soft-delete behavior.
- Quotation lifecycle with draft/sent/approved/rejected statuses, deterministic item/tax/discount totals, duplication, print, and browser PDF save.
- Reports for quotations, customers, products, and revenue with CSV export.
- Activity logs for authentication, CRUD, status changes, and quotation events.

## Default Login

```text
Email: admin@example.com
Password: Password@123
```

Change this password immediately after deployment.

## Docker Quick Start

```bash
cp .env.example .env
docker compose up -d --build
```

On first startup, the PHP container now performs the full application bootstrap automatically: Composer autoload/dependency setup, optional npm dependency install, asset build, MySQL readiness wait, application key generation, cache/runtime clear, runtime doctor checks, and `php artisan migrate --seed`. Open <http://localhost:8080> after the containers become healthy and sign in with the default administrator account.

## Local Development Without Docker

The runtime is dependency-light PHP and can be run locally with SQLite for quick testing:

```bash
cp .env.example .env
printf '\nDB_CONNECTION=sqlite\nDB_DATABASE=%s/storage/database.sqlite\n' "$PWD" >> .env
php artisan migrate --seed
php -S 127.0.0.1:8080 -t public
```



## Database Schema, Migrations, and Seeders

The database assets are tracked in the repository instead of being hidden inside runtime storage:

- `database/migrations/2026_06_15_000000_create_quotation_erp_schema.sql` is the executable base migration used by `php artisan migrate`.
- `database/schema.sql` is the plain SQL schema reference for review, database tooling, and hand-off.
- `database/seeders/DatabaseSeeder.php` contains the default roles, permission matrix, admin user, masters, quotation statuses, currencies, and company settings used by `php artisan migrate --seed` and `php artisan db:seed`.

Run `php artisan migrate --fresh --seed` to rebuild a local SQLite or MySQL database from these files.

## Project Structure

This repository has been reviewed as a complete lightweight PHP ERP project. The root-level files are intentional entry points, while application code lives under `app/`, templates under `resources/views/`, web-facing files under `public/`, and runtime/generated files under `storage/`. See [`docs/project-setup-audit.md`](docs/project-setup-audit.md) for a path-by-path setup audit and validation checklist.

> Note: `assets/` contains source CSS/JavaScript. `public/assets/` is the generated web-served copy created by `npm run build`.

## Development Commands

```bash
composer dump-autoload
npm run build
php artisan doctor
php artisan migrate --fresh --seed
php artisan test
```

Docker service commands are also available:

```bash
docker compose ps
docker compose logs -f app nginx mysql redis
docker compose exec app php artisan doctor
docker compose exec app php artisan migrate --fresh --seed
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan queue:restart
```

Set `RUN_MIGRATIONS=false` in `.env` only when you intentionally want the app container to skip automatic startup migrations/seeders.

## Architecture Notes

The codebase follows the blueprint's layered approach with route dispatching, controllers, repositories, services, SQL migrations, reusable schema files, seeders, Blade-like PHP views, and audit support. Heavy framework dependencies were intentionally avoided so this repository is immediately runnable in the provided environment while preserving the documented ERP modules and Docker topology.

## Documentation

Read the full implementation blueprint at [`docs/quotation-erp-blueprint.md`](docs/quotation-erp-blueprint.md). It includes the BRD, PRD, system architecture, database design, API design, roadmap, sprint plan, permission matrix, deployment guide, and production checklist.
