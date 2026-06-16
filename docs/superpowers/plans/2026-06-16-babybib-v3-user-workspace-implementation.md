# Babybib V3 User Workspace Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate the authenticated user workspace foundation into V3: dashboard, projects, bibliography library, and project CRUD APIs with legacy-compatible paths.

**Architecture:** Keep public paths under `/users/*` and `/api/projects/*`, but move SQL into small workspace classes under `src/Workspace`. Pages should be render-only plus simple input parsing. Avoid request-time DDL and avoid N+1 queries by using aggregate queries and paginated result sets.

**Tech Stack:** Vanilla PHP 8.1+, PDO, Composer PSR-4, Tailwind CSS, Preline, existing MySQL schema.

---

## Legacy Contract Notes

- Authenticated pages redirect guests to `/login.php`.
- Preserve user paths: `/users/dashboard.php`, `/users/projects.php`, `/users/bibliography-list.php`.
- Preserve project API paths: `/api/projects/create.php`, `/api/projects/update.php`, `/api/projects/delete.php`, `/api/projects/get-content.php`.
- Dashboard needs user greeting, bibliography count, project count, remaining quotas, and six recent bibliographies.
- Projects page needs search, sort, pagination, create/edit/delete entry points.
- Bibliography list needs search, resource type filter, project filter, sort, pagination, and project/resource metadata.
- Do not run cleanup deletes on page view. Old-record cleanup should become a later explicit maintenance action.

## File Structure

Create or modify these files under `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`:

```text
src/Workspace/
  BibliographyLibrary.php
  DashboardSummary.php
  ProjectWorkspace.php
  CurrentUser.php
src/Views/layout/
  navbar-user.php
users/
  dashboard.php
  projects.php
  bibliography-list.php
api/projects/
  create.php
  update.php
  delete.php
  get-content.php
tests/workspace_contract.php
scripts/check-workspace.sh
```

## Tasks

- [ ] Add workspace classes with parameterized SQL and no DDL.
- [ ] Add user navbar layout that keeps links compact and uses existing V3 theme.
- [ ] Add authenticated dashboard/projects/bibliography pages.
- [ ] Add project CRUD/content API endpoints with legacy response shapes.
- [ ] Add request-independent contract checks for files, quotas, sort allowlists, and path coverage.
- [ ] Run `./scripts/check-foundation.sh`, `./scripts/check-auth-public.sh`, and `./scripts/check-workspace.sh`.
- [ ] Commit as `feat: add user workspace foundation`.

## Handoff

This phase must be verified against a copied test database before real user data is switched to V3. Next phase should migrate bibliography generation and save/move/delete APIs, then report builder/export paths.
