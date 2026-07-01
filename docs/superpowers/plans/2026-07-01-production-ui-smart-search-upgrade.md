# Production UI Smart Search Upgrade Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Harden Babybib for production while establishing a restrained formal UI component layer and improving Thai-first Smart Search reliability without a full framework rewrite.

**Architecture:** Keep the short-term stack: PHP Vanilla, PDO MySQL, Tailwind CSS, daisyUI, Vanilla JS, and Alpine.js for small interactions. Add guardrails and reusable component classes first, then refactor high-risk behavior behind existing endpoints so current pages keep working.

**Tech Stack:** PHP 8.x, PDO MySQL, Composer, Tailwind CSS 3, daisyUI, Vanilla JS, Font Awesome, PhpWord.

---

## Current Baseline

- Snapshot commit before major changes: `399d824`.
- Working branch: `codex-production-ui-smart-search-upgrade`.
- Existing risks to address first:
  - CSRF debug logging exposes token values.
  - Runtime request paths contain schema changes with `ALTER TABLE`.
  - Smart Search is a single large endpoint with mixed HTTP, parsing, scoring, caching, and routing.
  - Smart Search UI uses heavy inline styles and animated visual patterns that do not match a formal product UI.
  - npm audit reports `postcss < 8.5.10`.

## Success Criteria

- Production checks can be run from the command line before deploy.
- No session or CSRF secret values are written to logs.
- Baseline build and lint commands are documented with results.
- UI foundation provides consistent Babybib component classes for buttons, inputs, badges, panels, alerts, skeletons, empty states, toolbars, and search result items.
- Smart Search remains available at `/api/smart_search.php?q=...`.
- Smart Search gives Thai-first results where available, shows clear source status, and degrades gracefully when external sources fail.
- Changes are committed in small reviewable groups.

## Phase 0: Branch And Baseline Verification

**Files:**
- Read: `composer.json`
- Read: `package.json`
- Read: PHP files across the project

- [x] Create branch from `main`.
- [x] Run `git status --short`.
- [x] Run PHP syntax check for all PHP files excluding `vendor`.
- [x] Run `composer validate --no-check-publish`.
- [x] Run `composer audit`.
- [x] Run `npm audit --audit-level=moderate`.
- [x] Run `npm run build`.
- [x] Record any baseline failures in this plan or the final work summary.

Baseline notes:
- `composer validate --no-check-publish` passed with a license warning.
- `composer audit` passed with no advisories.
- PHP syntax check passed.
- `npm run build` passed with a Browserslist/caniuse-lite freshness warning.
- Initial `npm audit --audit-level=moderate` failed on `postcss < 8.5.10`.

## Phase 1: Production Safety P0

**Files:**
- Modify: `includes/config.php`
- Create: `scripts/check-production.php`
- Create: `docs/production-readiness-checklist.md`
- Modify: `package-lock.json` if dependency remediation is needed

- [x] Remove raw CSRF/session token values from CSRF failure logs.
- [x] Replace CSRF diagnostics with non-secret metadata: remote IP, session id prefix or hash, token presence booleans, request URI.
- [x] Add `scripts/check-production.php` to validate production environment readiness:
  - `SITE_ENV=production`
  - `DEBUG_MODE=false`
  - `APP_KEY` exists and is not `babybib-change-this-app-key`
  - `SESSION_COOKIE_SECURE=1` when `SITE_URL` is HTTPS
  - DB username is not `root`
  - Required PHP extensions are loaded: `pdo_mysql`, `curl`, `mbstring`, `json`, `dom`, `libxml`, `zip`
  - Writable directories exist: `tmp`, `logs`, `backups`
  - Sensitive directories include `.htaccess` where applicable
- [x] Add production checklist documentation with exact commands and expected pass/fail behavior.
- [x] Remediate npm audit issue for `postcss` using the smallest lockfile-safe update.

Phase 1 notes:
- `npm audit fix` updated `postcss` to `8.5.16` and `nanoid` to `3.3.15`.
- `php scripts/check-production.php` correctly fails on the local development `.env`: `SITE_ENV`, `DEBUG_MODE`, missing `APP_KEY`, and `DB_USER=root`.

## Phase 2: UI Component Foundation

**Files:**
- Modify: `assets/css/components.css`
- Modify: `assets/css/main.css` only if token alignment is required
- Read: `DESIGN.md`

- [x] Define restrained formal product UI tokens:
  - Neutral surfaces and borders
  - Single violet accent for primary action and selected states
  - Semantic states for success, warning, danger, info
  - Reduced shadows and gradients
- [x] Add component classes:
  - `bb-btn`, `bb-btn-primary`, `bb-btn-secondary`, `bb-btn-ghost`, `bb-btn-danger`
  - `bb-input`, `bb-select`, `bb-field`, `bb-help`, `bb-error`
  - `bb-badge`, `bb-badge-source`, `bb-badge-muted`
  - `bb-alert`, `bb-alert-info`, `bb-alert-warning`, `bb-alert-danger`, `bb-alert-success`
  - `bb-panel`, `bb-toolbar`, `bb-empty`, `bb-skeleton`
  - `bb-result-item`, `bb-result-icon`, `bb-result-title`, `bb-result-meta`, `bb-result-action`
- [x] Include default, hover, focus, active, disabled, loading, and error states where applicable.
- [x] Avoid decorative gradients, spinning/rotating hover effects, excessive shadows, and nested card styling.

Phase 2 notes:
- Added additive `bb-*` component classes to `assets/css/components.css` without replacing existing page markup yet.
- Included dark-mode token overrides and mobile result-item behavior.

## Phase 3: Smart Search UI Refinement

**Files:**
- Modify: `generate.php`
- Modify: `assets/css/components.css`

- [x] Replace Smart Search inline styles with Babybib component classes where practical.
- [x] Keep existing debounce, abort controller, keyboard navigation, and result selection behavior.
- [x] Convert loading dropdown to `bb-skeleton` rows.
- [x] Convert source error banner to `bb-alert-warning`.
- [x] Convert no-result state to `bb-empty` with Thai-first guidance.
- [x] Normalize source badges using restrained colors and readable labels.
- [x] Keep result item layout stable on mobile and desktop.

Phase 3 notes:
- Updated the Smart Search toolbar, dropdown states, result items, source badges, show-more row, and source footer.
- Added HTML escaping for Smart Search data rendered from external sources.
- Continued Generate page component migration: hero/step markup, URL scraper box, author rows, action buttons, preview controls, and guest alert now use formal class-based styling with `bb-*` component vocabulary where practical.
- Full visual QA is still blocked locally until MySQL can be started; `generate.php` returns 200 but the page body is interrupted by the local database connection failure.

## Phase 4: Smart Search Reliability Refactor

**Files:**
- Modify: `api/smart_search.php`
- Create: `src/Search/SearchService.php`
- Create: `src/Search/SearchHttpClient.php`
- Create: `src/Search/SearchCache.php`
- Create: `src/Search/SearchRateLimiter.php`
- Create: `src/Search/SearchResultNormalizer.php`
- Create: `src/Search/SourceAdapters/ThaiJoAdapter.php`
- Create: `src/Search/SourceAdapters/ThaiLisAdapter.php`
- Create: `src/Search/SourceAdapters/OpenAlexThaiAdapter.php`
- Create: `src/Search/SourceAdapters/GoogleBooksThaiAdapter.php`
- Create: `src/Search/SourceAdapters/CrossRefAdapter.php`

- [x] Keep `/api/smart_search.php?q=...` response shape backward-compatible.
- [x] Move HTTP timeout and error tracking into `SearchHttpClient`.
- [x] Move cache read/write into `SearchCache`.
- [x] Move rate-limit state into `SearchRateLimiter` with file locking.
- [x] Add stale fallback support to `SearchCache`.
- [x] Move Thai title dedupe and metadata scoring into `SearchResultNormalizer`.
- [x] Prioritize Thai sources for Thai queries: ThaiLIS, ThaiJO, OpenAlex Thai, Google Books Thai, CrossRef.
- [x] Keep global fallback for English/non-Thai queries.
- [x] Add source error summary without exposing sensitive internals.

Phase 4 notes:
- First incremental backend checkpoint extracts cache and rate-limit file handling without changing the public endpoint response shape.
- Added `SearchHttpClient` for single and multi-request HTTP fetching, timeout handling, SSL verification, and sanitized source error reporting.
- Added stale cache fallback when external sources fail or throw after a previously cached result exists.
- Added `SearchResultNormalizer` for title normalization, duplicate checks, confidence scoring, and result ordering.
- Verified Thai keyword flow prioritizes ThaiLIS, ThaiJO, OpenAlex Thai, Google Books Thai, Open Library fallback, then CrossRef. Non-Thai keyword flow keeps Open Library, Google Books, Semantic Scholar, and CrossRef fallback.

## Phase 5: Runtime Schema Cleanup

**Files:**
- Modify: `includes/session.php`
- Modify: `includes/config.php`
- Modify: `api/auth/register.php`
- Create: `database/migrations/20260701_001_production_schema_hardening.sql`
- Create: `scripts/check-schema.php`

- [x] Create versioned SQL migration for schema hardening.
- [x] Add `scripts/check-schema.php` to verify required tables/columns before deploy.
- [x] Move runtime `ALTER TABLE` checks out of request paths.
- [x] Keep runtime code tolerant of older schemas only where needed, without modifying schema during user requests.

Phase 5 notes:
- Added `database/migrations/20260701_001_production_schema_hardening.sql` for user/profile, verification, and reset-token compatibility fields.
- Added a read-only schema readiness checker for deployment gates.
- Replaced runtime schema mutations with read-only schema guards and a tolerant current-user projection.
- Moved the `user_ratings` table out of the rating request path and into migration/schema checks.
- Moved `support_reports` and `page_visits` table creation out of request paths and into migration/schema checks.

## Phase 6: Access Control And Multi-User Stability

**Files:**
- Review: `api/admin/*.php`
- Review: `api/auth/*.php`
- Review: `api/bibliography/*.php`
- Review: `api/projects/*.php`

- [x] Verify every state-changing API uses CSRF protection through `includes/session.php`.
- [x] Verify admin endpoints call `requireAdmin()` or `isAdmin()`.
- [x] Verify user-owned project/bibliography resources filter by `user_id`.
- [x] Wrap project create/delete flows that modify counters in transactions.
- [x] Replace project quota checks with transaction-safe checks.
- [x] Replace race-prone bibliography create/delete counter updates where practical.

Phase 6 notes:
- Project create now locks the user row, checks quota inside the transaction, writes the project, and recalculates `project_count`.
- Project delete now verifies ownership inside the transaction, only unlinks the current user's bibliographies, deletes by `id + user_id`, and recalculates `project_count`.
- Project content fetch now filters bibliographies by both `project_id` and `user_id`.
- Bibliography create/delete now performs quota checks and counter recalculation inside transactions.
- Added `scripts/check-access-control.php` to make admin guard, session/CSRF gate, and ownership-token checks repeatable.

## Phase 7: Verification

**Commands:**
- `git status --short`
- `find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l`
- `composer validate --no-check-publish`
- `composer audit`
- `npm audit --audit-level=moderate`
- `npm run build`
- `php scripts/check-production.php`
- `php scripts/check-schema.php`
- `php scripts/check-access-control.php`

- [x] Run all commands above.
- [x] Record failures that require environment-specific fixes.
- [x] Smoke test Smart Search UI locally if a server is running.
- [x] Commit changes in focused groups.

Phase 7 notes:
- `find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l` passed.
- `composer validate --no-check-publish` passed with the existing license warning.
- `composer audit` passed.
- `npm audit --audit-level=moderate` passed.
- `npm run build` passed with the existing Browserslist/caniuse-lite freshness warning.
- `php scripts/check-access-control.php` passed with 0 failures and 0 warnings.
- `php scripts/check-production.php` correctly fails in the local environment: `SITE_ENV`, `DEBUG_MODE`, missing `APP_KEY`, and `DB_USER=root`.
- `php scripts/check-schema.php` correctly fails locally because the database connection is unavailable.
- Smart Search endpoint smoke test passed on `php -S 127.0.0.1:8099` using Thai query `การศึกษาไทย`. The endpoint returned JSON with real results from `openlibrary` and `thaijo`, and sanitized source errors for upstream `api.openalex.org` and `www.googleapis.com`.

## Commit Plan

- [ ] `chore: add production readiness checks`
- [ ] `fix: avoid logging csrf secrets`
- [ ] `style: add babybib component foundation`
- [ ] `style: refine smart search interface`
- [ ] `refactor: split smart search service`
- [ ] `fix: move runtime schema checks to migrations`
- [ ] `fix: harden ownership and counter updates`
