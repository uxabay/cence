# Changelog

All notable changes to this project will be documented in this file.

---

## [Unreleased]

### Added
- Added server-generated contract overview PDFs using Spatie Laravel PDF with the DOMPDF driver.
- Added an opt-in contract sample category flag to exclude linked protocol lines from laboratory-wide dashboard totals while keeping them in contract calculations.
- Added a dedicated laboratory sample statistics page with date, customer, customer-category, sample-category, and contract-link filters.

### Changed
- Updated CI to validate PHP changes against PHP 8.4, run the PHPUnit suite, install Filament's production CSS dependencies before building frontend assets, and handle release-tag pushes when selecting changed files for Pint.
- Raised the minimum supported PHP version to 8.4 to match the production runtime and locked dependencies.
- Updated Composer dependencies to address production security advisories.
- Updated npm dependencies within their declared version ranges to resolve frontend build-tool advisories.
- Contract budget summaries now use net forecasts after active revisions consistently across the overview and dashboard.
- The dashboard displays a positive remaining budget or an explicit overrun amount.
- Contract overview widgets now follow the Filament theme setting instead of the operating system color preference.
- Contract overview PDFs use a dedicated Greek-capable, table-based layout and preserve the selected protocol date range.
- Contract overview PDF access requires authentication and the existing contract-view permission.
- Laboratory-wide sample charts omit active protocol lines whose contract sample category is marked for exclusion; protocol records and contract totals remain unchanged.
- Sample statistics report received, valid, and invalid sample counts, monthly intake trends, and distributions by sample category, customer category, and customer, with optional same-period previous-year comparison.
- Qualified registration status and date columns in sample-statistics queries to avoid ambiguous-column errors in category breakdowns.
- Rebuilt the sample statistics presentation with Filament's native stats, charts, and tables, and aligned its navigation group with the existing Laboratory group.
- Tightened the responsive statistics filters, gave category charts full width with data-dependent heights, and suppressed year-over-year comparisons when the filtered prior period has no sample data.
- Fixed chart aspect ratios so monthly trends and category bars render at readable, distinct heights in the browser.
- Clarified that the dashboard's completed-sample count covers valid samples in active contracts across all dates, including categories excluded from laboratory-wide totals. The statistics contract split now recognizes a contract-sample link even when the registration's contract field is blank, and changing a registration's contract clears a sample category that belongs to another contract.
- Moved the dashboard completed-sample definition out of the stat card and into a footnote below the cards.
- Placed sample statistics as an independent item in the Reports navigation group and gave the page its own `view_sample_statistics` permission.
- Ordered the Reports navigation group before System so System remains the final group.
- Prefilled contract overview date filters from contract start through today and added quarter-focused quick periods.
- Kept Filament Shield custom permission keys in snake_case and added an additive migration that copies legacy custom-permission grants and prior statistics access to the canonical permission names.

---

## [1.2.0] - 2026-02-16
- Production release currently deployed on the server.
