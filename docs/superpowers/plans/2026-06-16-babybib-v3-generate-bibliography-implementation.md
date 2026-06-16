# Babybib V3 Generate And Bibliography Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the V3 generate workspace and legacy-compatible bibliography management APIs for creating, updating, deleting, moving, and previewing saved bibliographies.

**Architecture:** Keep `/generate.php` and `/api/bibliography/*` as public contracts. Move APA preview formatting and persistence into `src/Bibliography` classes. This phase intentionally avoids external smart search and deep APA edge cases; it creates a fast, testable save/manage foundation that preserves database fields and response shapes.

**Tech Stack:** Vanilla PHP 8.1+, PDO, Composer PSR-4, Tailwind CSS, browser fetch, existing MySQL schema.

---

## Scope

- Add modern generate page with resource type selector, key fields, live preview, and save form.
- Support editing via `/generate.php?edit=<id>` for authenticated users.
- Add APIs:
  - `/api/bibliography/create.php`
  - `/api/bibliography/delete.php`
  - `/api/bibliography/move.php`
  - `/api/bibliography/update_project.php`
  - `/api/bibliography/preview.php`
- Preserve DB fields: `resource_type_id`, `project_id`, `data`, `bibliography_text`, `citation_parenthetical`, `citation_narrative`, `language`, `author_sort_key`, `year`, `year_suffix`.
- Keep guests able to generate/copy preview, but require login for saving.
- Do not copy legacy `generate.php` monolith. No request-time DDL.

## Handoff

Next phase should migrate smart search (`api/smart_search.php`) and high-fidelity APA 7 templates per resource type, using golden samples from the legacy output.
