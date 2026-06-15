# Babybib V3 Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create the Babybib V3 project foundation beside the current app with vanilla PHP, Tailwind, Preline, reusable core classes, shared layout components, and smoke-testable legacy-compatible entry points.

**Architecture:** V3 keeps legacy-compatible root PHP paths while moving reusable behavior into `src/Core`, `src/Config`, and `src/Views`. This first plan builds only the scaffold and shared foundation, so later plans can migrate auth, generate, user, report, and admin features without re-solving bootstrapping.

**Tech Stack:** Vanilla PHP 8+, Composer autoloading, PDO, PhpWord, PHPMailer, Tailwind CSS, Preline UI, npm build scripts, Apache/XAMPP-compatible `.htaccess`.

---

## Scope Check

The approved design covers multiple subsystems: scaffold/core, auth, generate, user library, report export, and admin console. This plan intentionally covers only Phase 0 and Phase 1 from the design spec:

- Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`.
- Add dependency and asset build foundations.
- Add env/config/database/session/security/response core files.
- Add shared layout and first Babybib Console shell.
- Add smoke-testable `index.php` and `api/health.php`.

Separate implementation plans should follow for:

- Auth and public pages.
- Generate workspace and APA7 parity.
- User dashboard/projects/library.
- Report builder and DOCX exports.
- Admin console.
- Final parity and production switch.

## File Structure

Create this structure under `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`:

```text
babybib-v3/
  .env.example
  .gitignore
  .htaccess
  README.md
  composer.json
  package.json
  postcss.config.js
  tailwind.config.js
  index.php
  api/
    health.php
  assets/
    css/
      input.css
      app.css
    js/
      app.js
    vendor/
      preline/
        preline.js
  src/
    Config/
      config.php
      env.php
    Core/
      Database.php
      Response.php
      Security.php
      Session.php
    Views/
      components/
        alert.php
      layout/
        footer.php
        head.php
        navbar-guest.php
        sidebar-admin.php
  tests/
    foundation_smoke.php
  scripts/
    check-foundation.sh
```

Do not copy the old project `.git` directory into V3.

## Task 1: Create Target Project Skeleton

**Files:**
- Create directory: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/.gitignore`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/.env.example`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/README.md`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/.htaccess`

- [ ] **Step 1: Verify the source design spec exists**

Run:

```bash
test -f /Applications/XAMPP/xamppfiles/htdocs/babybib/docs/superpowers/specs/2026-06-15-babybib-v3-compatibility-rebuild-design.md
```

Expected: exits with code `0`.

- [ ] **Step 2: Create the target directory**

Run with filesystem approval if required:

```bash
mkdir -p /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
mkdir -p api assets/css assets/js assets/vendor/preline src/Config src/Core src/Views/components src/Views/layout tests scripts
```

Expected: directories exist under `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`.

- [ ] **Step 3: Initialize a fresh V3 git repo**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git init
```

Expected output includes:

```text
Initialized empty Git repository
```

- [ ] **Step 4: Create `.gitignore`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/.gitignore`:

```gitignore
.DS_Store
.env
.env.local
.env.production
vendor/
node_modules/
logs/*.log
backups/*.sql.gz
backups/*.tar.gz
uploads/avatars/*
!uploads/avatars/.gitkeep
tmp/
*.cache
*.sql.gz
```

- [ ] **Step 5: Create `.env.example`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/.env.example`:

```dotenv
APP_NAME=Babybib
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/babybib-v3
APP_TIMEZONE=Asia/Bangkok

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=babybib_v3_test
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4

SESSION_LIFETIME=600

MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.test
MAIL_FROM_NAME=Babybib

GOOGLE_BOOKS_API_KEYS=
```

- [ ] **Step 6: Create `README.md`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/README.md`:

```markdown
# Babybib V3

Babybib V3 is a compatibility-first vanilla PHP rebuild of the existing Babybib APA 7 bibliography system.

## Local Setup

1. Copy `.env.example` to `.env`.
2. Point `.env` at a copied test database, not production data.
3. Run `composer install`.
4. Run `npm install`.
5. Run `npm run build`.
6. Open `http://localhost/babybib-v3`.

## Migration Rules

- Keep legacy-compatible paths for the first V3 release.
- Use a copied test database before real data.
- Preserve existing feature behavior before cleanup.
- Keep APA7 output parity as a release gate.
```

- [ ] **Step 7: Create `.htaccess`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/.htaccess`:

```apacheconf
Options -Indexes

<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

<FilesMatch "^\.env">
    Require all denied
</FilesMatch>

<FilesMatch "(composer\.json|composer\.lock|package\.json|package-lock\.json|tailwind\.config\.js|postcss\.config\.js)$">
    Require all denied
</FilesMatch>

<IfModule mod_rewrite.c>
    RewriteEngine On
</IfModule>
```

- [ ] **Step 8: Commit skeleton**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git add .gitignore .env.example README.md .htaccess
git commit -m "chore: scaffold babybib v3 project"
```

Expected: one commit with four files.

## Task 2: Add Composer And npm Foundations

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/composer.json`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/package.json`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/tailwind.config.js`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/postcss.config.js`

- [ ] **Step 1: Create `composer.json`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/composer.json`:

```json
{
  "name": "babybib/babybib-v3",
  "description": "Compatibility-first vanilla PHP rebuild of Babybib.",
  "type": "project",
  "require": {
    "php": ">=8.1",
    "phpmailer/phpmailer": "^6.10",
    "phpoffice/phpword": "^1.4"
  },
  "autoload": {
    "psr-4": {
      "Babybib\\": "src/"
    }
  },
  "scripts": {
    "check:syntax": "find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l"
  }
}
```

- [ ] **Step 2: Create `package.json`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/package.json`:

```json
{
  "name": "babybib-v3",
  "private": true,
  "scripts": {
    "build": "npm run build:css && npm run build:vendor",
    "build:css": "tailwindcss -i ./assets/css/input.css -o ./assets/css/app.css --minify",
    "build:vendor": "mkdir -p ./assets/vendor/preline && cp ./node_modules/preline/dist/preline.js ./assets/vendor/preline/preline.js",
    "dev": "tailwindcss -i ./assets/css/input.css -o ./assets/css/app.css --watch"
  },
  "dependencies": {
    "preline": "^2.7.0"
  },
  "devDependencies": {
    "@tailwindcss/forms": "^0.5.10",
    "@tailwindcss/typography": "^0.5.16",
    "autoprefixer": "^10.4.21",
    "postcss": "^8.5.6",
    "tailwindcss": "^3.4.17"
  }
}
```

- [ ] **Step 3: Create `tailwind.config.js`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/tailwind.config.js`:

```js
module.exports = {
  content: [
    './*.php',
    './api/**/*.php',
    './admin/**/*.php',
    './users/**/*.php',
    './src/Views/**/*.php',
    './assets/js/**/*.js',
    './node_modules/preline/dist/*.js'
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#8B5CF6',
          dark: '#7C3AED',
          darker: '#6D28D9',
          soft: '#F5F3FF',
          border: '#DDD6FE'
        },
        ink: {
          DEFAULT: '#171322',
          muted: '#6B6478',
          faint: '#9A94A8'
        },
        surface: {
          DEFAULT: '#FBFAFD',
          raised: '#F7F5FA',
          panel: '#FEFDFF',
          sidebar: '#171322'
        },
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#3B82F6'
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
        thai: ['Tahoma', 'Noto Sans Thai', 'sans-serif'],
        brand: ['Comfortaa', 'Inter', 'system-ui', 'sans-serif']
      },
      boxShadow: {
        panel: '0 1px 2px rgba(23, 19, 34, 0.05)',
        popover: '0 16px 40px rgba(23, 19, 34, 0.14)'
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('preline/plugin')
  ]
};
```

- [ ] **Step 4: Create `postcss.config.js`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/postcss.config.js`:

```js
module.exports = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {}
  }
};
```

- [ ] **Step 5: Install PHP dependencies**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
composer install
```

Expected:

```text
Generating autoload files
```

- [ ] **Step 6: Install Node dependencies**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
npm install
```

Expected:

```text
added
```

- [ ] **Step 7: Commit dependency manifests**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git add composer.json composer.lock package.json package-lock.json tailwind.config.js postcss.config.js
git commit -m "chore: add php and tailwind dependencies"
```

Expected: one commit with dependency manifests and lock files.

## Task 3: Add Asset Pipeline

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/assets/css/input.css`
- Generated by build: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/assets/css/app.css`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/assets/js/app.js`
- Generated by build: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/assets/vendor/preline/preline.js`

- [ ] **Step 1: Create Tailwind input CSS**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/assets/css/input.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    color-scheme: light;
  }

  html {
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  body {
    @apply bg-surface text-ink font-sans;
  }

  :focus-visible {
    @apply outline-none ring-2 ring-primary ring-offset-2 ring-offset-surface;
  }
}

@layer components {
  .bb-shell {
    @apply min-h-screen bg-surface text-ink;
  }

  .bb-panel {
    @apply rounded-lg border border-slate-200 bg-surface-panel shadow-panel;
  }

  .bb-button-primary {
    @apply inline-flex min-h-9 items-center justify-center gap-2 rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark disabled:cursor-not-allowed disabled:opacity-50;
  }

  .bb-button-secondary {
    @apply inline-flex min-h-9 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-surface-panel px-3 py-2 text-sm font-semibold text-ink transition hover:bg-surface-raised disabled:cursor-not-allowed disabled:opacity-50;
  }

  .bb-input {
    @apply block w-full rounded-lg border-slate-200 bg-surface-panel text-sm text-ink placeholder:text-ink-faint focus:border-primary focus:ring-primary;
  }

  .bb-label {
    @apply mb-1.5 block text-sm font-medium text-ink;
  }

  .bb-muted {
    @apply text-sm text-ink-muted;
  }
}
```

- [ ] **Step 2: Create app JavaScript**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/assets/js/app.js`:

```js
(() => {
  const initPreline = () => {
    if (window.HSStaticMethods && typeof window.HSStaticMethods.autoInit === 'function') {
      window.HSStaticMethods.autoInit();
    }
  };

  const bindDismissibleAlerts = () => {
    document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
      button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-dismiss-alert');
        const target = targetId ? document.getElementById(targetId) : button.closest('[role="alert"]');
        if (target) {
          target.remove();
        }
      });
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    initPreline();
    bindDismissibleAlerts();
  });
})();
```

- [ ] **Step 3: Build CSS and vendor JS**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
npm run build
```

Expected:

```text
Done in
```

Also verify these files exist:

```bash
test -f assets/css/app.css
test -f assets/vendor/preline/preline.js
```

Expected: both commands exit with code `0`.

- [ ] **Step 4: Commit asset pipeline**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git add assets/css/input.css assets/css/app.css assets/js/app.js assets/vendor/preline/preline.js
git commit -m "feat: add babybib console asset pipeline"
```

Expected: one commit with source CSS, compiled CSS, app JS, and vendored Preline JS.

## Task 4: Add Core PHP Infrastructure

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Config/env.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Config/config.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Database.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Response.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Security.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Session.php`

- [ ] **Step 1: Create env loader**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Config/env.php`:

```php
<?php

declare(strict_types=1);

namespace Babybib\Config;

final class Env
{
    /** @var array<string, string> */
    private static array $values = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            $value = trim($value, "\"'");
            self::$values[$key] = $value;

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return self::$values[$key] ?? $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default): int
    {
        $value = self::get($key);
        if ($value === null || $value === '' || !is_numeric($value)) {
            return $default;
        }

        return (int) $value;
    }
}
```

- [ ] **Step 2: Create response helper**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Response.php`:

```php
<?php

declare(strict_types=1);

namespace Babybib\Core;

final class Response
{
    /** @param array<string, mixed> $payload */
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }
}
```

- [ ] **Step 3: Create database wrapper**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Database.php`:

```php
<?php

declare(strict_types=1);

namespace Babybib\Core;

use Babybib\Config\Env;
use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = Env::get('DB_HOST', '127.0.0.1');
        $port = Env::get('DB_PORT', '3306');
        $database = Env::get('DB_DATABASE', 'babybib_v3_test');
        $charset = Env::get('DB_CHARSET', 'utf8mb4');
        $username = Env::get('DB_USERNAME', 'root');
        $password = Env::get('DB_PASSWORD', '');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset);

        try {
            self::$connection = new PDO($dsn, (string) $username, (string) $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            ]);
        } catch (PDOException $exception) {
            error_log('[DB] Connection failed: ' . $exception->getMessage());
            throw $exception;
        }

        return self::$connection;
    }
}
```

- [ ] **Step 4: Create security helper**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Security.php`:

```php
<?php

declare(strict_types=1);

namespace Babybib\Core;

final class Security
{
    public static function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }

    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function csrfToken(): string
    {
        Session::start();

        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        Session::start();

        return is_string($token)
            && isset($_SESSION['csrf_token'])
            && is_string($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requestCsrfToken(): ?string
    {
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (is_string($header) && $header !== '') {
            return trim($header);
        }

        $postToken = $_POST['csrf_token'] ?? $_POST['_csrf'] ?? null;
        return is_string($postToken) ? trim($postToken) : null;
    }

    public static function requireCsrf(): void
    {
        if (!self::verifyCsrf(self::requestCsrfToken())) {
            Response::json([
                'success' => false,
                'error' => 'Invalid CSRF token',
            ], 419);
        }
    }
}
```

- [ ] **Step 5: Create session helper**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Core/Session.php`:

```php
<?php

declare(strict_types=1);

namespace Babybib\Core;

use Babybib\Config\Env;

final class Session
{
    public static function configure(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $lifetime = Env::int('SESSION_LIFETIME', 600);

        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) $lifetime);

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function start(): void
    {
        self::configure();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isAuthenticated(): bool
    {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            Response::redirect(app_url('/login.php'));
        }
    }

    public static function isAdmin(): bool
    {
        self::start();
        return (($_SESSION['role'] ?? '') === 'admin') || !empty($_SESSION['is_admin']);
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();

        if (!self::isAdmin()) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }
}
```

- [ ] **Step 6: Create config bootstrap**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Config/config.php`:

```php
<?php

declare(strict_types=1);

use Babybib\Config\Env;
use Babybib\Core\Security;
use Babybib\Core\Session;

$rootPath = dirname(__DIR__, 2);
$autoloadPath = $rootPath . '/vendor/autoload.php';

if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

Env::load($rootPath . '/.env');

date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Bangkok') ?? 'Asia/Bangkok');

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $rootPath);
}

if (!defined('APP_NAME')) {
    define('APP_NAME', Env::get('APP_NAME', 'Babybib') ?? 'Babybib');
}

if (!defined('APP_URL')) {
    define('APP_URL', rtrim(Env::get('APP_URL', 'http://localhost/babybib-v3') ?? 'http://localhost/babybib-v3', '/'));
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', Env::bool('APP_DEBUG', false));
}

Session::configure();
Security::sendHeaders();

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return Security::escape($value);
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return APP_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        return app_url('/assets/' . ltrim($path, '/'));
    }
}
```

- [ ] **Step 7: Run PHP syntax checks**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
composer dump-autoload
composer run check:syntax
```

Expected: every PHP file prints `No syntax errors detected`.

- [ ] **Step 8: Commit core infrastructure**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git add src/Config src/Core
git commit -m "feat: add core php infrastructure"
```

Expected: one commit with config, env, database, response, security, and session files.

## Task 5: Add Shared Layout Components

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/layout/head.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/layout/navbar-guest.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/layout/sidebar-admin.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/layout/footer.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/components/alert.php`

- [ ] **Step 1: Create `head.php`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/layout/head.php`:

```php
<?php

declare(strict_types=1);

use Babybib\Core\Security;

$pageTitle = isset($title) && is_string($title) ? $title : APP_NAME;
$bodyClass = isset($bodyClass) && is_string($bodyClass) ? $bodyClass : '';
$csrfToken = Security::csrfToken();
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo h($csrfToken); ?>">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo h(asset_url('css/app.css')); ?>">
</head>
<body class="bb-shell <?php echo h($bodyClass); ?>">
```

- [ ] **Step 2: Create guest navbar**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/layout/navbar-guest.php`:

```php
<?php

declare(strict_types=1);

$activePath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$navItems = [
    ['label' => 'หน้าหลัก', 'href' => app_url('/index.php'), 'match' => '/index.php'],
    ['label' => 'สร้างบรรณานุกรม', 'href' => app_url('/generate.php'), 'match' => '/generate.php'],
    ['label' => 'คู่มือ', 'href' => app_url('/start.php'), 'match' => '/start.php'],
];
?>
<header class="sticky top-0 z-40 border-b border-slate-200 bg-surface/95 backdrop-blur">
    <nav class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Main navigation">
        <a href="<?php echo h(app_url('/index.php')); ?>" class="flex items-center gap-2">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-primary text-sm font-bold text-white">B</span>
            <span class="font-brand text-lg font-bold text-ink">Babybib</span>
        </a>

        <div class="hidden items-center gap-1 md:flex">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = str_ends_with($activePath, $item['match']); ?>
                <a
                    href="<?php echo h($item['href']); ?>"
                    class="rounded-lg px-3 py-2 text-sm font-medium transition <?php echo $isActive ? 'bg-primary-soft text-primary-dark' : 'text-ink-muted hover:bg-surface-raised hover:text-ink'; ?>"
                >
                    <?php echo h($item['label']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?php echo h(app_url('/login.php')); ?>" class="bb-button-secondary">เข้าสู่ระบบ</a>
            <a href="<?php echo h(app_url('/register.php')); ?>" class="bb-button-primary">สมัครใช้งาน</a>
        </div>
    </nav>
</header>
```

- [ ] **Step 3: Create admin sidebar**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/layout/sidebar-admin.php`:

```php
<?php

declare(strict_types=1);

$activePath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$adminItems = [
    ['label' => 'Dashboard', 'href' => app_url('/admin/index.php'), 'match' => '/admin/index.php'],
    ['label' => 'Users', 'href' => app_url('/admin/users.php'), 'match' => '/admin/users.php'],
    ['label' => 'Bibliographies', 'href' => app_url('/admin/bibliographies.php'), 'match' => '/admin/bibliographies.php'],
    ['label' => 'Projects', 'href' => app_url('/admin/projects.php'), 'match' => '/admin/projects.php'],
    ['label' => 'Settings', 'href' => app_url('/admin/settings.php'), 'match' => '/admin/settings.php'],
];
?>
<aside class="flex min-h-screen w-64 flex-col bg-surface-sidebar p-3 text-slate-200">
    <a href="<?php echo h(app_url('/admin/index.php')); ?>" class="mb-4 flex items-center gap-3 rounded-lg px-2 py-2">
        <span class="grid h-9 w-9 place-items-center rounded-lg bg-primary text-sm font-bold text-white">B</span>
        <span>
            <span class="block font-brand text-base font-bold text-white">Babybib</span>
            <span class="block text-xs text-slate-400">Console</span>
        </span>
    </a>

    <nav class="grid gap-1" aria-label="Admin navigation">
        <?php foreach ($adminItems as $item): ?>
            <?php $isActive = str_ends_with($activePath, $item['match']); ?>
            <a
                href="<?php echo h($item['href']); ?>"
                class="rounded-lg px-3 py-2 text-sm font-medium transition <?php echo $isActive ? 'bg-primary text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white'; ?>"
            >
                <?php echo h($item['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="mt-auto rounded-lg border border-white/10 bg-white/5 p-3 text-xs text-slate-400">
        V3 test environment
    </div>
</aside>
```

- [ ] **Step 4: Create footer**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/layout/footer.php`:

```php
<?php

declare(strict_types=1);
?>
<footer class="border-t border-slate-200 bg-surface-panel">
    <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-sm text-ink-muted sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <p>&copy; <?php echo date('Y'); ?> Babybib. All rights reserved.</p>
        <div class="flex gap-4">
            <a class="hover:text-ink" href="<?php echo h(app_url('/privacy.php')); ?>">Privacy</a>
            <a class="hover:text-ink" href="<?php echo h(app_url('/terms.php')); ?>">Terms</a>
        </div>
    </div>
</footer>
<script src="<?php echo h(asset_url('vendor/preline/preline.js')); ?>"></script>
<script src="<?php echo h(asset_url('js/app.js')); ?>"></script>
</body>
</html>
```

- [ ] **Step 5: Create alert component**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/src/Views/components/alert.php`:

```php
<?php

declare(strict_types=1);

$alertId = 'alert-' . bin2hex(random_bytes(4));
$variant = isset($variant) && is_string($variant) ? $variant : 'info';
$message = isset($message) && is_string($message) ? $message : '';

$classes = [
    'info' => 'border-info/20 bg-blue-50 text-blue-900',
    'success' => 'border-success/20 bg-emerald-50 text-emerald-900',
    'warning' => 'border-warning/20 bg-amber-50 text-amber-900',
    'danger' => 'border-danger/20 bg-red-50 text-red-900',
];
?>
<div id="<?php echo h($alertId); ?>" role="alert" class="flex items-start justify-between gap-3 rounded-lg border px-4 py-3 text-sm <?php echo h($classes[$variant] ?? $classes['info']); ?>">
    <p><?php echo h($message); ?></p>
    <button type="button" class="font-semibold opacity-70 transition hover:opacity-100" data-dismiss-alert="<?php echo h($alertId); ?>">
        ปิด
    </button>
</div>
```

- [ ] **Step 6: Run syntax checks**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
composer run check:syntax
```

Expected: every PHP file prints `No syntax errors detected`.

- [ ] **Step 7: Commit layout components**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git add src/Views
git commit -m "feat: add shared babybib console layout"
```

Expected: one commit with shared view files.

## Task 6: Add First Legacy-Compatible Entry Points

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/index.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/api/health.php`

- [ ] **Step 1: Create `index.php`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/index.php`:

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/src/Config/config.php';

$title = APP_NAME . ' V3';
$bodyClass = 'min-h-screen';

require APP_ROOT . '/src/Views/layout/head.php';
require APP_ROOT . '/src/Views/layout/navbar-guest.php';
?>
<main class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_420px] lg:px-8 lg:py-14">
    <section class="flex flex-col justify-center">
        <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary-dark">Babybib V3</p>
        <h1 class="max-w-3xl text-4xl font-bold tracking-normal text-ink sm:text-5xl">
            สร้างบรรณานุกรม APA 7 ด้วยระบบใหม่ที่ยังรองรับข้อมูลเดิม
        </h1>
        <p class="mt-5 max-w-2xl text-base leading-7 text-ink-muted">
            V3 เป็น rebuild แบบ compatibility-first: โครงสร้างใหม่, UI ใหม่, vanilla PHP เหมือนเดิม, และใช้ฐานข้อมูลเดิมได้หลังผ่านการตรวจเทียบครบถ้วน
        </p>
        <div class="mt-7 flex flex-wrap gap-3">
            <a href="<?php echo h(app_url('/generate.php')); ?>" class="bb-button-primary">เริ่มสร้างบรรณานุกรม</a>
            <a href="<?php echo h(app_url('/start.php')); ?>" class="bb-button-secondary">ดูคู่มือ</a>
        </div>
    </section>

    <section class="bb-panel p-4">
        <div class="rounded-lg border border-primary-border bg-primary-soft p-4">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-primary-dark">Foundation status</p>
                    <h2 class="mt-1 text-xl font-bold text-ink">Ready for Phase 2</h2>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-primary-dark shadow-panel">Test DB first</span>
            </div>
            <div class="grid gap-2 text-sm text-ink-muted">
                <div class="rounded-lg bg-white px-3 py-2">Legacy paths remain the public contract.</div>
                <div class="rounded-lg bg-white px-3 py-2">Core PHP classes live in <code>src/Core</code>.</div>
                <div class="rounded-lg bg-white px-3 py-2">Tailwind and Preline assets are build-based.</div>
            </div>
        </div>
    </section>
</main>
<?php
require APP_ROOT . '/src/Views/layout/footer.php';
```

- [ ] **Step 2: Create `api/health.php`**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/api/health.php`:

```php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Config/config.php';

use Babybib\Core\Response;

Response::json([
    'success' => true,
    'app' => APP_NAME,
    'environment' => \Babybib\Config\Env::get('APP_ENV', 'local'),
    'timestamp' => date(DATE_ATOM),
]);
```

- [ ] **Step 3: Run syntax checks**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
composer run check:syntax
```

Expected: every PHP file prints `No syntax errors detected`.

- [ ] **Step 4: Run local health check**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
php -S 127.0.0.1:8093 -t . > /tmp/babybib-v3-foundation.log 2>&1 &
curl -s http://127.0.0.1:8093/api/health.php
```

Expected JSON contains:

```json
{"success":true,"app":"Babybib"
```

Stop the PHP server after the check:

```bash
pkill -f "php -S 127.0.0.1:8093 -t ."
```

- [ ] **Step 5: Commit entry points**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git add index.php api/health.php
git commit -m "feat: add foundation entry points"
```

Expected: one commit with `index.php` and `api/health.php`.

## Task 7: Add Foundation Smoke Test Script

**Files:**
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/tests/foundation_smoke.php`
- Create: `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/scripts/check-foundation.sh`

- [ ] **Step 1: Create PHP smoke test**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/tests/foundation_smoke.php`:

```php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Config/config.php';

use Babybib\Core\Security;
use Babybib\Core\Session;

Session::start();

$checks = [
    'APP_ROOT defined' => defined('APP_ROOT') && is_dir(APP_ROOT),
    'APP_URL defined' => defined('APP_URL') && APP_URL !== '',
    'asset_url works' => str_contains(asset_url('css/app.css'), '/assets/css/app.css'),
    'app_url works' => str_ends_with(app_url('/generate.php'), '/generate.php'),
    'csrf token length' => strlen(Security::csrfToken()) === 64,
];

$failed = [];
foreach ($checks as $label => $passed) {
    if (!$passed) {
        $failed[] = $label;
    }
}

if ($failed !== []) {
    fwrite(STDERR, 'Foundation smoke test failed: ' . implode(', ', $failed) . PHP_EOL);
    exit(1);
}

echo 'Foundation smoke test passed' . PHP_EOL;
```

- [ ] **Step 2: Create shell verification script**

Create `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3/scripts/check-foundation.sh`:

```bash
#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

composer run check:syntax
npm run build
php tests/foundation_smoke.php

echo "Foundation checks passed"
```

- [ ] **Step 3: Make the script executable**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
chmod +x scripts/check-foundation.sh
```

Expected: command exits with code `0`.

- [ ] **Step 4: Run the foundation checks**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
./scripts/check-foundation.sh
```

Expected output includes:

```text
Foundation smoke test passed
Foundation checks passed
```

- [ ] **Step 5: Commit smoke tests**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git add tests/foundation_smoke.php scripts/check-foundation.sh
git commit -m "test: add foundation smoke checks"
```

Expected: one commit with the smoke test and check script.

## Task 8: Final Foundation Review

**Files:**
- Inspect all files created in `/Applications/XAMPP/xamppfiles/htdocs/babybib-v3`
- No new source files unless a verification failure requires a fix

- [ ] **Step 1: Verify clean V3 git status**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git status --short
```

Expected: no output.

- [ ] **Step 2: Verify commit history**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
git log --oneline --max-count=5
```

Expected commit subjects include:

```text
test: add foundation smoke checks
feat: add foundation entry points
feat: add shared babybib console layout
feat: add core php infrastructure
feat: add babybib console asset pipeline
```

- [ ] **Step 3: Run final checks**

Run:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/babybib-v3
./scripts/check-foundation.sh
```

Expected:

```text
Foundation checks passed
```

- [ ] **Step 4: Record next phase handoff**

Create no new file. Add this note to the final implementation report:

```text
Foundation complete. Next plan should migrate Auth and Public pages while preserving legacy-compatible paths and API response shapes.
```

