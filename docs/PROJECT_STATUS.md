# Project Status — Cence

## Release status

- Production release: `v1.3.0` (`85a2b5005ef066d20a05d0e8da2ac9a3c6dcbfd8`), deployed on 2026-11-27.
- `main` and `develop` are synchronized at the release commit; the production checkout is detached at tag `v1.3.0`.
- Production `APP_VERSION` is `1.3.0`; the release returned HTTP 200 after deployment and the user confirmed the application works normally.
- The production server runs PHP `8.4.28`; PHP `8.2-FPM` is disabled and retained as an installed rollback option. Nginx routes the application to `php8.4-fpm.sock`.

## Application purpose

Cence records laboratory sample protocols and their optional links to contracts. Contract progress is calculated from active protocol entries linked to contract sample lines. A physical sample assigned to multiple contract categories is represented by separate protocol entries, which may share a protocol number.

## Current functional work

The contract overview now shows responsive contract totals, compact per-category totals, sample and financial balances or overruns, and a server-generated PDF using Spatie Laravel PDF with DOMPDF. Forecast totals use active revisions. Visible date filters apply to protocol actuals; the forecast remains the full contract forecast. The PDF uses a dedicated table-based Greek layout, opens inline in a new tab, preserves the selected date range, and is protected by the contract-view permission.

Contract overview date filters default to the contract start date through today. Quick periods include the current quarter, previous quarter, trailing four quarters, and year to date; blank date inputs reset to the corresponding default instead of remaining empty.

Contract sample categories now have an opt-in “exclude from laboratory totals” flag. Active protocol lines for flagged categories remain visible and continue to count toward contract progress and costs, but are excluded from the dashboard's laboratory-wide sample charts. The migration defaults the flag to false so existing data keeps its current totals until categories are explicitly configured.

The dedicated “Στατιστικά Δειγμάτων” page presents laboratory sample volumes without financial data as an independent item in the “Αναφορές” navigation group. Navigation groups appear in the order Συμβάσεις, Εργαστήριο, Αναφορές, Σύστημα, keeping System last. It uses Filament's native stats overview, chart, and table widgets. It defaults to year-to-date and supports date, customer, customer-category, laboratory-sample-category, and contract-link filters. It compares the selected period with the corresponding period one year earlier when filtered prior-period sample data exists, and shows monthly trends and distributions by sample category, customer category, and customer. Chart aspect ratios provide readable chart heights, with taller horizontal category charts than the monthly trend. Counts distinguish received, valid, and invalid samples; flagged contract-category protocol lines are excluded from these laboratory totals. Access uses the dedicated `view_sample_statistics` permission. The compact five-week charts remain on the main dashboard.

The main dashboard's completed-sample metric is a separate contract-progress figure: valid samples linked to active contracts, across all dates, including contract categories excluded from laboratory-wide totals. Its definition appears in a footnote below the dashboard cards. The statistics page is date-filtered and excludes those flagged categories from laboratory-wide totals. Its contract split treats a registration as contract-linked when either its contract or its contract-sample category is set. The registration form clears a category link when the selected contract changes and the category belongs to a different contract.

Filament Shield custom permission keys use snake_case to match the authorization checks in the application. The permission migration copies assignments from legacy PascalCase custom permissions to the canonical snake_case permissions, without deleting the legacy rows. It also copies any grants of the old shared `view_dashboard` permission to `view_sample_statistics`. Both v1.3.0 migrations ran successfully in production after the database backup. The permission grant-copy operations run in a database transaction and clear Spatie's permission cache after completion; the separate schema migration adds the category flag with a false default, so existing categories remain included. See the [deployment guide](GIT_WORKFLOW.md#production-deployment) for future release steps.

## Local review environment

- Local application URL: `http://cence.local`.
- The local `.env` currently points to the anonymized `cence_review` database.
- The original local `cence` database remains separate and unchanged.
- The review database preserves contract relationships, sample counts, dates, and amounts while replacing customer, user, and protocol identifiers.

## Production runtime and release dependencies

- Environment: `production`.
- PHP: `8.4.28` (confirmed by the server System Info after the PHP runtime upgrade).
- Laravel framework: `12.74.0`; Filament: `4.17.0` (locked in v1.3.0 and installed during deployment).
- Database: MySQL.
- Timezone: `Europe/Athens`.

## Git and release workflow

See [Git, versioning, release, and deployment workflow](GIT_WORKFLOW.md). Production deploys a verified tag, never a branch.

The CI runs for `main`, `develop`, and tag `v1.3.0` passed. The release used Composer production dependencies and npm dependencies with no known security advisories at the time of release.
