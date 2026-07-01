# Babybib Production Readiness Checklist

Use this checklist before deploying Babybib to a public production server.

## Required Commands

Run these from the project root:

```bash
git status --short
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
composer validate --no-check-publish
composer audit
npm audit --audit-level=moderate
npm run build
php scripts/check-production.php
php scripts/check-schema.php
php scripts/check-access-control.php
```

If the schema check fails, apply the production schema migration during a maintenance window:

```bash
mysql -u "$DB_USER" -p "$DB_NAME" < database/migrations/20260701_001_production_schema_hardening.sql
```

## Environment

- `SITE_ENV=production`
- `DEBUG_MODE=false`
- `APP_KEY` is set, random, at least 32 characters, and not `babybib-change-this-app-key`
- `SITE_URL` uses `https://`
- `SESSION_COOKIE_SECURE=1` when using HTTPS
- `DB_USER` is not `root`
- `.env` is present on the server and is never committed

## Required PHP Extensions

- `pdo_mysql`
- `curl`
- `mbstring`
- `json`
- `dom`
- `libxml`
- `zip`

## Writable Directories

- `tmp`
- `logs`
- `backups`

## Protected Directories

These directories must contain `.htaccess` protection:

- `backups`
- `logs`
- `uploads`

## Deployment Notes

- Run database migration scripts before serving new code.
- Run `php scripts/check-schema.php` after migrations and before opening traffic.
- Run `php scripts/check-access-control.php` before release to catch missing endpoint guards.
- Keep backups outside public web access where possible.
- Do not run schema changes from normal user requests.
- Verify Smart Search with Thai queries after deploy.
- Review logs after the first production traffic window.
