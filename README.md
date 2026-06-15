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
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --seed
```

Open <http://localhost:8080> and sign in with the default administrator account.

## Local Development Without Docker

The runtime is dependency-light PHP and can be run locally with SQLite for quick testing:

```bash
cp .env.example .env
printf '\nDB_CONNECTION=sqlite\nDB_DATABASE=%s/storage/database.sqlite\n' "$PWD" >> .env
php artisan migrate --seed
php -S 127.0.0.1:8080 -t public
```

## Development Commands

```bash
php artisan migrate --fresh --seed
php artisan test
npm run build
composer dump-autoload
```

Docker service commands are also available:

```bash
docker compose logs -f app nginx mysql redis
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan queue:restart
```

## Architecture Notes

The codebase follows the blueprint's layered approach with route dispatching, controllers, repositories, services, schema/seed logic, Blade-like PHP views, and audit support. Heavy framework dependencies were intentionally avoided so this repository is immediately runnable in the provided environment while preserving the documented ERP modules and Docker topology.

## Documentation

Read the full implementation blueprint at [`docs/quotation-erp-blueprint.md`](docs/quotation-erp-blueprint.md). It includes the BRD, PRD, system architecture, database design, API design, roadmap, sprint plan, permission matrix, deployment guide, and production checklist.
