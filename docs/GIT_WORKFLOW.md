# Git, Versioning, Release, and Deployment Workflow

This document describes the repository workflow for Cence. The production server is an intranet host and must run a released Git tag, never a branch.

## Repository and branches

- Canonical repository: `uxabay/cence` (`origin`).
- `develop`: normal feature work and the next unreleased application changes.
- `main`: release line and focused fixes that need to be applied directly to the release line.
- `upgrade/*`: historical or temporary upgrade branches; do not deploy them.
- Production: detached at an approved annotated version tag.

Keep feature work on `develop`. When a production fix is committed on `main`, merge `main` back into `develop` so both branches retain the fix. This can be a fast-forward when `develop` has not advanced; otherwise use a normal merge. Before releasing, fast-forward `main` to the reviewed `develop` commit. Do not rebase shared release history or force-push branches.

## Versioning and change records

Use `vMAJOR.MINOR.PATCH` tags:

- **Major:** an incompatible change to application behavior, data, or operation.
- **Minor:** backward-compatible features and substantial application changes.
- **Patch:** backward-compatible corrections and maintenance releases.

For every application change:

1. Update `CHANGELOG.md` under `Unreleased`.
2. Update `README.md`, `docs/PROJECT_STATUS.md`, or this guide when the change affects application behavior, setup, release, or operations.
3. Before release, move the included changelog entries under the new version and date.
4. Commit only reviewed project files. Do not stage `.env`, certificates, database dumps, `.playwright-mcp/`, or other machine-local artifacts.

Use short, descriptive commit subjects with a type prefix, for example `feat:`, `fix:`, `docs:`, `ci:`, or `chore:`.

## Preparing and publishing a release

Run these checks from the release candidate:

```bash
git status --short --branch
composer validate --no-check-publish
composer test
composer audit --locked --no-dev
npm audit
npm run build
```

Also confirm that:

- CI passes for the release commit and tag.
- The locked PHP requirements match the production PHP 8.4 runtime.
- Database migrations work against MySQL, and any production permission/data effect is understood.
- `CHANGELOG.md` and the documentation describe the release accurately.
- The Vite build succeeds after Composer dependencies are installed; Filament's theme CSS is in `vendor/`.
- `public/build/` is generated during deployment and is not expected to be supplied by Git.

When the release candidate is ready, fast-forward `main` to `develop`:

```bash
git switch main
git merge --ff-only develop
```

Create the annotated tag on `main`, then verify its target:

```bash
git tag -a v1.3.0 -m "Release v1.3.0"
git show --no-patch --format=fuller v1.3.0
git rev-parse 'v1.3.0^{commit}'
```

Replace `v1.3.0` with the next version. Confirm the commit hash, tag name, and green CI results before publishing. Push the synchronized branches and tag:

```bash
git push origin main develop v1.3.0
```

Replace the tag in the command with the release tag being published. Once published, treat a release tag as immutable. Do not force-update a tag that has been fetched or deployed; if a released build must be corrected, agree on a new patch release.

## Production deployment

Deploy from the exact approved tag. Commands below show `v1.3.0` as an example; verify the release commit hash before running the checkout.

### 1. Check the server and preserve its local state

On the production host:

```bash
cd /var/www/cence
git rev-parse HEAD
git describe --tags --always --dirty
git status --short --branch
php8.4 -v
php8.4 /usr/local/bin/composer --version
node --version
npm --version
```

Do not run `git clean`, `git reset --hard`, or another command that removes server-local files. Preserve `.env`, `APP_KEY`, certificates, and known untracked files such as `.env.save` and `certs/`.

### 2. Back up the database and environment

Before checkout or migration:

- Create a database dump outside the repository, with restrictive permissions. Use `mysqldump --single-transaction --quick --routines --triggers --events`, pipe it through gzip, test it with `gzip -t`, count the exported tables, and record a SHA-256 checksum.
- Copy the current `.env` to a private backup outside the repository with mode `600`. Never print or paste its contents.
- Confirm that enough disk space is available and that both backups are readable.

Keep the backup paths and checksums in the deployment record, not in tracked application files.

### 3. Fetch and verify the tag

Fetch the new tag without checking it out yet:

```bash
git fetch origin tag v1.3.0
git rev-parse 'refs/tags/v1.3.0^{commit}'
git status --short --branch
```

Compare the commit hash to the locally verified release commit. Stop if the hash differs or if fetching would replace an existing tag unexpectedly.

### 4. Install the release

Take the application into maintenance mode, then switch to the tag. Keep the maintenance window open until the commands succeed:

```bash
php8.4 artisan down --retry=60
git switch --detach v1.3.0
```

Install production dependencies with the explicit PHP 8.4 binary, then build frontend assets:

```bash
php8.4 /usr/local/bin/composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
```

Set `APP_VERSION` in the server's untracked `.env` to the version without the leading `v` (for example, `1.3.0`). Do not commit or replace the server `.env`.

Run migrations and optimize the application:

```bash
php8.4 artisan migrate --force
php8.4 artisan optimize
```

Verify the loaded `config('app.version')`, the tag and commit, and the server's local files before bringing the application online.

### 5. Bring the application online and verify it

```bash
php8.4 artisan up
```

Then verify:

- The web endpoint returns an expected HTTP status.
- System Info reports the release version, production environment, PHP 8.4, and the expected Laravel version.
- The logs contain no new errors.
- Authorized users can access sample statistics and contract overviews.
- The contract overview PDF opens and renders correctly.
- Existing permissions and records are present.
- `git describe --tags --always --dirty` reports the approved tag, and `git status` shows only known server-local files.

Keep PHP 8.2-FPM disabled after the PHP 8.4 release has been verified. Do not purge the old packages as part of routine deployment unless a separate maintenance decision is made.

## Rollback

If the release fails, leave the application in maintenance mode, inspect the error, and restore service from the previous approved tag using its matching `composer.lock` and `package-lock.json`. Rebuild `vendor/` and `public/build/` for that tag, set `APP_VERSION` to that release, optimize, and only then run `artisan up`.

Do not automatically roll back database migrations. The permission migration intentionally preserves grants on rollback, and the sample-category flag migration changes the schema. Decide any schema rollback separately after reviewing the live data and the verified database backup.
