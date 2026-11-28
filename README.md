# Cence

Cence is an intranet application for tracking laboratory sample protocols and the progress of contracts used to process those samples. It is built with Laravel and Filament and is operated in Greek.

## How the application works

The protocol (`registrations`) is the source of truth for sample activity. A registration may be linked to a contract and a contract sample category, but it may also be unlinked from a contract.

A laboratory sample can contribute to more than one contract category. In that case, it is represented by separate protocol entries, so the same protocol number may appear on more than one row. Contract totals and sample counts are calculated from those protocol entries and their contract links.

Contract sample categories have an **Exclude from laboratory totals** flag. When enabled, linked protocol entries continue to count toward that contract's progress and costs, but are omitted from laboratory-wide sample statistics. The flag defaults to `false`; existing categories remain included unless someone changes them.

## Main areas

- **Contracts:** contract details, forecast and actual progress, balances or overruns, and a printable PDF overview.
- **Laboratory:** customers, laboratory sample categories, and the protocol records used for contract and sample calculations.
- **Reports:** sample statistics by date, customer, customer category, laboratory sample category, and contract association. The page reports sample counts, not financial totals.
- **System:** users, roles, permissions, and system information.

Statistics are available independently of protocol-management permissions. The sample-statistics page uses the dedicated `view_sample_statistics` permission.

## Production release

- Release: `v1.3.0`
- Release commit: `85a2b5005ef066d20a05d0e8da2ac9a3c6dcbfd8`
- Production PHP: `8.4.28`; PHP 8.2-FPM is disabled.
- Locked application framework: Laravel `12.74.0`; Filament `4.17.0`.
- Production database: MySQL-compatible database; application timezone: `Europe/Athens`.

The server deploys an exact Git tag in detached-HEAD state. See [the Git and release workflow](docs/GIT_WORKFLOW.md) for branching, release, backup, deployment, and rollback procedures.

## Requirements

- PHP `8.4` or later, with the extensions required by `composer.json` and the selected database driver.
- Composer 2.
- Node.js 20 and npm for frontend development and production asset builds.
- MySQL/MariaDB for a production-like environment. The Laravel example environment can also be configured for SQLite where supported by the migrations.

The Filament Vite theme imports CSS from Composer-installed packages. Run `composer install` before `npm run build`.

## Local development

1. Clone the repository and enter the project directory.
2. Create a local environment file and application key:

   ```bash
   cp .env.example .env
   composer install
   php artisan key:generate
   ```

3. Configure the local database and application URL in `.env`, then create the schema:

   ```bash
   php artisan migrate
   ```

4. Install and build frontend assets, then start the development services:

   ```bash
   npm ci
   npm run build
   composer run dev
   ```

Never copy the production `.env`, `APP_KEY`, certificates, or production database into a development checkout. Keep local review data separate from production data.

## Checks

```bash
composer validate --no-check-publish
composer test
composer audit --locked --no-dev
npm audit
npm run build
```

The GitHub Actions workflow also runs migrations against MySQL, checks changed PHP files with Pint on PHP 8.4, runs PHPUnit, and builds the frontend with Node 20.

## Documentation

- [Git, versioning, release, and deployment workflow](docs/GIT_WORKFLOW.md)
- [Current project and production status](docs/PROJECT_STATUS.md)
- [Release history](CHANGELOG.md)
