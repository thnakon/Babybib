# Babybib V3 Auth And Public Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate Babybib public auth flows into V3 with legacy-compatible paths, response shapes, session keys, and database fields while keeping request-time work fast.

**Architecture:** Keep root PHP entry points for compatibility and move reusable behavior into small classes under `src/Core` and `src/Auth`. Auth APIs share one JSON request/parser path, one response path, and one `AuthService` for login/register/logout/captcha/password reset. V3 must not run schema-changing DDL inside normal requests; schema compatibility checks belong in scripts/tests.

**Tech Stack:** Vanilla PHP 8.1+, PDO, Composer PSR-4, Tailwind CSS, Preline, browser `fetch`, existing MySQL schema.

---

## Legacy Contract Notes

- Login form posts JSON to `/api/auth/login.php` with `login`, `password`, and optional `remember`; success returns `success`, `message`, `redirect`, and `user`.
- Login accepts username or email from `users.username` / `users.email`, requires `users.is_active = 1`, uses `password_verify()`, tracks failed attempts by IP in `login_attempts`, logs activity into `activity_logs`, and redirects admins to `/admin/index.php`.
- Session keys to preserve: `user_id`, `username`, `user_role`, `user_name`, `user_language`, `last_activity`, `csrf_token`, and `captcha_answer`.
- Register posts JSON to `/api/auth/register.php`, requires CSRF, validates username/email/password/captcha, writes `users`, optionally writes `email_verifications`, and returns `requires_verification`, `user_id`, and `email`.
- Captcha endpoint returns `{ success: true, captcha: "N + N = ?" }` and stores the numeric answer in `$_SESSION['captcha_answer']`.
- Forgot/reset password use `users.token` and `users.token_expiry`, with HMAC token hashing and legacy plaintext token support.
- Email verification stores hashed or legacy plaintext six-digit codes in `email_verifications.code`, marks `used = 1`, updates `users.is_verified = 1`, and auto-logs the user in.
- Runtime DDL found in legacy auth (`ALTER TABLE` / `CREATE TABLE IF NOT EXISTS`) must not be copied into request handlers.

## File Structure

Create or modify these files under `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`:

```text
src/Core/
  Request.php
  Validator.php
src/Auth/
  AuthService.php
  PasswordPolicy.php
  UserSession.php
api/auth/
  get-captcha.php
  login.php
  logout.php
  register.php
  forgot-password.php
  reset-password.php
  verify-code.php
login.php
register.php
forgot-password.php
reset-password.php
verify.php
tests/auth_contract.php
scripts/check-auth-public.sh
```

## Task 1: Add Core Request And Validation Helpers

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Request.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Validator.php`
- Test: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/tests/auth_contract.php`

- [ ] **Step 1: Create `Request.php`**

Create a final class with:
- `json(): array` that decodes `php://input`, returns an empty array for empty body, and returns `400` JSON on malformed JSON.
- `requireMethod(string $method): void` that returns `405` JSON when method does not match.
- `ip(): string` returning `$_SERVER['REMOTE_ADDR'] ?? 'unknown'`.

- [ ] **Step 2: Create `Validator.php`**

Create a final class with:
- `email(string $email): ?string`
- `username(string $username): ?string`
- `password(string $password): ?string`
- `required(string $value, string $message): ?string`

Validation must preserve legacy rules where practical: username 3-50 chars, letters/numbers/underscore/dot/hyphen; password at least 8 chars.

- [ ] **Step 3: Add contract checks**

Add `tests/auth_contract.php` checks for helper classes existing, password policy behavior, and session keys written by `UserSession` after Task 2.

## Task 2: Add Auth Domain Classes

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Auth/UserSession.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Auth/PasswordPolicy.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Auth/AuthService.php`
- Modify: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Session.php`

- [ ] **Step 1: Add `UserSession.php`**

Implement `set(array $user): void` to call `session_regenerate_id(true)` and set legacy keys: `user_id`, `username`, `user_role`, `user_name`, `user_language`, `last_activity`.

- [ ] **Step 2: Add `PasswordPolicy.php`**

Implement `validate(string $password): ?string` returning Thai error text for passwords shorter than 8 chars.

- [ ] **Step 3: Add `AuthService.php`**

Implement methods:
- `login(string $login, string $password, bool $remember): array`
- `logout(): array`
- `captcha(): array`
- `register(array $input): array`
- `forgotPassword(string $email): array`
- `resetPassword(string $token, string $password): array`
- `verifyCode(int $userId, string $code): array`

Keep SQL parameterized. Do not run DDL in these methods.

- [ ] **Step 4: Align `Session::isAdmin()`**

Update `Session::isAdmin()` to check `user_role` first while keeping existing `role`/`is_admin` fallback.

## Task 3: Add Legacy-Compatible Auth API Entry Points

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/api/auth/get-captcha.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/api/auth/login.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/api/auth/logout.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/api/auth/register.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/api/auth/forgot-password.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/api/auth/reset-password.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/api/auth/verify-code.php`

- [ ] **Step 1: Add each endpoint**

Each endpoint must include `src/Config/config.php`, call `Session::start()`, require the legacy HTTP method, call CSRF for state-changing JSON endpoints except login only if keeping legacy behavior exactly, call `AuthService`, and return `Response::json()`.

- [ ] **Step 2: Preserve response shape**

Responses must keep legacy keys: `success`, `message` or `error`, `redirect`, `requires_verification`, `user_id`, `email`, and `captcha` where applicable.

## Task 4: Add Modern Public Auth Pages

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/login.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/register.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/forgot-password.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/reset-password.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/verify.php`
- Modify: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/assets/js/app.js`

- [ ] **Step 1: Build shared auth page pattern**

Use the V3 layout files and utility classes. Keep UI in Babybib Console style: compact cards, restrained purple accent, no heavy marketing hero.

- [ ] **Step 2: Add JS helpers**

Add a small `window.Babybib` object with `csrf()`, `postJson(url, payload)`, `showFormMessage(form, type, message)`, and `refreshCaptcha()` helpers.

- [ ] **Step 3: Wire forms**

Forms must submit to the compatible API paths and honor redirects returned by API.

## Task 5: Add Auth/Public Verification

**Files:**
- Modify: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/scripts/check-foundation.sh`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/scripts/check-auth-public.sh`
- Modify: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/tests/auth_contract.php`

- [ ] **Step 1: Add static contract checks**

Check that all legacy auth/public paths exist and PHP syntax passes.

- [ ] **Step 2: Add request-independent class checks**

Check `PasswordPolicy`, `Validator`, `UserSession`, and token hashing logic without connecting to the database.

- [ ] **Step 3: Run checks**

Run:

```bash
./scripts/check-foundation.sh
./scripts/check-auth-public.sh
```

Expected output includes:

```text
Foundation checks passed
Auth/public checks passed
```

## Task 6: Commit And Handoff

**Files:**
- Inspect all files changed in `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`

- [ ] **Step 1: Verify clean checks**

Run `git status --short`, `./scripts/check-foundation.sh`, and `./scripts/check-auth-public.sh`.

- [ ] **Step 2: Commit**

Commit with:

```bash
git add .
git commit -m "feat: add legacy-compatible auth public foundation"
```

- [ ] **Step 3: Handoff note**

Final report must include:

```text
Auth/Public phase should be verified against a copied test database before enabling real user login.
Next phase: user dashboard, projects, and bibliography library.
```
