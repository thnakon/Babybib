# Babybib V3 Compatibility Rebuild Design

Date: 2026-06-15

## Summary

Babybib V3 will be a new vanilla PHP project built beside the current project, not a framework migration. The goal is to modernize the structure and UI while preserving all existing data, feature behavior, and important public paths.

The selected approach is a compatibility-first rebuild:

- New project path: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`
- Current project remains untouched during development.
- Database migration uses a copied test database first.
- Existing paths stay compatible, including `.php` entry points, `users/`, `admin/`, and `api/`.
- UI direction is Babybib Console: Supabase-like admin structure with Babybib colors and product-grade density.

## Goals

- Rebuild Babybib as a clean vanilla PHP application with a modern internal structure.
- Keep every existing user-facing feature available in V3 before calling the migration complete.
- Reuse the existing production data model, uploads, templates, and exported-document behavior.
- Improve maintainability by separating core PHP concerns from page markup.
- Replace the old custom CSS-heavy UI with Tailwind CSS and a reusable PHP component vocabulary.
- Give the admin area a Supabase-style console experience using the Babybib visual identity.
- Make the migration testable phase by phase with old-vs-new verification.

## Non-Goals

- Do not introduce Laravel, Symfony, Slim, or any other PHP framework.
- Do not redesign the database as a first step.
- Do not break existing URLs for core pages and API endpoints.
- Do not rewrite APA7 citation logic at the same time as the UI migration unless a parity bug is found.
- Do not switch V3 to the real production database until the copied test database passes verification.

## Constraints And Decisions

| Area | Decision |
| --- | --- |
| Backend style | Vanilla PHP |
| Frontend styling | Tailwind CSS build output, not Tailwind CDN |
| Component library | Preline UI patterns plus local reusable PHP view components |
| Admin UI direction | Supabase-like console, Babybib colors |
| Target location | `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3` |
| Data strategy | Copy existing database and uploads into a test environment first |
| URL strategy | Keep old paths compatible for the first V3 release |
| Compatibility priority | Behavior parity before cleanup beyond the active scope |

Implementation outside the current repository path will require filesystem approval because the current sandbox only allows writing inside `/Applications/XAMPP/xamppfiles/htdocs/babybib` and temporary directories.

## Proposed Structure

```text
babybib-v3/
  .env
  .env.example
  .htaccess
  composer.json
  package.json
  tailwind.config.js
  postcss.config.js

  index.php
  generate.php
  login.php
  register.php
  verify.php
  forgot-password.php
  reset-password.php
  sort.php
  summary.php
  start.php
  privacy.php
  terms.php

  users/
    dashboard.php
    bibliography-list.php
    projects.php
    project-preview.php
    report-template.php
    report-builder.php
    activity-history.php
    profile.php

  admin/
    index.php
    users.php
    bibliographies.php
    projects.php
    announcements.php
    feedback.php
    settings.php
    backups.php
    logs.php
    notifications.php
    profile.php

  api/
    auth/
    bibliography/
    projects/
    template/
    admin/
    feedback/
    rating/
    scraper/
    support/
    smart_search.php

  src/
    Config/
      env.php
      config.php
      email-config.php
    Core/
      Database.php
      Session.php
      Security.php
      Response.php
    Helpers/
      functions.php
      EmailHelper.php
      VisitTracker.php
    Views/
      layout/
        head.php
        navbar-guest.php
        navbar-user.php
        sidebar-admin.php
        footer.php
        footer-admin.php
      components/
        button.php
        input.php
        modal.php
        toast.php
        badge.php
        table.php
        loading-overlay.php
        announcement-toast.php

  assets/
    css/
      input.css
      app.css
    js/
      app.js
      apa7-formatter.js
      tour.js
    images/
    templates/

  lang/
  database/
  sql/
  uploads/
  backups/
  logs/
  tmp/
  scripts/
```

The root keeps legacy-compatible entry points. Internally, those pages can require files from `src/` so logic becomes more structured without changing external URLs.

## Architecture

V3 should keep a simple request model:

1. A legacy-compatible PHP entry point receives the request.
2. The page loads `src/Config/config.php` and shared core classes.
3. Auth-required pages call session guards from `src/Core/Session.php`.
4. State-changing endpoints call CSRF verification from `src/Core/Security.php`.
5. Database access goes through `src/Core/Database.php`.
6. Page chrome and repeated controls come from `src/Views`.
7. Existing business behavior is moved into focused helpers or service-style PHP classes only when that reduces risk or duplication.

This is not an MVC framework. The goal is a practical structure that keeps vanilla PHP understandable while preventing every page from becoming a large mixed PHP/HTML/JS file.

## Data Migration Strategy

The selected strategy is test-copy first:

1. Export the current database.
2. Import it into a separate V3 test database.
3. Copy or symlink non-sensitive development copies of required uploads and templates.
4. Configure V3 `.env` to point to the test database.
5. Run old-vs-new verification against the copied data.
6. Switch V3 to the real database only after sign-off.

The initial V3 release should avoid schema-breaking changes. If a schema patch is required, it must be additive where possible and recorded in `sql/`.

## UI Design System

The selected visual direction is Babybib Console.

### Product Scene

Users are students, researchers, faculty, and administrators working in a browser during academic tasks. They need accuracy, trust, and repeatable workflows more than decorative presentation. The UI should feel calm, organized, and professional during long sessions.

### Visual Rules

- Admin surfaces use a dark, compact sidebar and a light content area.
- Babybib violet is used for primary actions, active navigation, selected tabs, focus rings, and important states.
- Neutral surfaces should be lightly tinted, not pure black or pure white.
- Tables and forms use practical density, clear spacing, and consistent controls.
- Public pages can be warmer and more explanatory, but the first screen should still lead users into the actual tool.
- Generate page is a workspace, not a landing page.
- Use Lucide or a single consistent icon set.
- Avoid heavy gradients, decorative blobs, nested cards, and low-contrast text.

### Core Layouts

Admin console:

- Left sidebar with Babybib logo, primary admin navigation, active state, and account control.
- Top row in content area for page title, search/filter, and primary action.
- Stats, tables, and forms appear as bordered panels with restrained elevation.
- CRUD flows prefer inline panels or focused drawers where practical; modal use should be purposeful.

Generate workspace:

- Left column: resource type categories, smart search, source type controls.
- Center column: dynamic APA7 form fields.
- Right column: sticky live preview, citation variants, copy/save/export actions.
- Mobile layout collapses into step-based panels with preview reachable by tab or drawer.

User dashboard:

- Similar vocabulary to admin but lighter.
- Quick actions for generate, projects, bibliography list, and report builder.
- Recent bibliographies and projects remain prominent.

## Feature Migration Checklist

V3 is not complete until these feature groups pass parity checks.

### Auth And Account

- Register with current fields: username, name, surname, email, password, organization type/name, province, LIS CMU flag.
- Login with rate limiting.
- Email verification with OTP.
- Forgot password and reset password.
- Captcha endpoint.
- Profile update and avatar upload/remove.
- Delete account.
- Session timeout and secure cookies.
- CSRF for state-changing requests.
- Security headers.

### APA7 Bibliography Core

- `generate.php` remains accessible.
- 30+ resource types remain available.
- Author types remain available: general, anonymous, pseudonym, royal, nobility, monk, editor, organization, translator.
- Secondary Source (Cited In) remains available.
- Smart Search keeps ISBN, DOI, URL, and keyword flows.
- External source behavior remains compatible: Google Books, Open Library, CrossRef, OpenAlex, Semantic Scholar, scraper fallback.
- Live preview generates bibliography plus parenthetical and narrative citations.
- Copy to clipboard works.
- Edit mode works from existing bibliography records.
- Thai and English bibliography output selection works.

### Projects And Library

- Create, update, and delete projects.
- Enforce existing user limits.
- Save bibliographies into projects.
- Move bibliographies between projects.
- Bibliography list search, filter, sort, edit, delete.
- Project preview works.
- Export project as `.txt` and `.docx`.

### Report Templates

- Academic general report export.
- Report with logo export.
- Research or thesis report export.
- Internship report export.
- Default CMU logo support.
- Custom logo upload support.
- Bibliography injection into generated DOCX.
- Output files must be opened or inspected during verification, not only checked for HTTP success.

### Admin Console

- Dashboard stats: users, bibliographies, projects, visits, and feedback.
- User management: list, create, edit, status toggle, delete.
- Bibliography management.
- Project management.
- Announcements with active/inactive and date ranges.
- Feedback management with response/status.
- System settings, including SMTP/email settings.
- Test email button.
- Backup create, download, delete.
- Logs view.
- Notifications and unread badge.

### Support, Analytics, Help, And I18n

- Rating system.
- Support/report issue endpoint.
- Visit tracking.
- Activity logs.
- Announcement toast.
- Onboarding tour.
- Help pages for author, place, and publisher guidance.
- Start guide, privacy, terms.
- Error pages: 403, 404, 500.
- Thai/English UI language files.
- Bibliography output language remains independent from UI language.

## Implementation Phases

### Phase 0: Scaffold

- Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`.
- Initialize Composer and npm.
- Install PHP dependencies needed by the current feature set, including PhpWord for DOCX export and PHPMailer for email flows.
- Install Tailwind tooling and component dependencies.
- Create Tailwind config with Babybib tokens.
- Copy images, templates, language files, SQL files, and non-sensitive development upload samples.
- Prepare V3 `.env.example`.

### Phase 1: Core Infrastructure

- Implement config and env loading.
- Implement database connection wrapper.
- Implement session guard functions/classes.
- Implement security helpers: CSRF, sanitization, headers, upload validation support.
- Implement JSON response helpers.
- Create shared layout partials and base components.
- Add app-wide JS initialization.

### Phase 2: Auth And Public Pages

- Rebuild login, register, verify, forgot/reset password, and related APIs.
- Rebuild public pages with Babybib Console-compatible visual vocabulary.
- Preserve existing auth API response shapes where frontend code depends on them.

### Phase 3: Generate Core

- Rebuild `generate.php` as a three-panel workspace.
- Preserve APA7 formatter behavior during the first pass.
- Integrate smart search and fallback behavior.
- Reconnect save, edit, preview, copy, and export actions.
- Verify 30+ resource types and author modes with representative samples.

### Phase 4: User Area

- Rebuild dashboard, bibliography list, projects, project preview, activity history, profile.
- Rebuild sort and summary.
- Verify existing project and bibliography records render correctly.
- Verify project export.

### Phase 5: Report And DOCX

- Rebuild report template selection and report builder.
- Preserve export endpoint paths and payload behavior.
- Verify generated DOCX files against sample projects.

### Phase 6: Admin Console

- Rebuild admin dashboard and management pages.
- Rebuild settings, email test, backup, logs, feedback, announcements, notifications.
- Use Babybib Console admin design consistently.
- Run admin action checks against the test database only.

### Phase 7: Final Parity And Switch Plan

- Run old-vs-new parity checklist.
- Confirm database compatibility.
- Confirm uploads/templates access.
- Confirm export outputs.
- Confirm security checks.
- Prepare backup and rollback instructions before pointing V3 at real data.

## Verification Strategy

Each phase needs a clear old-vs-new check:

- Open the old screen and new screen with equivalent copied data.
- Exercise the main workflow manually.
- Check database writes for expected records and no unexpected schema damage.
- Check API status codes and response shapes for critical endpoints.
- Check browser console for frontend errors.
- Check logs for PHP warnings/fatals.
- Check generated DOCX files where export is involved.

For `generate.php`, representative samples must cover:

- Book.
- Journal article.
- Web page.
- Thesis.
- Report.
- Conference item.
- Media item.
- AI-generated source.
- Secondary source.
- Thai and English output.

## Risks And Controls

| Risk | Control |
| --- | --- |
| `generate.php` is large and high-impact | Migrate after core/auth are stable, preserve formatter logic first |
| APA7 output regression | Use sample inputs and compare old vs new output |
| DOCX output regression | Inspect generated documents, not only HTTP responses |
| Data loss | Develop against copied test DB, switch only after sign-off |
| Broken old links | Keep root `.php`, `users/`, `admin/`, and `api/` paths compatible |
| External API instability | Preserve cache, rate limiting, and fallback behavior |
| Admin destructive actions | Test only on copied DB and copied backup/log directories |
| UI inconsistency | Use shared Tailwind tokens and reusable PHP components |

## Acceptance Criteria

The V3 migration is ready for real-data switch only when:

- All feature groups in the checklist pass against the test database.
- Existing records are readable and editable in V3.
- Existing important paths continue to load.
- Auth, CSRF, rate limiting, and upload protections are active.
- Smart search and fallback behavior work at least as well as the current system.
- DOCX exports are generated and inspected with sample projects.
- Admin settings, backups, and logs work in the test environment.
- A backup and rollback procedure is written before production switch.
