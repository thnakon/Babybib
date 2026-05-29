# Babybib V3 — Migration Plan
## PHP Vanilla + Tailwind CSS + Preline UI

> **เป้าหมาย:** ลื้อโฟลเดอร์ใหม่ทั้งหมด จัดโครงสร้างใหม่ เก็บฟีเจอร์เดิมครบ และอัปเกรด UI/UX ด้วย Tailwind CSS + Preline UI

---

## 1. สิ่งที่มีอยู่ (Current State Analysis)

### 1.1 Tech Stack เดิม

| ชั้น | ของเดิม |
|---|---|
| Backend | PHP Vanilla (ไม่มี Framework) |
| Database | MySQL (PDO) |
| CSS | Custom CSS ล้วน (main.css 1,970+ บรรทัด + components.css + animations.css + หน้า-specific) |
| JS | Vanilla JS (`main.js`, `apa7-formatter.js`, `tour.js`) |
| Icons | Font Awesome 6.4, Lucide Icons |
| Fonts | Google Fonts: Comfortaa (brand), Inter (UI), Tahoma (Thai body) |
| UI Library | ไม่มี — custom ทุกอย่าง |
| Tailwind | ใช้ CDN (production warning suppress) |
| Alpine.js | CDN `3.x` |
| Email | PHPMailer (via Composer) |
| Export | PhpWord (Composer) สำหรับ .docx template |
| Search | Vanilla Rate-limiting + File-based cache (`/tmp/babybib_search_cache/`) |

### 1.2 Design System เดิม

**สีหลัก (Primary Palette)**
```
--primary:          #8B5CF6   (Violet 500)
--primary-dark:     #7C3AED   (Violet 600)
--primary-light:    #EDE9FE   (Violet 100)
--primary-gradient: linear-gradient(135deg, #6366F1 0%, #8B5CF6 45%, #D946EF 100%)
--brand-gradient:   linear-gradient(to right, #180d36, #8B5CF6, #CF23CF, #180d36)
```

**สีสถานะ**
```
--success: #10B981   --warning: #F59E0B
--danger:  #EF4444   --info:    #3B82F6
```

**ฟอนต์**
- Thai body: `Tahoma, sans-serif`
- English body: `Inter, system-ui, sans-serif`
- Brand logo: `Comfortaa` (Google Fonts, weight 700) — gradient text
- Logo text: "Babybib" แบบ gradient ม่วง → ชมพู

**Shadow / Radius**
- Primary shadow: `0 4px 14px 0 rgba(139, 92, 246, 0.3)`
- Navbar: `backdrop-filter: blur(10px)` + sticky
- Cards: `border-radius: 1rem` (16px)

### 1.3 Features ครบทั้งหมด

#### 🔐 Authentication System
- [x] Register (username, name, surname, email, password, org_type, org_name, province, is_lis_cmu)
- [x] Email Verification (6-digit OTP code)
- [x] Login + Rate limiting (login_attempts table)
- [x] Forgot Password (OTP email)
- [x] Reset Password
- [x] Profile Update (avatar upload .webp/.jpg/.png, org info)
- [x] Delete Account (soft confirmation)
- [x] Session timeout (600 sec default)
- [x] CSRF Token (all state-changing requests)
- [x] Security Headers (CSP, X-Frame, etc.)
- [x] CAPTCHA (`/api/auth/get-captcha.php`)

#### 📚 Bibliography Core (APA7 Thai Edition)
- [x] สร้างบรรณานุกรม (generate.php) — หน้าหลัก split-view (form | preview)
- [x] **30+ ประเภททรัพยากร** ทุก Category:
  - Books (5 ประเภท): book, book_series, book_chapter, ebook_doi, ebook_no_doi
  - Journals (5): journal_article, ejournal_doi, ejournal_no_doi, ejournal_print, ejournal_only
  - Reference (4): dictionary, dictionary_online, encyclopedia, encyclopedia_online
  - Newspapers (2): newspaper_print, newspaper_online
  - Reports (4): report, research_report, government_report, institutional_report
  - Conferences (3): conference_proceeding, conference_no_proceeding, conference_presentation
  - Theses (3): thesis_unpublished, thesis_website, thesis_database
  - Online (5): webpage, social_media, royal_gazette, patent_online, personal_communication
  - Media (6): infographic, slides_online, webinar, youtube_video, podcast, podcast_series
  - Interview (สัมภาษณ์)
  - Others (1): ai_generated
- [x] **Author Types** (9): general, anonymous, pseudonym, royal, nobility, monk, editor, organization, translator
- [x] **Secondary Source (Cited In)** — อ้างอิงทุติยภูมิ
- [x] **Smart Search** — Auto-detect ISBN / DOI / URL / Keyword
  - Thai Layer: Google Books Thai, Semantic Scholar, CrossRef
  - Global Layer: Open Library, Google Books, CrossRef, OpenAlex, Web Scraper
  - Rate limit: 30 req/min/IP + File cache
  - Google Books API Keys rotation (3 keys)
- [x] **Live Preview** — real-time บรรณานุกรม + in-text citation (parenthetical + narrative)
- [x] **Copy to Clipboard** (บรรณานุกรม + citation)
- [x] **Edit mode** (แก้ไขรายการที่มีอยู่)
- [x] Year validation (flexible)
- [x] Language toggle ไทย/English (bibliography output language)

#### 📂 Projects & Library
- [x] สร้าง/แก้ไข/ลบ Projects (max 30/user)
- [x] เพิ่ม bibliography เข้า Project (max 300 bib/user)
- [x] ย้าย bibliography ระหว่าง Project
- [x] Bibliography list (กรอง, ค้นหา, sort)
- [x] Export Project เป็น .txt / .docx (sorted APA7)

#### 📄 Report Templates (DOCX Export)
- [x] **template_academic_general.docx** — รายงานวิชาการทั่วไป (ใส่ bibliography อัตโนมัติ)
- [x] **template_academic_general_logo.docx** — รายงานพร้อมโลโก้
- [x] **template_academic_research.docx** — รายงานวิจัย/วิทยานิพนธ์
- [x] Internship report template
- [x] Upload โลโก้สถาบันเองได้ (CMU logo default)
- [x] เลือก Project ที่ต้องการแนบบรรณานุกรม

#### 🔄 Sort & Manage
- [x] sort.php — เรียงบรรณานุกรมตาม APA7 (ผู้แต่ง/ปี/ชื่อเรื่อง)
- [x] summary.php — แสดงบรรณานุกรมล่าสุด + export

#### 📊 User Dashboard
- [x] Stats (bib count, project count)
- [x] Recent bibliographies (6 รายการ)
- [x] Activity History (7 วัน)
- [x] Announcement toast (admin push)

#### 👁️ Onboarding Tour
- [x] Interactive tour (BabybibTour class) สำหรับผู้ใช้ใหม่
- [x] Steps: เลือกประเภท → กรอกข้อมูล → preview → บันทึก
- [x] localStorage flag (`babybib_tour_completed`)

#### ℹ️ Help Pages
- [x] help-author.php — คำแนะนำผู้แต่ง
- [x] help-place.php — คำแนะนำสถานที่
- [x] help-publisher.php — คำแนะนำสำนักพิมพ์
- [x] start.php — คู่มือเริ่มต้น
- [x] privacy.php, terms.php — กฎหมาย/นโยบาย

#### 🛡️ Admin Panel (`/admin/`)
- [x] Dashboard (stats: users, bibs, projects, visits)
- [x] User Management (list, create, edit, toggle active, delete)
- [x] Bibliography Management (view all, edit, delete)
- [x] Project Management
- [x] Announcements (CRUD, active/inactive, date range)
- [x] Feedback Management (pending/read/resolved + admin response)
- [x] System Settings (site name, limits, email config, provinces)
- [x] Backup (create .sql.gz, download, delete)
- [x] Logs (activity logs)
- [x] Admin Notifications (unread badge)

#### 📧 Email System
- [x] PHPMailer via Composer
- [x] Email verification OTP
- [x] Password reset OTP
- [x] Configurable SMTP via Admin Settings (ไม่ต้อง edit .env)
- [x] Test email button

#### 🌐 Internationalization
- [x] ไทย/English (lang/th.php, lang/en.php)
- [x] Language switcher (pill style)
- [x] body.lang-en font override
- [x] bibliography output language แยกต่างหาก (ไม่ต้องตาม UI language)

#### 🔒 Security
- [x] CSRF Token (meta + header)
- [x] Security headers (CSP, X-Frame-Options, X-Content-Type, Referrer-Policy)
- [x] Input sanitization (htmlspecialchars + PDO prepared statements)
- [x] Login attempt rate limiting
- [x] Session cookie: httponly, samesite=Lax
- [x] Avatar upload: type validation + size limit
- [x] `.htaccess` protection for /backups/, /logs/, /uploads/

#### 📈 Analytics
- [x] Visit tracking (visits_table)
- [x] Activity logs (7 วัน สำหรับ user, ถาวรสำหรับ admin)
- [x] Rating system (submit.php)
- [x] Support/Report system

---

## 2. โครงสร้างใหม่ (New Structure — V3)

```
babybib-v3/                          ← โฟลเดอร์ใหม่ทั้งหมด
│
├── .env                             ← เหมือนเดิม (ไม่ commit)
├── .env.example
├── .htaccess                        ← อัปเกรด security rules
├── .gitignore
│
├── composer.json                    ← PHPMailer + PhpWord
├── vendor/                          ← Composer packages
│
├── package.json                     ← Tailwind + Preline
├── tailwind.config.js               ← Custom config (violet theme)
├── postcss.config.js
│
├── public/                          ← ไฟล์ที่ browser เข้าถึงได้
│   ├── index.php                    ← Home page
│   ├── login.php
│   ├── register.php
│   ├── verify.php
│   ├── forgot-password.php
│   ├── reset-password.php
│   ├── generate.php                 ← หน้าสร้างบรรณานุกรม (CORE)
│   ├── sort.php
│   ├── summary.php
│   ├── start.php
│   ├── privacy.php
│   ├── terms.php
│   │
│   ├── users/                       ← Protected pages (login required)
│   │   ├── dashboard.php
│   │   ├── bibliography-list.php
│   │   ├── projects.php
│   │   ├── project-preview.php
│   │   ├── report-template.php
│   │   ├── report-builder.php
│   │   ├── activity-history.php
│   │   └── profile.php
│   │
│   ├── admin/                       ← Admin area
│   │   ├── index.php
│   │   ├── users.php
│   │   ├── bibliographies.php
│   │   ├── projects.php
│   │   ├── announcements.php
│   │   ├── feedback.php
│   │   ├── settings.php
│   │   ├── backups.php
│   │   ├── logs.php
│   │   └── notifications.php
│   │
│   ├── help/                        ← Help pages (แยกโฟลเดอร์)
│   │   ├── author.php
│   │   ├── place.php
│   │   └── publisher.php
│   │
│   └── errors/                      ← Error pages
│       ├── 403.php
│       ├── 404.php
│       └── 500.php
│
├── src/                             ← App source (PHP backend logic)
│   │
│   ├── Config/
│   │   ├── config.php               ← DB + constants
│   │   ├── env.php                  ← .env loader
│   │   └── email-config.php         ← Email settings
│   │
│   ├── Core/
│   │   ├── Session.php              ← Session management
│   │   ├── Security.php             ← CSRF, sanitize, headers
│   │   ├── Database.php             ← PDO singleton
│   │   └── Response.php             ← jsonResponse helper
│   │
│   ├── Helpers/
│   │   ├── functions.php            ← Global helper functions
│   │   ├── EmailHelper.php          ← PHPMailer wrapper
│   │   └── VisitTracker.php
│   │
│   ├── Api/                         ← API endpoints
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   ├── logout.php
│   │   │   ├── register.php
│   │   │   ├── verify-code.php
│   │   │   ├── resend-code.php
│   │   │   ├── forgot-password.php
│   │   │   ├── reset-password.php
│   │   │   ├── change-password.php
│   │   │   ├── update-profile.php
│   │   │   ├── upload-avatar.php
│   │   │   ├── remove-avatar.php
│   │   │   ├── delete-account.php
│   │   │   └── get-captcha.php
│   │   │
│   │   ├── bibliography/
│   │   │   ├── create.php
│   │   │   ├── update.php
│   │   │   ├── delete.php
│   │   │   ├── export.php
│   │   │   ├── move.php
│   │   │   ├── preview.php
│   │   │   └── update-project.php
│   │   │
│   │   ├── projects/
│   │   │   ├── create.php
│   │   │   ├── update.php
│   │   │   ├── delete.php
│   │   │   ├── get-content.php
│   │   │   └── export.php
│   │   │
│   │   ├── search/
│   │   │   └── smart.php            ← smart_search.php (เหมือนเดิม + upgrade)
│   │   │
│   │   ├── template/
│   │   │   ├── export-report.php
│   │   │   ├── export-report-research.php
│   │   │   ├── export-report-logo.php
│   │   │   ├── export-report-internship.php
│   │   │   └── get-project-bibs.php
│   │   │
│   │   ├── scraper/
│   │   │   └── web.php
│   │   │
│   │   ├── feedback/
│   │   │   └── create.php
│   │   │
│   │   ├── rating/
│   │   │   └── submit.php
│   │   │
│   │   ├── support/
│   │   │   └── report.php
│   │   │
│   │   └── admin/
│   │       ├── create-announcement.php
│   │       ├── update-announcement.php
│   │       ├── delete-announcement.php
│   │       ├── update-bibliography.php
│   │       ├── update-project.php
│   │       ├── create-user.php
│   │       ├── update-user.php
│   │       ├── update-user-details.php
│   │       ├── delete-user.php
│   │       ├── create-backup.php
│   │       ├── download-backup.php
│   │       ├── delete-backup.php
│   │       ├── update-feedback.php
│   │       ├── delete-feedback.php
│   │       ├── update-settings.php
│   │       ├── test-email-settings.php
│   │       ├── notifications.php
│   │       └── mark-notifications-read.php
│   │
│   └── Views/                       ← Shared PHP component partials
│       ├── layout/
│       │   ├── head.php             ← <head> block (meta, CSS, JS imports)
│       │   ├── navbar-guest.php
│       │   ├── navbar-user.php
│       │   ├── sidebar-admin.php
│       │   ├── footer.php
│       │   └── footer-admin.php
│       │
│       └── components/
│           ├── toast.php            ← Toast container
│           ├── modal.php            ← Reusable modal shell
│           ├── announcement-toast.php
│           └── loading-overlay.php
│
├── assets/                          ← Static files
│   ├── css/
│   │   └── app.css                  ← Compiled Tailwind output (DO NOT edit manually)
│   ├── js/
│   │   ├── app.js                   ← Main JS (init Preline, global functions)
│   │   ├── apa7-formatter.js        ← APA7 formatter (เหมือนเดิม ไม่เปลี่ยน logic)
│   │   └── tour.js                  ← Onboarding tour (refactor เล็กน้อย)
│   ├── images/
│   │   ├── favicon.svg
│   │   └── Chiang_Mai_University.svg.png
│   └── templates/
│       ├── template_academic_general.docx
│       ├── template_academic_general_logo.docx
│       ├── template_academic_research.docx
│       └── template_internship.docx
│
├── lang/
│   ├── th.php
│   └── en.php
│
├── database/
│   ├── database.sql                 ← Schema (เหมือนเดิม)
│   ├── resource_types.sql           ← 30+ resource types seed
│   └── babybib_db.sql               ← Full dump
│
├── sql/                             ← Migration SQLs
│   └── ...
│
├── backups/                         ← DB backups (.sql.gz)
│   └── .htaccess
│
├── uploads/                         ← User uploads
│   ├── avatars/
│   └── .htaccess
│
├── logs/                            ← App logs
│   └── .htaccess
│
├── tmp/                             ← Cache files
│   ├── babybib_search_cache/
│   ├── babybib_rate/
│   └── export_samples/
│
└── scripts/
    ├── backup_database.sh
    └── build.sh                     ← npm run build wrapper
```

---

## 3. Design System V3 (Tailwind Config)

### 3.1 Color Tokens (เหมือนเดิม → Tailwind custom)

```js
// tailwind.config.js
module.exports = {
  content: [
    './public/**/*.php',
    './src/Views/**/*.php',
    './assets/js/**/*.js',
    './node_modules/preline/dist/*.js',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#8B5CF6',  // violet-500
          dark:    '#7C3AED',  // violet-600
          darker:  '#6D28D9',  // violet-700
          light:   '#EDE9FE',  // violet-100
          50:      '#F5F3FF',
          100:     '#EDE9FE',
          200:     '#DDD6FE',
          300:     '#C4B5FD',
          400:     '#A78BFA',
          500:     '#8B5CF6',
          600:     '#7C3AED',
          700:     '#6D28D9',
          800:     '#5B21B6',
          900:     '#4C1D95',
        },
        success: { DEFAULT: '#10B981', light: '#D1FAE5' },
        warning: { DEFAULT: '#F59E0B', light: '#FEF3C7' },
        danger:  { DEFAULT: '#EF4444', light: '#FEE2E2' },
        info:    { DEFAULT: '#3B82F6', light: '#DBEAFE' },
      },
      fontFamily: {
        thai:    ['Tahoma', 'Noto Sans Thai', 'sans-serif'],
        sans:    ['Inter', 'system-ui', 'sans-serif'],
        brand:   ['Comfortaa', 'sans-serif'],
      },
      backgroundImage: {
        'primary-gradient': 'linear-gradient(135deg, #6366F1 0%, #8B5CF6 45%, #D946EF 100%)',
        'brand-gradient':   'linear-gradient(to right, #180d36, #8B5CF6, #CF23CF, #180d36)',
      },
      boxShadow: {
        'primary': '0 4px 14px 0 rgba(139, 92, 246, 0.3)',
        'primary-lg': '0 10px 30px 0 rgba(139, 92, 246, 0.4)',
      },
    },
  },
  plugins: [
    require('preline/plugin'),
  ],
}
```

### 3.2 Typography

| Role | Font | Weight |
|---|---|---|
| Brand Logo "Babybib" | Comfortaa | 700 + brand-gradient clip |
| Thai body text | Tahoma (ไม่ต้อง load Google Fonts) | 400/600 |
| English body / UI | Inter (Google Fonts / preconnect) | 400/500/600/700 |
| Code / monospace | system monospace | 400 |

### 3.3 Component Mapping (เดิม → Preline)

| Component เดิม | Preline Component |
|---|---|
| `.navbar` custom | `hs-navbar` + `hs-dropdown` |
| `.dropdown-menu` custom | `hs-dropdown` |
| `.modal-overlay` custom | `hs-overlay` |
| `.toast` custom | Custom (keep) หรือ `hs-toast` |
| `.btn-primary` custom | `py-2 px-4 bg-primary text-white rounded-lg` |
| `.form-input` custom | `hs-input` / Tailwind form classes |
| `.table` custom | Preline DataTable |
| `.pagination` custom | Preline pagination |
| `.badge` custom | Preline badge |
| Sidebar admin | `hs-sidebar` |
| Loading overlay | Custom (keep) |
| Language toggle | Custom (keep — unique) |
| Onboarding tour | Custom (keep — `tour.js`) |

---

## 4. Dependency Stack V3

### 4.1 PHP / Composer
```json
{
  "require": {
    "phpmailer/phpmailer": "^6.9",
    "phpoffice/phpword":   "^1.3"
  }
}
```

### 4.2 Node.js / npm
```json
{
  "devDependencies": {
    "tailwindcss":          "^3.x",
    "autoprefixer":         "^10.x",
    "postcss":              "^8.x",
    "@tailwindcss/forms":   "^0.5.x",
    "@tailwindcss/typography": "^0.5.x"
  },
  "dependencies": {
    "preline":              "^2.x",
    "chart.js":             "^4.x"
  }
}
```

> **หมายเหตุ:** Chart.js ใช้เฉพาะ Admin Panel — load แบบ conditional (ไม่โหลดทุกหน้า)

### 4.3 CDN (ลด dependency CDN ให้น้อยลง)
```html
<!-- ยังคงใช้ CDN สำหรับ: -->
<link rel="stylesheet" href="...font-awesome 6.x...">
<link href="...Google Fonts Inter + Comfortaa..." rel="stylesheet">

<!-- ยกเลิก CDN: -->
<!-- Tailwind CDN → ใช้ build แทน -->
<!-- Alpine.js CDN → ยังใช้ CDN หรือ npm install alpinejs -->
<!-- Lucide CDN → ลดการใช้ (ใช้ FA เป็นหลัก) -->
```

### 4.4 External APIs (เหมือนเดิม)
- Google Books API (3 keys rotation)
- Open Library API
- CrossRef API
- OpenAlex API
- Semantic Scholar API
- Web Scraper (DOMDocument)

---

## 5. Migration Steps — Phase by Phase

### Phase 0: Setup (วันที่ 1)
- [ ] สร้างโฟลเดอร์ `babybib-v3/` ใน `/htdocs/`
- [ ] `npm init` + ติดตั้ง Tailwind + Preline
- [ ] `composer init` + ติดตั้ง PHPMailer + PhpWord
- [ ] Setup `tailwind.config.js` พร้อม violet color tokens
- [ ] สร้าง `assets/css/input.css` + build script
- [ ] Copy `.env`, `database/`, `assets/templates/`, `assets/images/`, `lang/`, `vendor/`, `uploads/`, `backups/`, `tmp/`
- [ ] Copy `assets/js/apa7-formatter.js` (ไม่เปลี่ยน logic)

### Phase 1: Core Infrastructure (วันที่ 1-2)
- [ ] `src/Config/config.php` — เหมือนเดิม แต่ clean ขึ้น
- [ ] `src/Config/env.php` — เหมือนเดิม
- [ ] `src/Core/Session.php` — ย้าย session logic
- [ ] `src/Core/Security.php` — CSRF, sanitize, headers
- [ ] `src/Core/Database.php` — PDO singleton
- [ ] `src/Helpers/functions.php` — global helpers
- [ ] `src/Helpers/EmailHelper.php` — PHPMailer wrapper
- [ ] `src/Views/layout/head.php` — `<head>` template (Tailwind build CSS, Preline JS)
- [ ] `src/Views/layout/footer.php`
- [ ] `src/Views/components/toast.php`
- [ ] `src/Views/components/loading-overlay.php`

### Phase 2: Navbar + Layout Components (วันที่ 2-3)
- [ ] `navbar-guest.php` — Preline navbar + hs-dropdown (สีม่วง)
- [ ] `navbar-user.php` — เหมือน guest + เมนู user
- [ ] `sidebar-admin.php` — Preline sidebar (hs-sidebar)
- [ ] `footer.php`
- [ ] `footer-admin.php`
- [ ] Mobile responsive (hamburger → hs-overlay)
- [ ] Language toggle component
- [ ] Announcement toast component

### Phase 3: Auth Pages (วันที่ 3-4)
- [ ] `public/login.php`
- [ ] `public/register.php` (multi-step: account info → org info)
- [ ] `public/verify.php`
- [ ] `public/forgot-password.php`
- [ ] `public/reset-password.php`
- [ ] Auth API endpoints (src/Api/auth/) — logic เหมือนเดิม
- [ ] CAPTCHA

### Phase 4: Home + Public Pages (วันที่ 4)
- [ ] `public/index.php` — Landing page (hero + features)
- [ ] `public/start.php` — คู่มือ
- [ ] `public/privacy.php`, `public/terms.php`
- [ ] `public/help/author.php`, `place.php`, `publisher.php`
- [ ] Error pages (403, 404, 500)

### Phase 5: CORE — Generate Page (วันที่ 5-7) ⭐ สำคัญที่สุด
- [ ] `public/generate.php` — split-view layout (Tailwind grid)
  - Left: Resource type selector (category tabs → Preline tabs)
  - Left: Dynamic form (30+ resource types)
  - Right: Live preview (sticky)
  - Right: Action buttons (copy, save, export)
- [ ] Form fields (authors, year, title, etc.) → Tailwind form classes
- [ ] Author editor (add/remove/reorder authors) → dynamic JS
- [ ] Smart Search bar → Preline input + dropdown results
- [ ] `apa7-formatter.js` — ไม่เปลี่ยน logic, แค่ connect กับ form ใหม่
- [ ] Secondary Source (Cited In) feature
- [ ] Edit mode
- [ ] Save bibliography (AJAX → `/src/Api/bibliography/create.php`)
- [ ] Tour integration (`tour.js`)

### Phase 6: User Area (วันที่ 7-9)
- [ ] `users/dashboard.php` — stats cards, recent bibs, quick actions
- [ ] `users/bibliography-list.php` — Preline DataTable (search, filter, sort)
- [ ] `users/projects.php` — project cards grid
- [ ] `users/project-preview.php` — bib list + export
- [ ] `users/report-template.php` — template cards (Preline card)
- [ ] `users/report-builder.php` — DOCX builder
- [ ] `users/activity-history.php` — timeline
- [ ] `users/profile.php` — avatar upload, org info

### Phase 7: Sort + Summary (วันที่ 9-10)
- [ ] `public/sort.php` — paste & sort
- [ ] `public/summary.php` — last bibliography summary

### Phase 8: Admin Panel (วันที่ 10-13)
- [ ] `admin/index.php` — Dashboard (Preline charts/stats)
- [ ] `admin/users.php` — DataTable + CRUD modals
- [ ] `admin/bibliographies.php`
- [ ] `admin/projects.php`
- [ ] `admin/announcements.php`
- [ ] `admin/feedback.php`
- [ ] `admin/settings.php` — form groups
- [ ] `admin/backups.php`
- [ ] `admin/logs.php`
- [ ] `admin/notifications.php`
- [ ] Admin API endpoints

### Phase 9: Export System (วันที่ 13-14)
- [ ] `/src/Api/template/` — DOCX export (PhpWord, เหมือนเดิม)
- [ ] `/src/Api/bibliography/export.php`
- [ ] `/src/Api/projects/export.php`

### Phase 10: Performance + Security Polish (วันที่ 14-15)
- [ ] Build Tailwind production (`--minify`)
- [ ] จัดการ cache headers (`.htaccess`)
- [ ] Review security headers
- [ ] Test rate limiting
- [ ] Test CSRF on all forms
- [ ] Mobile responsive test
- [ ] Cross-browser test

---

## 6. อัปเกรดจากเดิม (Upgrade Points)

| Feature | เดิม | V3 |
|---|---|---|
| CSS | 2,000+ บรรทัด custom | Tailwind utility + Preline components |
| Navbar dropdown | Custom hover JS | Preline `hs-dropdown` (accessible) |
| Modal | Custom CSS class | Preline `hs-overlay` |
| DataTable | Custom HTML table | Preline DataTable (built-in search/sort/page) |
| Sidebar admin | Custom CSS | Preline `hs-sidebar` (collapsible, responsive) |
| Dark mode | localStorage toggle (partial) | ❌ ยกเลิก — Light mode เท่านั้น (focus quality ก่อน) |
| Form validation | Custom JS | Alpine.js + Preline input states |
| Toast | Custom JS class | ปรับ custom ให้ใช้ Tailwind classes |
| Loading | Custom CSS spinner | ปรับ custom ให้ใช้ Tailwind |
| Tailwind | CDN (warn suppressed) | **npm build (production)** |
| Alpine.js | CDN | npm หรือ CDN (เลือก) |
| Icons | FA + Lucide | **FA เป็นหลัก** (ลด Lucide) |
| Font | Comfortaa + Inter + Tahoma | เหมือนเดิม + `@tailwindcss/fonts` |

---

## 7. ไฟล์ที่ Copy มาได้เลย (ไม่ต้องเขียนใหม่)

| ไฟล์ | Action |
|---|---|
| `assets/js/apa7-formatter.js` | **ต้องแก้ bug ก่อน** แล้วค่อย copy (ดู Section 17) |
| `assets/js/tour.js` | Copy + แก้ CSS class names เป็น Tailwind |
| `lang/th.php`, `lang/en.php` | Copy ตรง ๆ |
| `database/database.sql` | Copy ตรง ๆ |
| `database/resource_types.sql` | Copy ตรง ๆ |
| `assets/templates/*.docx` | Copy ตรง ๆ |
| `assets/images/*` | Copy ตรง ๆ |
| `src/Api/*` (ทุก endpoint) | Copy + ปรับ require path ใหม่ |
| `includes/email-helper.php` | Copy → `src/Helpers/EmailHelper.php` |
| `includes/security-utils.php` | Copy → `src/Core/Security.php` |
| `includes/functions.php` (logic) | Copy → `src/Helpers/functions.php` |
| `.env`, `.env.example` | Copy ตรง ๆ |
| `scripts/backup_database.sh` | Copy ตรง ๆ |

---

## 8. ไฟล์ที่ต้องเขียนใหม่ทั้งหมด (HTML/CSS → Tailwind)

| ไฟล์ | เหตุผล |
|---|---|
| ทุก `.php` ใน `public/` | เปลี่ยน CSS class ทั้งหมดเป็น Tailwind |
| ทุก `.php` ใน `src/Views/` | เปลี่ยน CSS class ทั้งหมดเป็น Tailwind |
| `assets/css/` | แทนด้วย Tailwind build output |
| `assets/js/app.js` | เขียน Preline init + global helpers ใหม่ |
| `src/Views/layout/head.php` | Tailwind build CSS + Preline JS |

---

## 9. package.json / build scripts

```json
{
  "name": "babybib-v3",
  "version": "3.0.0",
  "scripts": {
    "dev":   "tailwindcss -i ./assets/css/input.css -o ./assets/css/app.css --watch",
    "build": "tailwindcss -i ./assets/css/input.css -o ./assets/css/app.css --minify"
  },
  "devDependencies": {
    "tailwindcss":              "^3.4.x",
    "autoprefixer":             "^10.4.x",
    "@tailwindcss/forms":       "^0.5.x",
    "@tailwindcss/typography":  "^0.5.x"
  },
  "dependencies": {
    "preline":    "^2.x",
    "alpinejs":   "^3.x"
  }
}
```

**assets/css/input.css:**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  body { @apply font-thai text-gray-900 bg-gray-50; }
  body.lang-en { @apply font-sans; }
}

@layer components {
  /* Comfortaa brand logo */
  .brand-logo {
    @apply font-brand font-bold text-transparent bg-clip-text;
    background-image: linear-gradient(to right, #180d36, #8B5CF6, #CF23CF, #180d36);
  }
  
  /* Primary gradient button */
  .btn-primary-gradient {
    @apply inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white font-medium;
    background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 45%, #D946EF 100%);
    box-shadow: 0 4px 14px 0 rgba(139, 92, 246, 0.3);
  }
}
```

---

## 10. Preline UI Key Components

### ที่จะใช้หลัก ๆ
- **hs-navbar** — Navbar responsive + dropdown
- **hs-dropdown** — User menu, resource type selector
- **hs-overlay** — Modal dialogs
- **hs-sidebar** — Admin sidebar (collapsible)
- **hs-tabs** — Resource category tabs (Books, Journals, etc.)
- **hs-datatable** — Admin tables (user list, bib list)
- **hs-input** — Form inputs with validation states
- **hs-select** — Custom select (author type, org type)
- **hs-toast** — Notifications (หรือ keep custom)
- **hs-accordion** — FAQ / help sections
- **hs-stepper** — Register multi-step form

### Preline CDN (ใน head.php)
```html
<!-- Preline JS (after build) -->
<script src="<?= SITE_URL ?>/node_modules/preline/dist/preline.js"></script>
<!-- หรือ copy ไปใน assets/js/preline.js -->
```

---

## 11. Database — ไม่เปลี่ยน Schema

Database schema เดิมใช้ได้ทั้งหมด ไม่ต้อง migrate ข้อมูล

**Tables ที่มี:**
- `users`
- `email_verifications`
- `password_resets`
- `resource_types` (30+ types)
- `projects`
- `bibliographies`
- `activity_logs`
- `announcements`
- `feedback`
- `system_settings`
- `visits`
- `login_attempts`
- `support_reports`
- `ratings`

---

## 12. Security Checklist V3

> **ยืนยัน (29 พ.ค. 2568):** จะ deploy บน production server จริง — ต้องทำ hardening ครบทุกข้อ

**Application Security:**
- [ ] CSRF token บนทุก `<form>` method="POST" และทุก AJAX POST
- [ ] Security headers ใน `src/Core/Security.php` (CSP ปรับสำหรับ Preline + Google Fonts + Font Awesome)
- [ ] PDO prepared statements ทุก query (ไม่มี raw SQL concatenation)
- [ ] `htmlspecialchars()` ทุก output ที่มาจาก user input
- [ ] Avatar upload: MIME type check (PHP finfo), ขนาด max 2MB, convert เป็น .webp
- [ ] Rate limiting: login (5 tries/5min), smart_search (30/min), register (3/hour per IP)
- [ ] Session: httponly, samesite=Lax, secure=true (HTTPS), regenerate ID on login
- [ ] `APP_KEY` ≥ 32 chars random ใน `.env` สำหรับ HMAC token hashing
- [ ] Admin middleware: check `role === 'admin'` ทุก admin page + API

**Production Server Hardening:**
- [ ] HTTPS only (redirect HTTP → HTTPS ใน `.htaccess`)
- [ ] `SESSION_COOKIE_SECURE=1` ใน `.env`
- [ ] `.htaccess` protect: `/backups/`, `/logs/`, `/tmp/`, `/vendor/`, `/src/`
- [ ] PHP error display OFF (`display_errors = 0`) ใน production
- [ ] PHP error log ON (`log_errors = 1`, log ไปนอก webroot)
- [ ] `expose_php = Off` ใน `php.ini`
- [ ] `server_tokens Off` (nginx) หรือ `ServerTokens Prod` (Apache)
- [ ] Disable directory listing (`Options -Indexes`)
- [ ] File permissions: PHP files 644, directories 755, `.env` 600
- [ ] Database user: ใช้ user เฉพาะ (ไม่ใช้ root), grant เฉพาะ SELECT/INSERT/UPDATE/DELETE
- [ ] Backup encryption: `.sql.gz` encrypt ก่อน store

**Content Security Policy (CSP) สำหรับ V3:**
```
Content-Security-Policy:
  default-src 'self';
  script-src 'self' cdn.jsdelivr.net cdnjs.cloudflare.com;
  style-src 'self' 'unsafe-inline' fonts.googleapis.com cdnjs.cloudflare.com;
  font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com;
  img-src 'self' data: https://books.google.com https://covers.openlibrary.org;
  connect-src 'self';
  frame-ancestors 'none';
```

---

## 13. ลำดับความสำคัญ (Priority Order)

```
1. [Critical] Core infrastructure (Config, DB, Security, Session)
2. [Critical] APA7 generate page (หน้าหลัก — ทำก่อน)
3. [High]     Auth (login, register, verify, reset password)
4. [High]     User area (dashboard, bib-list, projects)
5. [High]     Admin panel
6. [Medium]   Export / Templates (DOCX)
7. [Medium]   Sort + Summary pages
8. [Medium]   Help pages
9. [Low]      Onboarding tour (ปรับ CSS class)
10. [Low]     Rating + Support system
```

---

## 14. สิ่งที่ต้องระวัง

1. **APA7 formatter bugs** — มี bug ยืนยันแล้ว 9 จุด (ดู Section 17) ต้องแก้ทุกจุดก่อน copy ไป V3
2. **PhpWord template** — DOCX template ใช้ placeholder syntax เฉพาะ → ทดสอบก่อน deploy
3. **Smart Search cache path** — ต้องแน่ใจว่า `/tmp/babybib_search_cache/` writable
4. **Preline JS init** — ต้อง call `window.HSStaticMethods.autoInit()` หลัง DOM loaded
5. **Thai font** — Tahoma ต้องมีบน server (XAMPP มี), Noto Sans Thai เป็น fallback
6. **CSP header** — ถ้าใช้ CDN (FA, Google Fonts) ต้อง whitelist ใน CSP
7. **Alpine.js + Preline** — ต้อง load ตามลำดับ (Alpine หลัง Preline หรือใช้ Alpine defer)
8. **Session path** — กรณีใช้ shared hosting ต้อง set session path ชัด ๆ

---

## 15. Quick Start Commands

```bash
# 1. สร้างโฟลเดอร์ใหม่
mkdir /Applications/XAMPP/xamppfiles/htdocs/babybib_new
cd /Applications/XAMPP/xamppfiles/htdocs/babybib_new

# 2. Init git repo ใหม่
git init
git remote add origin <github-repo-url-ใหม่>

# 3. Init npm + ติดตั้ง Tailwind + Preline
npm init -y
npm install -D tailwindcss@3 autoprefixer postcss @tailwindcss/forms @tailwindcss/typography
npm install preline alpinejs chart.js
npx tailwindcss init -p

# 4. Init Composer
php ../babybib/composer.phar init
php ../babybib/composer.phar require phpmailer/phpmailer phpoffice/phpword

# 5. Copy assets ที่ไม่ต้องแก้
cp -r ../babybib/lang ./
cp -r ../babybib/database ./
cp -r ../babybib/assets/templates ./assets/templates
cp -r ../babybib/assets/images ./assets/images
cp ../babybib/.env.example ./.env.example
cp ../babybib/scripts/backup_database.sh ./scripts/

# 6. Setup .env สำหรับ local
cp .env.example .env
# แก้ SITE_URL=http://localhost/babybib_new ใน .env

# 7. ทดสอบ build
npm run dev
```

---

## 16. ไฟล์อ้างอิง Preline

- Docs: https://preline.co/docs
- Components: https://preline.co/docs/navbars.html
- Dark mode: https://preline.co/docs/dark-mode.html
- DataTable: https://preline.co/docs/datatable.html
- Sidebar: https://preline.co/docs/sidebar.html
- Overlay/Modal: https://preline.co/docs/overlay.html

---

---

## 17 (เดิม → ย้ายเป็น 23). Confirmed Decisions — Final

> รวมคำตอบทุกข้อจากการ Q&A session ไว้ที่นี่สำหรับ reference ตอน implement

| หัวข้อ | Decision |
|--------|----------|
| Font Thai | Tahoma (system font, weight 400 — lighter feel) |
| Font EN | Inter |
| Font Brand | Comfortaa 700 + brand gradient |
| Dark Mode | ❌ Light mode เท่านั้น |
| Admin Charts | ✅ Chart.js v4 (conditional load) |
| i18n Strategy | Subdirectory `/th/` + `/en/` + hreflang SEO |
| Bibliography Language | แยกจาก UI lang — 2 toggle อิสระ |
| Export Formats | .docx + .txt เท่านั้น (ไม่เพิ่ม PDF/BibTeX/RIS) |
| Guest Access | สร้าง + copy ได้, sort ได้ — แต่ export .docx ต้อง login |
| Deploy target | Production server (มีอยู่แล้ว) + production hardening |
| Code strategy | Clean Rewrite — เขียนใหม่ทั้งหมด, logic เดิมเป็น reference |
| DB Schema | ไม่เปลี่ยน — copy ตรงๆ |
| APA7 bugs | แก้ 10 จุดก่อน implement |
| Smart Search bugs | แก้ 9 จุด + เพิ่ม DataCite/PubMed |

---

## 24. i18n Architecture — Subdirectory Strategy (`/th/` + `/en/`)

> **Decision:** URL-based language via subdirectory prefix
> ส่งผลกระทบต่อ routing, .htaccess, SITE_URL, links ทั้งหมด

### 24.1 URL Structure

```
/th/                     ← Homepage ภาษาไทย (default)
/th/generate             ← สร้างบรรณานุกรม
/th/sort
/th/start
/th/login
/th/register
/th/verify
/th/forgot-password
/th/reset-password
/th/privacy
/th/terms
/th/help/author
/th/help/place
/th/help/publisher
/th/users/dashboard
/th/users/bibliography-list
/th/users/projects
/th/users/project/{id}
/th/users/report-template
/th/users/report-builder
/th/users/activity
/th/users/profile
/th/admin/                ← Admin ใช้ภาษาเดิมของ user (ไม่แยก /en/admin)
/en/                     ← Homepage ภาษาอังกฤษ
/en/generate
... (เหมือนกัน แต่ /en/ prefix)
```

**Default language:** `/th/` เป็น default, redirect `/` → `/th/`

### 24.2 .htaccess Routing

```apache
# /public/.htaccess
RewriteEngine On

# Redirect root to default language
RewriteRule ^$ /th/ [R=302,L]

# Strip /th/ or /en/ prefix and pass lang as ENV variable
RewriteRule ^(th|en)/(.*)$ public_pages/$2 [E=LANG:$1,QSA,L]

# Fallback: if file not found, 404
```

**ทางเลือก (แนะนำกว่า) — Front Controller Pattern:**
```apache
# .htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(th|en)/(.*)$ index.php [E=LANG:$1,E=PAGE:$2,QSA,L]
RewriteRule ^$ index.php [E=LANG:th,E=PAGE:,L]
```

### 24.3 Front Controller (`public/index.php`)

```php
<?php
// ตรวจ language จาก URL
$lang = $_SERVER['LANG'] ?? 'th';  // set จาก .htaccess ENV
$page = $_SERVER['PAGE'] ?? '';    // set จาก .htaccess ENV

// Validate
if (!in_array($lang, ['th', 'en'])) $lang = 'th';

// Set session lang
$_SESSION['lang'] = $lang;
define('CURRENT_LANG', $lang);

// Route ไปยัง page handler
$pagePath = __DIR__ . '/pages/' . ($page ?: 'home') . '.php';
if (!file_exists($pagePath)) {
    include __DIR__ . '/errors/404.php';
    exit;
}

include $pagePath;
```

### 24.4 Folder Structure ปรับสำหรับ i18n

```
public/
├── index.php            ← Front Controller (ทุก request ผ่านที่นี่)
├── pages/               ← Page files (ไม่มี lang ใน path)
│   ├── home.php
│   ├── generate.php
│   ├── sort.php
│   ├── login.php
│   ├── register.php
│   ├── verify.php
│   ├── forgot-password.php
│   ├── reset-password.php
│   ├── privacy.php
│   ├── terms.php
│   ├── users/
│   │   ├── dashboard.php
│   │   ├── bibliography-list.php
│   │   ├── projects.php
│   │   ├── project-view.php
│   │   ├── report-template.php
│   │   ├── report-builder.php
│   │   ├── activity.php
│   │   └── profile.php
│   ├── help/
│   │   ├── author.php
│   │   ├── place.php
│   │   └── publisher.php
│   ├── admin/
│   │   ├── index.php
│   │   ├── users.php
│   │   ├── bibliographies.php
│   │   ├── projects.php
│   │   ├── announcements.php
│   │   ├── feedback.php
│   │   ├── settings.php
│   │   ├── backups.php
│   │   ├── logs.php
│   │   └── notifications.php
│   └── errors/
│       ├── 403.php
│       ├── 404.php
│       └── 500.php
├── .htaccess
└── api/ → (ไม่มี lang prefix, API ไม่ใช้ i18n routing)
```

### 24.5 Helper Functions สำหรับ i18n

```php
// src/Helpers/i18n.php

/**
 * สร้าง URL พร้อม language prefix
 * url('generate') → '/th/generate' หรือ '/en/generate'
 */
function url(string $page, string $lang = null): string {
    $lang = $lang ?? CURRENT_LANG;
    $base = rtrim(SITE_URL, '/');
    $page = ltrim($page, '/');
    return "{$base}/{$lang}/{$page}";
}

/**
 * สร้าง URL สำหรับ switch language (เหมือน page นี้แต่ต่าง lang)
 */
function langSwitchUrl(string $targetLang): string {
    $currentPage = $_SERVER['PAGE'] ?? '';
    $base = rtrim(SITE_URL, '/');
    return "{$base}/{$targetLang}/{$currentPage}";
}

/**
 * Translate key
 */
function __($key, string $lang = null): string {
    global $translations;
    $lang = $lang ?? CURRENT_LANG;
    return $translations[$lang][$key] ?? $key;
}
```

### 24.6 SEO — hreflang Tags (ใน head.php)

```html
<!-- head.php -->
<link rel="canonical" href="<?= url(CURRENT_PAGE, CURRENT_LANG) ?>">
<link rel="alternate" hreflang="th" href="<?= url(CURRENT_PAGE, 'th') ?>">
<link rel="alternate" hreflang="en" href="<?= url(CURRENT_PAGE, 'en') ?>">
<link rel="alternate" hreflang="x-default" href="<?= url(CURRENT_PAGE, 'th') ?>">
```

### 24.7 Language Toggle ใน Navbar

```
[TH] [EN]   ← toggle pill
            ← click EN → redirect ไป langSwitchUrl('en')
```

เมื่อ switch:
1. `redirect(langSwitchUrl('en'))`
2. Session `lang` เปลี่ยนเป็น `'en'`
3. Load `lang/en.php` แทน `lang/th.php`
4. Bibliography output language **ไม่เปลี่ยน** (แยก toggle)

### 24.8 Bibliography Output Language — 2 Toggle แยก

```
UI Language:       [🇹🇭 TH] [🇬🇧 EN]   ← เปลี่ยน UI text ทั้งหมด
Bibliography Lang: [ไทย] [English]     ← เปลี่ยน APA7 output เท่านั้น
```

- `CURRENT_LANG` = UI language (จาก URL prefix)
- `BIB_LANG` = bibliography output language (จาก form toggle, default = 'th')
- `BIB_LANG` เก็บใน session แยกต่างหาก (`$_SESSION['bib_lang']`)
- ส่งไปยัง `apa7-formatter.js` ผ่าน JS variable

```php
// generate.php
<script>
  const BIB_LANG = '<?= $_SESSION['bib_lang'] ?? 'th' ?>';
  const UI_LANG  = '<?= CURRENT_LANG ?>';
</script>
```

### 24.9 API Endpoints — ไม่ใช้ Language Prefix

API ทุก endpoint ใช้ `/api/...` ตรงๆ ไม่มี `/th/` `/en/` prefix:
```
POST /api/auth/login
POST /api/bibliography/create
GET  /api/search/smart?q=...
```

เหตุผล: API รับ-ส่ง JSON ไม่ต้องการ i18n routing

### 24.10 Admin Panel Language

- Admin panel (`/admin/...`) **ไม่มี language prefix**
- **Force ภาษาไทยเสมอ** — UI admin เป็นไทยล้วน ไม่มี toggle
- `define('CURRENT_LANG', 'th')` ใน admin layout header
- โหลด `lang/th.php` เสมอโดยไม่ดู session lang

---

## 25. Guest Access — Permission Matrix V3

> **Decision:** Guest ใช้ได้เต็มที่ ยกเว้น Export .docx

| Feature | Guest | User (Logged In) |
|---------|-------|-----------------|
| สร้างบรรณานุกรม (generate.php) | ✅ | ✅ |
| Smart Search (ISBN/DOI/URL/keyword) | ✅ | ✅ |
| Live Preview | ✅ | ✅ |
| Copy bibliography text | ✅ | ✅ |
| Copy in-text citation | ✅ | ✅ |
| Sort bibliography (sort.php) | ✅ | ✅ |
| Help pages | ✅ | ✅ |
| Save bibliography ถาวร | ❌ → prompt login | ✅ |
| Projects | ❌ → prompt login | ✅ |
| Export .txt | ✅ (from preview) | ✅ |
| Export .docx template | ❌ → prompt login | ✅ |
| Report Builder | ❌ → prompt login | ✅ |
| Dashboard | ❌ → redirect login | ✅ |
| Profile | ❌ → redirect login | ✅ |

**UX สำหรับ Guest ที่พยายาม Save:**
- แสดง modal: "บันทึกบรรณานุกรมนี้ได้เลย เพียง [สมัครฟรี] หรือ [เข้าสู่ระบบ]"
- ไม่หายไปจาก form (ไม่ redirect ทันที)
- บรรณานุกรมที่สร้างค้างไว้ใน localStorage ชั่วคราว

---

## 26. ปรับ Migration Steps Phase 0 ตามข้อมูลใหม่

Phase 0 เพิ่มงาน:

```
Phase 0 Extra:
- [ ] ออกแบบ .htaccess routing สำหรับ /th/ /en/
- [ ] สร้าง Front Controller (public/index.php)
- [ ] สร้าง url() helper function
- [ ] สร้าง langSwitchUrl() helper function
- [ ] ทดสอบ routing: /th/generate → pages/generate.php ✓
- [ ] ทดสอบ routing: /en/login → pages/login.php ✓
- [ ] Plan Guest localStorage strategy (บันทึก bib ชั่วคราว)
```

Phase 5 (Generate Page) เพิ่มงาน:
```
- [ ] Guest mode detection (isLoggedIn() check)
- [ ] "Save prompt" modal สำหรับ guest
- [ ] localStorage fallback สำหรับ guest bib (ชั่วคราว)
- [ ] BIB_LANG toggle (แยกจาก UI_LANG)
```

---

---

## 27. Resource Types — Field Specification ครบทุกประเภท

> ต้องครบเหมือนเดิมทุกตัว ห้ามตัดออก — เป็น core feature หลัก
> อ้างอิงจาก `database/resource_types.sql`

---

### 27.1 หมวด Books (หนังสือ) — 5 ประเภท

#### `book` — หนังสือ
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `title` | ชื่อหนังสือ | Text | ✅ |
| `edition` | ครั้งที่พิมพ์ | Number | ❌ |
| `publisher` | สำนักพิมพ์ | Text | ✅ |
| `place` | สถานที่พิมพ์ | Text | ❌ (APA7 ไม่บังคับ) |

**APA7 Output:** `ผู้แต่ง. (ปี). *ชื่อหนังสือ* (ครั้งที่พิมพ์). สำนักพิมพ์.`

---

#### `book_series` — หนังสือชุดหลายเล่มจบ
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `title` | ชื่อหนังสือชุด | Text | ✅ |
| `volume` | เล่มที่ | Number | ❌ |
| `edition` | ครั้งที่พิมพ์ | Number | ❌ |
| `publisher` | สำนักพิมพ์ | Text | ✅ |
| `place` | สถานที่พิมพ์ | Text | ❌ |

**APA7 Output:** `ผู้แต่ง. (ปี). *ชื่อชุด* (ครั้งที่, เล่มที่). สำนักพิมพ์.`

---

#### `book_chapter` — บทความในหนังสือ
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้เขียนบท | Author Editor | ✅ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `chapter_title` | ชื่อบท | Text | ✅ |
| `editors` | บรรณาธิการ | Text (free-form) | ❌ |
| `book_title` | ชื่อหนังสือ | Text | ✅ |
| `edition` | ครั้งที่พิมพ์ | Number | ❌ |
| `volume` | เล่มที่ | Number | ❌ |
| `pages` | หน้า (เช่น 45-78) | Text | ❌ |
| `publisher` | สำนักพิมพ์ | Text | ✅ |
| `place` | สถานที่พิมพ์ | Text | ❌ |

**APA7 Output:** `ผู้แต่ง. (ปี). ชื่อบท. ใน บ.ก. (บ.ก.), *ชื่อหนังสือ* (ครั้งที่, เล่มที่, หน้า xx-xx). สำนักพิมพ์.`

---

#### `ebook_doi` — หนังสืออิเล็กทรอนิกส์ (มี DOI)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `title` | ชื่อหนังสือ | Text | ✅ |
| `edition` | ครั้งที่พิมพ์ | Number | ❌ |
| `publisher` | สำนักพิมพ์ | Text | ✅ |
| `doi` | DOI | URL/DOI Input | ✅ |

**APA7 Output:** `ผู้แต่ง. (ปี). *ชื่อหนังสือ* (ครั้งที่). สำนักพิมพ์. https://doi.org/xxx`

---

#### `ebook_no_doi` — หนังสืออิเล็กทรอนิกส์ (ไม่มี DOI)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `title` | ชื่อหนังสือ | Text | ✅ |
| `edition` | ครั้งที่พิมพ์ | Number | ❌ |
| `publisher` | สำนักพิมพ์ | Text | ✅ |
| `url` | URL | URL Input | ✅ |

---

### 27.2 หมวด Journals (วารสาร) — 5 ประเภท

#### `journal_article` — บทความวารสาร (ตีพิมพ์)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `article_title` | ชื่อบทความ | Text | ✅ |
| `journal_name` | ชื่อวารสาร | Text | ✅ |
| `volume` | ปีที่/เล่มที่ | Number | ❌ |
| `issue` | ฉบับที่ | Number | ❌ |
| `pages` | หน้า | Text | ❌ |

**APA7 Output:** `ผู้แต่ง. (ปี). ชื่อบทความ. *ชื่อวารสาร*, *ปีที่*(ฉบับที่), หน้า.`

---

#### `ejournal_doi` — บทความวารสารออนไลน์ (มี DOI)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `article_title` | ชื่อบทความ | Text | ✅ |
| `journal_name` | ชื่อวารสาร | Text | ✅ |
| `volume` | ปีที่/เล่มที่ | Number | ❌ |
| `issue` | ฉบับที่ | Number | ❌ |
| `pages` | หน้า | Text | ❌ |
| `doi` | DOI | DOI Input | ✅ |

---

#### `ejournal_no_doi` — บทความวารสารออนไลน์ (ไม่มี DOI)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| ... (เหมือน ejournal_doi) | | | |
| `url` | URL | URL Input | ✅ |

---

#### `ejournal_print` — วารสารออนไลน์ (มีฉบับพิมพ์)
Fields เหมือน `ejournal_no_doi`

---

#### `ejournal_only` — วารสารออนไลน์ล้วน (ไม่มีฉบับพิมพ์)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `article_title` | ชื่อบทความ | Text | ✅ |
| `journal_name` | ชื่อวารสาร | Text | ✅ |
| `volume` | ปีที่/เล่มที่ | Number | ❌ |
| `issue` | ฉบับที่ | Number | ❌ |
| `url` | URL | URL Input | ✅ |

---

### 27.3 หมวด Reference (พจนานุกรม/สารานุกรม) — 4 ประเภท

#### `dictionary` — พจนานุกรม (ไม่มีผู้แต่ง)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `title` | ชื่อพจนานุกรม | Text | ✅ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `edition` | ครั้งที่พิมพ์ | Number | ❌ |
| `publisher` | สำนักพิมพ์ | Text | ✅ |
| `place` | สถานที่พิมพ์ | Text | ❌ |

**หมายเหตุ:** ไม่มี author field — title ขึ้นก่อน

---

#### `dictionary_online` — พจนานุกรมออนไลน์
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `entry_word` | คำศัพท์ | Text | ✅ |
| `year` | ปีพิมพ์/อัปเดต | Year Input | ✅ |
| `dictionary_name` | ชื่อพจนานุกรม | Text | ✅ |
| `url` | URL | URL Input | ✅ |
| `accessed_date` | วันที่สืบค้น | Date | ❌ (เฉพาะเมื่อเนื้อหาเปลี่ยนบ่อย) |

---

#### `encyclopedia` — สารานุกรม
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้เขียนหัวข้อ | Author Editor | ❌ |
| `year` | ปีพิมพ์ | Year Input | ✅ |
| `entry_title` | ชื่อหัวข้อ | Text | ✅ |
| `encyclopedia_name` | ชื่อสารานุกรม | Text | ✅ |
| `volume` | เล่มที่ | Number | ❌ |
| `pages` | หน้า | Text | ❌ |
| `publisher` | สำนักพิมพ์ | Text | ✅ |
| `place` | สถานที่พิมพ์ | Text | ❌ |

---

#### `encyclopedia_online` — สารานุกรมออนไลน์
Fields เหมือน encyclopedia แต่แทน publisher/place ด้วย `url` + `accessed_date`

---

### 27.4 หมวด Newspapers (หนังสือพิมพ์) — 2 ประเภท

#### `newspaper_print` — หนังสือพิมพ์แบบรูปเล่ม
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้เขียน | Author Editor | ❌ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ✅ |
| `day` | วัน | Number | ✅ |
| `article_title` | ชื่อข่าว/บทความ | Text | ✅ |
| `newspaper_name` | ชื่อหนังสือพิมพ์ | Text | ✅ |
| `pages` | หน้า | Text | ❌ |

**APA7 Output:** `ผู้เขียน. (ปี, เดือน วัน). ชื่อข่าว. *หนังสือพิมพ์*, หน้า.`

---

#### `newspaper_online` — หนังสือพิมพ์ออนไลน์
Fields เหมือน print แต่แทน `pages` ด้วย `url`

---

### 27.5 หมวด Reports (รายงาน) — 4 ประเภท

#### `report` — รายงาน (บุคคล)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `title` | ชื่อรายงาน | Text | ✅ |
| `report_number` | เลขที่รายงาน | Text | ❌ |
| `institution` | หน่วยงาน | Text | ✅ |
| `place` | สถานที่ | Text | ❌ |

---

#### `research_report` — รายงานการวิจัย
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้วิจัย | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `title` | ชื่อรายงานวิจัย | Text | ✅ |
| `institution` | หน่วยงาน | Text | ✅ |
| `place` | สถานที่ | Text | ❌ |

---

#### `government_report` — รายงานหน่วยงานราชการ/องค์กร
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `organization` | ชื่อหน่วยงาน | Text | ✅ |
| `year` | ปี | Year Input | ✅ |
| `title` | ชื่อรายงาน | Text | ✅ |
| `report_number` | เลขที่รายงาน | Text | ❌ |
| `url` | URL | URL Input | ❌ |

---

#### `institutional_report` — รายงานบุคคลสังกัดหน่วยงาน
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `title` | ชื่อรายงาน | Text | ✅ |
| `institution` | สังกัด | Text | ✅ |
| `url` | URL | URL Input | ❌ |

---

### 27.6 หมวด Conferences (งานประชุม) — 3 ประเภท

#### `conference_proceeding` — เอกสารประชุม (มี Proceeding)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้นำเสนอ | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `paper_title` | ชื่อบทความ | Text | ✅ |
| `editors` | บรรณาธิการ Proceedings | Text | ❌ |
| `proceeding_title` | ชื่อ Proceedings | Text | ✅ |
| `pages` | หน้า | Text | ❌ |
| `publisher` | สำนักพิมพ์ | Text | ✅ |
| `place` | สถานที่พิมพ์ | Text | ❌ |

---

#### `conference_no_proceeding` — เอกสารประชุม (ไม่มี Proceeding)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้นำเสนอ | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ❌ |
| `paper_title` | ชื่อบทความ | Text | ✅ |
| `conference_name` | ชื่อการประชุม | Text | ✅ |
| `location` | สถานที่จัด | Text | ❌ |

---

#### `conference_presentation` — การนำเสนอ/โปสเตอร์
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้นำเสนอ | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ❌ |
| `presentation_title` | ชื่อการนำเสนอ | Text | ✅ |
| `presentation_type` | ประเภท (Paper/Poster/Symposium) | Select | ✅ |
| `conference_name` | ชื่อการประชุม | Text | ✅ |
| `location` | สถานที่จัด | Text | ❌ |

---

### 27.7 หมวด Theses (วิทยานิพนธ์) — 3 ประเภท

#### `thesis_unpublished` — วิทยานิพนธ์ไม่ตีพิมพ์
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `title` | ชื่อวิทยานิพนธ์ | Text | ✅ |
| `degree_type` | ระดับปริญญา | Select (bachelor/master/doctoral) | ✅ |
| `institution` | มหาวิทยาลัย | Text | ✅ |
| `place` | จังหวัด/ที่ตั้ง | Text | ❌ |

**APA7 Output:** `ผู้แต่ง. (ปี). *ชื่อวิทยานิพนธ์* [วิทยานิพนธ์ปริญญาXXไม่ได้ตีพิมพ์]. มหาวิทยาลัย.`

---

#### `thesis_website` — วิทยานิพนธ์จากเว็บไซต์
Fields เหมือน unpublished แต่แทน `place` ด้วย `url`

---

#### `thesis_database` — วิทยานิพนธ์จากฐานข้อมูล
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `title` | ชื่อวิทยานิพนธ์ | Text | ✅ |
| `degree_type` | ระดับปริญญา | Select | ✅ |
| `institution` | มหาวิทยาลัย | Text | ✅ |
| `database_name` | ชื่อฐานข้อมูล | Text | ✅ |
| `accession_number` | Accession Number | Text | ❌ |

---

### 27.8 หมวด Online (ออนไลน์) — 5 ประเภท

#### `webpage` — เว็บเพจ/เอกสารอิเล็กทรอนิกส์
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้แต่ง/เจ้าของ | Author Editor | ❌ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ❌ |
| `day` | วัน | Number | ❌ |
| `page_title` | ชื่อหน้า/เอกสาร | Text | ✅ |
| `website_name` | ชื่อเว็บไซต์ | Text | ❌ |
| `url` | URL | URL Input | ✅ |

---

#### `social_media` — สื่อออนไลน์/โซเชียลมีเดีย
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้โพสต์ | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ❌ |
| `day` | วัน | Number | ❌ |
| `content_title` | ชื่อเนื้อหา | Text | ✅ |
| `platform` | Platform (Facebook/X/Instagram/etc.) | Text | ✅ |
| `url` | URL | URL Input | ✅ |

---

#### `royal_gazette` — ราชกิจจานุเบกษาออนไลน์
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `title` | ชื่อกฎหมาย/ประกาศ | Text | ✅ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ❌ |
| `day` | วัน | Number | ❌ |
| `volume` | เล่ม | Number | ❌ |
| `section` | ตอนที่ | Text | ❌ |
| `pages` | หน้า | Text | ❌ |
| `url` | URL | URL Input | ✅ |

---

#### `patent_online` — สิทธิบัตรออนไลน์
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `inventors` | ผู้ประดิษฐ์ | Text | ✅ |
| `year` | ปี | Year Input | ✅ |
| `patent_title` | ชื่อสิทธิบัตร | Text | ✅ |
| `patent_number` | หมายเลขสิทธิบัตร | Text | ✅ |
| `patent_office` | สำนักงานสิทธิบัตร | Text | ✅ |
| `url` | URL | URL Input | ❌ |

---

#### `personal_communication` — การติดต่อส่วนบุคคล
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `communicator_name` | ชื่อผู้ให้ข้อมูล | Text | ✅ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ✅ |
| `day` | วัน | Number | ✅ |
| `communication_type` | ประเภท (สัมภาษณ์/อีเมล/โทรศัพท์) | Select | ✅ |

**หมายเหตุ APA7:** ไม่ปรากฏในรายการอ้างอิง — ใช้เฉพาะ in-text citation เท่านั้น
**Output:** "(ชื่อผู้ให้ข้อมูล, personal communication, วัน เดือน ปี)"

---

### 27.9 หมวด Media (สื่อภาพ/เสียง) — 6 ประเภท

#### `infographic` — อินโฟกราฟิก
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้สร้าง | Author Editor | ❌ |
| `year` | ปี | Year Input | ✅ |
| `title` | ชื่อ | Text | ✅ |
| `website_name` | เว็บไซต์/แหล่งที่มา | Text | ❌ |
| `url` | URL | URL Input | ✅ |

---

#### `slides_online` — สไลด์/เอกสารการสอนออนไลน์
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `authors` | ผู้สร้าง | Author Editor | ✅ |
| `year` | ปี | Year Input | ✅ |
| `title` | ชื่อสไลด์ | Text | ✅ |
| `platform` | แพลตฟอร์ม (SlideShare/GoogleSlides/etc.) | Text | ❌ |
| `url` | URL | URL Input | ✅ |

---

#### `webinar` — สัมมนาออนไลน์
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `presenters` | ผู้นำเสนอ | Text | ✅ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ❌ |
| `day` | วัน | Number | ❌ |
| `webinar_title` | ชื่อสัมมนา | Text | ✅ |
| `organization` | ผู้จัด | Text | ✅ |
| `url` | URL | URL Input | ✅ |

---

#### `youtube_video` — วิดีโอ YouTube/ออนไลน์
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `channel_name` | ชื่อช่อง | Text | ✅ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ❌ |
| `day` | วัน | Number | ❌ |
| `video_title` | ชื่อวิดีโอ | Text | ✅ |
| `url` | URL | URL Input | ✅ |

**APA7 Output:** `ชื่อช่อง. (ปี, เดือน วัน). *ชื่อวิดีโอ* [วิดีโอ]. YouTube. URL`

---

#### `podcast` — พอดแคสต์ (จบตอน)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `host` | ผู้ดำเนินรายการ | Text | ✅ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ❌ |
| `day` | วัน | Number | ❌ |
| `episode_title` | ชื่อตอน | Text | ✅ |
| `podcast_name` | ชื่อพอดแคสต์ | Text | ✅ |
| `url` | URL | URL Input | ✅ |

---

#### `podcast_series` — พอดแคสต์ (หลายตอน)
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `host` | ผู้ดำเนินรายการ | Text | ✅ |
| `year_start` | ปีเริ่มต้น | Year Input | ✅ |
| `year_end` | ปีสิ้นสุด (หรือปัจจุบัน) | Year Input | ❌ |
| `podcast_name` | ชื่อพอดแคสต์ | Text | ✅ |
| `producer` | ผู้ผลิต | Text | ❌ |
| `url` | URL | URL Input | ✅ |

---

### 27.10 หมวด Others — 1 ประเภท

#### `ai_generated` — เนื้อหาที่สร้างโดย AI
| Field | Label TH | ประเภท | Required |
|-------|----------|--------|----------|
| `ai_name` | ชื่อ AI | Text | ✅ |
| `year` | ปี | Year Input | ✅ |
| `month` | เดือน | Month Select | ❌ |
| `day` | วัน | Number | ❌ |
| `prompt_description` | คำอธิบาย prompt | Text | ✅ |
| `version` | เวอร์ชัน | Text | ❌ |
| `url` | URL (ถ้ามี) | URL Input | ❌ |

**APA7 Output:** `ชื่อ AI (เวอร์ชัน). (ปี, เดือน วัน). *prompt_description* [Large language model]. URL`

---

### 27.11 ฟีเจอร์พิเศษ — Secondary Source (อ้างถึงใน)

**ทุก resource type** มีตัวเลือก toggle: **"แหล่งทุติยภูมิ (อ้างถึงใน)"**

เมื่อเปิด จะเพิ่ม fields:
| Field | Label TH | ประเภท |
|-------|----------|--------|
| `orig_author` | ผู้แต่งต้นฉบับ | Text |
| `orig_year` | ปีของต้นฉบับ | Year Input |

**in-text output:** `(ผู้แต่งต้นฉบับ, ปีต้นฉบับ, อ้างถึงใน ผู้แต่งแหล่งที่พบ, ปี)`

---

## 28. Author Types — ครบทุกประเภทเหมือนเดิม

### 28.1 ประเภทผู้แต่งทั้ง 9 ประเภท

| Code | Label TH | Label EN | พฤติกรรม APA7 |
|------|----------|----------|--------------|
| `general` | ทั่วไป | General | ชื่อ + นามสกุล → EN: Last, F. M. / TH: ชื่อ นามสกุล |
| `anonymous` | ไม่ปรากฏชื่อ | Anonymous | แสดงเป็น "ไม่ปรากฏชื่อผู้แต่ง" หรือ "Anonymous" |
| `pseudonym` | นามแฝง | Pseudonym | แสดง display name ตรงๆ ไม่แปลง |
| `royal` | ราชสกุล | Royal Name | แสดง display name (พระนามเต็ม) |
| `nobility` | บรรดาศักดิ์ | Nobility Title | แสดง display name |
| `monk` | พระสงฆ์ | Buddhist Monk | แสดง display name (ฉายา/สมณศักดิ์) |
| `editor` | บรรณาธิการ | Editor | ชื่อ + `(บ.ก.)` / `(Ed.)` / `(Eds.)` หลังชื่อสุดท้าย |
| `organization` | หน่วยงาน | Organization | แสดง organization name ตรงๆ ไม่แปลง |
| `translator` | ผู้แปล | Translator | ชื่อ + ` (ผู้แปล)` / ` (Trans.)` |

### 28.2 Author Input Fields (ทุกประเภทยกเว้น organization/anonymous)

```
┌─────────────────────────────────────────────────────┐
│ [Select Type ▼] [ชื่อต้น] [ชื่อกลาง] [นามสกุล]    │
│                   [Display Name (สำหรับ special types)] │
│ [+ เพิ่มผู้แต่ง] [✤ drag to reorder]               │
└─────────────────────────────────────────────────────┘
```

### 28.3 กฎพิเศษ Author Formatting

**ไทย (APA7 ฉบับไทย):**
- General: ชื่อต้น [ชื่อกลาง] นามสกุล (ไม่ใช้ initials)
- 2 คน: ชื่อ1 และ ชื่อ2
- 3-20 คน: ชื่อ1, ชื่อ2, ... และ ชื่อสุดท้าย
- 21+ คน: 19 คนแรก, . . . ชื่อสุดท้าย
- หมด: แทนที่ด้วย "และคณะ"

**อังกฤษ (APA7 Standard):**
- General: Last, F. M.
- 2 คน: Last1, F. M., & Last2, F. M.
- 3-20 คน: Last1, F. M., Last2, F. M., ... & LastN, F. M.
- 21+ คน: 19 คนแรก, ... LastN (et al. เฉพาะ in-text)
- in-text 3+: Last et al. (จากครั้งแรก — APA7 2020)

---

## 29. Export to Word — Upgrade Plan V3

> **เป้าหมาย:** Export .docx ที่ถูกต้องตามมาตรฐาน TH academic เหมือนทำใน Word เป๊ะๆ

### 29.1 ปัญหาใน Export เดิม

| ปัญหา | อาการ | สาเหตุ |
|-------|-------|--------|
| Thai Unicode encoding | ตัวอักษรขาด/ผิดรูป บาง font | Unicode normalization ไม่ครบ (zero-width chars) |
| Tab indent เป๊ะ | paragraph indent ไม่สม่ำเสมอ | setValue ใส่ `\t` แต่ word tab stop ไม่ตรง |
| Bibliography hanging indent | ไม่มี hanging indent ใน .docx | TemplateProcessor ไม่รองรับ paragraph style |
| Font fallback | ใช้ font อะไรก็ได้ที่ template มี | Template .docx ต้องตั้ง font ถูกต้อง |
| Line spacing | ระยะบรรทัดไม่ถูกตาม format | Template กำหนดใน Word ต้องตรวจสอบ |
| Bibliography sorting | Sort order อาจไม่ถูก | ตัวหนังสือไทยอยู่ก่อนอังกฤษ ✓ แต่ต้องทดสอบ |
| HTML tags ใน .docx | `<i>`, `<b>` ปรากฏเป็นข้อความ | bibliography_text เก็บ HTML แต่ Word ต้องการ OOXML |

### 29.2 แผนแก้ไข V3 — Export System

#### Fix 1: HTML → OOXML Conversion
bibliography_text ที่เก็บในฐานข้อมูลมี HTML tags (`<i>`, `<b>`) → ต้องแปลงเป็น PhpWord Run objects

```php
// V3: แปลง HTML markup เป็น PhpWord formatting
function htmlToBibWordRuns(string $htmlText): array {
    // Parse <i>text</i> → addRun(['italic' => true])
    // Parse <b>text</b> → addRun(['bold' => true])
    // Plain text → addRun()
    // Return array of ['text' => ..., 'style' => ...]
}
```

#### Fix 2: Hanging Indent สำหรับ Bibliography
APA7 กำหนด hanging indent 0.5 นิ้ว (1.27cm) สำหรับบรรณานุกรม

```php
// ใช้ PhpWord paragraph style
$bibStyle = [
    'spaceAfter' => 0,
    'spaceBefore' => 0,
    'lineHeight' => 1.0,
    'indentation' => [
        'left' => 720,      // 0.5 นิ้ว = 720 twips
        'hanging' => 720,   // hanging indent
    ],
];
```

#### Fix 3: Thai Font กำหนดใน Template
Template .docx ต้องใช้ TH Sarabun New หรือ TH Niramit AS
- ขนาด body: 16pt
- ระยะบรรทัด: 1.0
- หน้ากระดาษ: A4, margin 2.5cm ทุกด้าน

#### Fix 4: Unicode Normalization ครบถ้วน
```php
function normalizeThaiText(string $text): string {
    // Remove zero-width joiners
    $text = str_replace(["\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D", "\xEF\xBB\xBF"], '', $text);
    // Fix Sara Am (แม่ กม) ที่อาจแยกเป็น 2 char
    $text = preg_replace('/\x{0E4D}\x{0E32}/u', "\xE0\xB8\xB3", $text);
    // NFC normalization
    if (class_exists('Normalizer')) {
        $text = Normalizer::normalize($text, Normalizer::FORM_C) ?: $text;
    }
    return preg_replace('/\s+/u', ' ', trim($text));
}
```

#### Fix 5: Bibliography Block Structure ใน .docx

```
[บรรณานุกรม] ← หัวข้อ bold 16pt center
[บรรทัดว่าง]
[TH bib 1] ← 16pt Sarabun, hanging indent
[TH bib 2]
...
[EN bib 1] ← 16pt Times New Roman, hanging indent
[EN bib 2]
```

### 29.3 Template Files ที่ต้องปรับปรุง

| Template | สถานะ V3 |
|----------|----------|
| `template_academic_general.docx` | ✅ Keep + ตรวจ font/spacing |
| `template_academic_general_logo.docx` | ✅ Keep + ตรวจ |
| `template_academic_research.docx` | ✅ Keep + ตรวจ |
| `template_internship.docx` | ✅ Keep + ตรวจ |

**ทุก template ต้องทดสอบ:**
- [ ] เปิดบน Windows (Word 2019/2021)
- [ ] เปิดบน macOS (Word for Mac)
- [ ] Thai font แสดงถูกต้อง
- [ ] Hanging indent บรรณานุกรม
- [ ] HTML tags ไม่ปรากฏเป็นข้อความ
- [ ] ตัวเอียงของชื่อหนังสือ/วารสารแสดงถูกต้อง

### 29.4 Export Flow V3

```
User คลิก Export .docx
         ↓
เลือก Template (general/logo/research/internship)
         ↓
กรอก Cover Data (ชื่อรายงาน, ผู้จัดทำ, รายวิชา, สถาบัน)
         ↓
เลือก Project (แหล่ง bibliography)
         ↓
POST /api/template/export-report.php
         ↓
PHP: ดึง bibliographies จาก DB
         ↓
PHP: Sort ไทยก่อน → EN ตาม APA7 (author_sort_key, year)
         ↓
PHP: HTML → OOXML conversion (แปลง <i>, <b>)
         ↓
PHP: Apply hanging indent style
         ↓
PHP: PhpWord cloneBlock + setValue
         ↓
Return .docx file download
```

---

---

## 30. Project Setup — Folder, Git & Deployment Strategy

> **ยืนยัน (29 พ.ค. 2568):**
> - โฟลเดอร์ใหม่: `/Applications/XAMPP/xamppfiles/htdocs/babybib_new`
> - Git workflow: commit + push เหมือนเดิม (new repo)
> - ทดสอบ local XAMPP ควบคู่กับ production server
> - Deployment ต้องง่าย ไม่ติดปัญหา

---

### 30.1 Folder & URL Structure

| Environment | Path | URL |
|-------------|------|-----|
| **Local Dev** | `/Applications/XAMPP/xamppfiles/htdocs/babybib_new/` | `http://localhost/babybib_new/` |
| **Production** | `/var/www/html/babybib/` หรือ `/public_html/` | `https://yourdomain.com/` |

**Local URL examples:**
```
http://localhost/babybib_new/th/generate
http://localhost/babybib_new/en/login
http://localhost/babybib_new/admin/
http://localhost/babybib_new/api/search/smart?q=test
```

**Production URL examples:**
```
https://yourdomain.com/th/generate
https://yourdomain.com/en/login
https://yourdomain.com/admin/
```

---

### 30.2 .htaccess — รองรับทั้ง Local Subfolder + Production Root

```apache
# /babybib_new/.htaccess  (รากโปรเจค)
Options -Indexes
RewriteEngine On

# ─── Security: Block access to sensitive directories ───
RewriteRule ^(vendor|src|database|backups|logs|tmp|scripts|lang)(/|$) - [F,L]
RewriteRule ^\.env - [F,L]
RewriteRule ^composer\.(json|lock|phar) - [F,L]
RewriteRule ^package(-lock)?\.json - [F,L]
RewriteRule ^tailwind\.config\.js - [F,L]

# ─── API: ไม่ route ผ่าน front controller ───
RewriteRule ^api/ - [L]

# ─── Static assets: ปล่อยผ่านตรงๆ ───
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^ - [L]

# ─── Language prefix routing ───
# /th/... หรือ /en/... → front controller
RewriteRule ^(th|en)/(.+)$ index.php [QSA,L]

# / (root) → redirect to /th/
RewriteRule ^$ index.php [L]

# ─── Admin: ไม่มี lang prefix ───
RewriteRule ^admin/(.*)$ admin/$1 [L]
```

**จุดสำคัญ:** ไม่ต้องใช้ `RewriteBase` — ใช้ `SITE_URL` จาก `.env` แทนเพื่อรองรับทั้ง subfolder (local) และ root (production)

---

### 30.3 Front Controller (`index.php`)

```php
<?php
// index.php — รากโปรเจค
require_once __DIR__ . '/src/Config/env.php';
require_once __DIR__ . '/src/Config/config.php';
require_once __DIR__ . '/src/Core/Session.php';
require_once __DIR__ . '/src/Helpers/functions.php';
require_once __DIR__ . '/src/Helpers/i18n.php';

// ─── Parse URL ───
$requestUri = $_SERVER['REQUEST_URI'];
// Strip SITE_BASE_PATH (e.g. /babybib_new) จาก URI
$basePath   = parse_url(SITE_URL, PHP_URL_PATH) ?? '';
$path       = '/' . ltrim(substr($requestUri, strlen($basePath)), '/');
$path       = strtok($path, '?');  // remove query string

// ─── Detect language from path ───
$lang = 'th';  // default
$page = '';

if (preg_match('#^/(th|en)/(.*)$#', $path, $m)) {
    $lang = $m[1];
    $page = rtrim($m[2], '/') ?: 'home';
} elseif ($path === '/' || $path === '') {
    // Redirect root → default language
    header('Location: ' . SITE_URL . '/th/');
    exit;
}

define('CURRENT_LANG', $lang);
define('CURRENT_PAGE', $page);
session_start_safe();
$_SESSION['lang'] = $lang;

// ─── Load translations ───
$translations = require __DIR__ . '/lang/' . $lang . '.php';

// ─── Route to page ───
$pageFile = __DIR__ . '/src/Pages/' . $page . '.php';
if (!file_exists($pageFile)) {
    http_response_code(404);
    require __DIR__ . '/src/Pages/errors/404.php';
    exit;
}

require $pageFile;
```

---

### 30.4 SITE_URL — Auto-detect Local vs Production

```php
// src/Config/config.php
$envSiteUrl = env('SITE_URL');
if ($envSiteUrl) {
    define('SITE_URL', rtrim($envSiteUrl, '/'));
} else {
    // Auto-detect
    $proto   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script  = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $base    = rtrim(str_replace('index.php', '', $script), '/');
    define('SITE_URL', $proto . '://' . $host . $base);
}
```

**`.env` Local:**
```env
SITE_URL=http://localhost/babybib_new
```

**`.env` Production:**
```env
SITE_URL=https://yourdomain.com
```

เมื่อ `url('generate')` → Local: `http://localhost/babybib_new/th/generate` / Production: `https://yourdomain.com/th/generate` ✓

---

### 30.5 Git Workflow

#### Repository Setup
```bash
# สร้าง repo ใหม่บน GitHub ชื่อ "babybib_new" (หรือ "babybib-v3")
# แล้วใน local:
cd /Applications/XAMPP/xamppfiles/htdocs/babybib_new
git init
git remote add origin git@github.com:thnakon/babybib_new.git
git branch -M main
```

#### .gitignore สำหรับ babybib_new
```gitignore
# Environment
.env
.DS_Store

# Dependencies
/vendor/
/node_modules/

# Build output (compiled CSS)
/assets/css/app.css

# User uploads
/uploads/avatars/*
!/uploads/avatars/.gitkeep

# Logs & cache
/logs/*.log
/tmp/babybib_search_cache/
/tmp/babybib_rate/
/backups/*.sql.gz

# PhpWord temp
/tmp/*.docx
/tmp/*.xml

# Test files
*.test.php
test_*.php
```

**หมายเหตุ:** `assets/css/app.css` (compiled Tailwind) **ไม่ commit** — build บน server แทน

#### Branch Strategy
```
main         ← production-ready code เท่านั้น
dev          ← development branch (เปิด PR เข้า main)
feature/xxx  ← feature branches
```

#### Commit Flow
```bash
# พัฒนาใน dev branch
git checkout -b dev
git add -p                    # stage เลือก changes
git commit -m "feat: add generate page layout"
git push origin dev

# เมื่อพร้อม → merge to main
git checkout main
git merge dev --no-ff
git push origin main
```

---

### 30.6 Deployment Strategy — ง่าย ไม่ติดปัญหา

#### Option A: Git Pull on Server (แนะนำ — ง่ายที่สุด)

```bash
# บน production server (SSH)
cd /var/www/html/babybib_new
git pull origin main

# Build CSS
npm run build

# ไม่ต้อง restart server (PHP + Apache)
```

**ข้อดี:** ง่าย ไม่มีความเสี่ยง, rollback ได้ด้วย `git checkout <commit>`
**ข้อเสีย:** ต้อง install git + node บน server

---

#### Option B: rsync (เร็ว + เสถียร)

```bash
# สร้าง deploy script: scripts/deploy.sh
#!/bin/bash
# Build ก่อน
npm run build

# rsync ไป server (exclude sensitive files)
rsync -avz --delete \
  --exclude '.env' \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude 'uploads/' \
  --exclude 'backups/' \
  --exclude 'logs/' \
  --exclude 'tmp/' \
  ./ user@yourserver.com:/var/www/html/babybib_new/

# Install dependencies บน server
ssh user@yourserver.com "cd /var/www/html/babybib_new && composer install --no-dev --optimize-autoloader"

echo "✅ Deploy complete"
```

**ข้อดี:** ไม่ต้องมี git บน server, เร็ว, เลือก exclude ได้
**ข้อเสีย:** ต้อง run ด้วยตนเอง

---

#### Option C: GitHub Actions CI/CD (อัตโนมัติ)

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Build Tailwind
        run: |
          npm ci
          npm run build

      - name: Deploy via rsync
        uses: burnett01/rsync-deployments@7.0.1
        with:
          switches: -avz --delete --exclude='.env' --exclude='uploads/' --exclude='backups/' --exclude='tmp/' --exclude='logs/'
          path: ./
          remote_path: /var/www/html/babybib_new/
          remote_host: ${{ secrets.DEPLOY_HOST }}
          remote_user: ${{ secrets.DEPLOY_USER }}
          remote_key: ${{ secrets.SSH_PRIVATE_KEY }}

      - name: Post-deploy: Composer install
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.DEPLOY_HOST }}
          username: ${{ secrets.DEPLOY_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/html/babybib_new
            composer install --no-dev --optimize-autoloader
            echo "Deploy done at $(date)"
```

**ข้อดี:** push → deploy อัตโนมัติ, ไม่ต้อง SSH ด้วยตนเอง
**ข้อเสีย:** ต้อง setup GitHub Secrets

---

### 30.7 Production Server Checklist

**ก่อน deploy ครั้งแรก:**
```bash
# บน server — ทำครั้งเดียว
mkdir -p /var/www/html/babybib_new
cd /var/www/html/babybib_new

# สร้าง .env จาก template
cp .env.example .env
nano .env  # แก้ค่า production

# สร้างโฟลเดอร์ที่ต้องการ writable
mkdir -p uploads/avatars
mkdir -p backups
mkdir -p logs
mkdir -p tmp/babybib_search_cache
mkdir -p tmp/babybib_rate

# Set permissions
chmod 755 uploads/ backups/ logs/ tmp/
chmod 700 .env

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Build CSS (ถ้า node มีบน server)
npm ci && npm run build
# หรือ commit built CSS (ถ้า server ไม่มี node)
```

**`.env` Production (ต้องตั้งค่าเหล่านี้):**
```env
SITE_URL=https://yourdomain.com
SITE_ENV=production
DEBUG_MODE=false
SESSION_COOKIE_SECURE=1

DB_HOST=localhost
DB_NAME=babybib_db
DB_USER=babybib_user       ← ไม่ใช้ root!
DB_PASS=strong_password_here

APP_KEY=random_32_char_string_here

MAIL_ENABLED=true
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your@gmail.com
SMTP_PASS=app_password

GOOGLE_BOOKS_API_KEY_1=key1here
GOOGLE_BOOKS_API_KEY_2=key2here
GOOGLE_BOOKS_API_KEY_3=key3here

OPENALEX_EMAIL=your@email.com
MAX_BIBLIOGRAPHIES=300
MAX_PROJECTS=30
SESSION_TIMEOUT=600
TIMEZONE=Asia/Bangkok
```

---

### 30.8 Database Migration — Production

```bash
# Import schema ครั้งแรก (ไม่กระทบ DB เดิมถ้าใช้ DB name ใหม่)
mysql -u root -p < database/database.sql
mysql -u root -p babybib_db < database/resource_types.sql

# ถ้ามี DB เดิมและต้องการ migrate data:
mysqldump -u root -p babybib_db > backup_old.sql       # backup เดิมก่อน
mysql -u root -p babybib_db < database/database.sql    # run schema ใหม่ (IF NOT EXISTS)
# ข้อมูลเดิมยังอยู่ครบ เพราะ schema ไม่เปลี่ยน
```

---

### 30.9 Local Dev Workflow ประจำวัน

```bash
# เปิด XAMPP → Start Apache + MySQL

# Terminal 1: CSS watch
cd /Applications/XAMPP/xamppfiles/htdocs/babybib_new
npm run dev   # watch + rebuild CSS อัตโนมัติ

# Terminal 2: Git work
git status
git add src/Pages/generate.php assets/js/apa7-formatter.js
git commit -m "fix: edition order bug in book formatter"
git push origin dev

# ทดสอบที่: http://localhost/babybib_new/th/generate
# ทดสอบ EN: http://localhost/babybib_new/en/generate
```

---

### 30.10 โครงสร้างโฟลเดอร์ที่อัปเดตตาม SITE_URL

```
/Applications/XAMPP/xamppfiles/htdocs/babybib_new/
│
├── index.php                    ← Front Controller
├── .htaccess                    ← Routing rules
├── .env                         ← SITE_URL=http://localhost/babybib_new
├── .env.example
├── .gitignore
├── composer.json
├── package.json
├── tailwind.config.js
├── postcss.config.js
│
├── src/
│   ├── Config/
│   │   ├── config.php           ← DB + SITE_URL + constants
│   │   ├── env.php
│   │   └── email-config.php
│   ├── Core/
│   │   ├── Session.php
│   │   ├── Security.php         ← CSRF, headers, sanitize
│   │   ├── Database.php         ← PDO singleton
│   │   └── Response.php         ← jsonResponse()
│   ├── Helpers/
│   │   ├── functions.php
│   │   ├── i18n.php             ← url(), langSwitchUrl(), __()
│   │   ├── EmailHelper.php
│   │   └── VisitTracker.php
│   └── Pages/                   ← PHP page files (ไม่มี lang prefix)
│       ├── home.php
│       ├── generate.php         ← ⭐ CORE
│       ├── sort.php
│       ├── summary.php
│       ├── start.php
│       ├── login.php
│       ├── register.php
│       ├── verify.php
│       ├── forgot-password.php
│       ├── reset-password.php
│       ├── privacy.php
│       ├── terms.php
│       ├── users/
│       │   ├── dashboard.php
│       │   ├── bibliography-list.php
│       │   ├── projects.php
│       │   ├── project-view.php
│       │   ├── report-template.php
│       │   ├── report-builder.php
│       │   ├── activity.php
│       │   └── profile.php
│       ├── help/
│       │   ├── author.php
│       │   ├── place.php
│       │   └── publisher.php
│       └── errors/
│           ├── 403.php
│           ├── 404.php
│           └── 500.php
│
├── admin/                       ← Admin panel (ไม่มี lang prefix)
│   ├── index.php
│   ├── users.php
│   ├── bibliographies.php
│   ├── projects.php
│   ├── announcements.php
│   ├── feedback.php
│   ├── settings.php
│   ├── backups.php
│   ├── logs.php
│   └── notifications.php
│
├── api/                         ← API endpoints (ไม่มี lang prefix)
│   ├── auth/
│   ├── bibliography/
│   ├── projects/
│   ├── search/
│   │   └── smart.php
│   ├── template/
│   ├── scraper/
│   ├── feedback/
│   ├── rating/
│   ├── support/
│   └── admin/
│
├── views/                       ← Shared PHP components (partials)
│   ├── layout/
│   │   ├── head.php             ← <head> + CSS + JS
│   │   ├── navbar-guest.php
│   │   ├── navbar-user.php
│   │   ├── sidebar-admin.php
│   │   ├── footer.php
│   │   └── footer-admin.php
│   └── components/
│       ├── toast.php
│       ├── modal.php
│       ├── loading.php
│       └── announcement-toast.php
│
├── assets/
│   ├── css/
│   │   ├── input.css            ← Tailwind source (commit)
│   │   └── app.css              ← Compiled output (ไม่ commit / build บน server)
│   ├── js/
│   │   ├── app.js               ← Preline init + global helpers
│   │   ├── apa7-formatter.js    ← APA7 formatter (bug-fixed V3)
│   │   └── tour.js              ← Onboarding tour
│   ├── images/
│   │   ├── favicon.svg
│   │   └── Chiang_Mai_University.svg.png
│   └── templates/               ← DOCX templates
│       ├── template_academic_general.docx
│       ├── template_academic_general_logo.docx
│       ├── template_academic_research.docx
│       └── template_internship.docx
│
├── lang/
│   ├── th.php
│   └── en.php
│
├── database/
│   ├── database.sql
│   ├── resource_types.sql
│   └── babybib_db.sql
│
├── uploads/
│   └── avatars/
│       └── .gitkeep
│
├── backups/
│   └── .htaccess
│
├── logs/
│   ├── .htaccess
│   └── .gitkeep
│
├── tmp/
│   ├── babybib_search_cache/
│   └── babybib_rate/
│
├── vendor/                      ← Composer (ไม่ commit)
├── node_modules/                ← npm (ไม่ commit)
│
└── scripts/
    ├── backup_database.sh
    ├── deploy.sh                ← rsync deploy script
    └── build.sh                 ← npm run build wrapper
```

---

### 30.11 สรุป Deployment Decision

| ข้อ | Decision |
|-----|----------|
| โฟลเดอร์ | `/Applications/XAMPP/xamppfiles/htdocs/babybib_new` |
| Git | New repository (ไม่ใช้ repo เดิม) |
| Branch | `main` (production) + `dev` (development) |
| CSS build | `npm run build` — **ไม่ commit** `app.css` |
| Deploy method | Git pull หรือ rsync (เลือกตาม server access) |
| Admin lang prefix | ❌ ไม่มี — `/admin/` ตรงๆ (ภาษาไทยล้วน ไม่มี EN) |
| API lang prefix | ❌ ไม่มี — `/api/` ตรงๆ |
| SITE_URL local | `http://localhost/babybib_new` |
| SITE_URL prod | `https://yourdomain.com` |
| DB migration | ไม่เปลี่ยน schema — `IF NOT EXISTS` ทำงานปลอดภัย |

---

---

## 31. Upgrade Opportunities — สิ่งที่เพิ่มได้ใน V3 เพื่ออัปเกรดจากเดิม

> แบ่งตาม Priority: 🔴 High Impact / 🟡 Medium / 🟢 Nice to Have
> ทุกข้อนี้ไม่ได้มีในระบบเดิม — เป็นของใหม่ทั้งหมด

---

### 31.1 🔴 Performance — ความเร็วและการโหลด

#### P1 — APCu / File Cache Upgrade สำหรับ Smart Search
**เดิม:** file-based cache ใน `/tmp/babybib_search_cache/` — I/O ช้า, race condition
**V3:**
```php
// ลำดับ fallback: APCu → File cache → No cache
function cacheGet(string $key): mixed {
    if (function_exists('apcu_fetch')) {
        $val = apcu_fetch($key, $success);
        return $success ? $val : null;
    }
    // fallback to file
    $file = TMP_DIR . '/search_cache/' . md5($key) . '.json';
    if (file_exists($file) && time() - filemtime($file) < CACHE_TTL) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}
```
**ผล:** Search response เร็วขึ้น 5-10x สำหรับ cached queries

---

#### P2 — Tailwind Purge + Minify
**V3:** ตั้ง `content` ใน `tailwind.config.js` ให้ครอบคลุมทุกไฟล์ → ขนาด CSS ลดจาก ~300KB → ~15-30KB
```bash
npm run build  # --minify → production CSS เล็กมาก
```

---

#### P3 — Lazy Load JS Modules
**เดิม:** โหลด Font Awesome, Alpine.js, Lucide ทุกหน้า (แม้ไม่ต้องการ)
**V3:**
```html
<!-- โหลด Chart.js เฉพาะ admin dashboard -->
<?php if ($page === 'admin/index'): ?>
<script src="<?= SITE_URL ?>/assets/js/chart.min.js" defer></script>
<?php endif; ?>

<!-- โหลด tour.js เฉพาะ generate page + user ใหม่ -->
<?php if ($page === 'generate' && $isNewUser): ?>
<script src="<?= SITE_URL ?>/assets/js/tour.js" defer></script>
<?php endif; ?>
```

---

#### P4 — Google Fonts Preconnect + Display Swap
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```
`display=swap` ป้องกัน FOIT (Flash of Invisible Text)

---

#### P5 — HTTP Cache Headers สำหรับ Static Assets
```apache
# .htaccess
<FilesMatch "\.(css|js|woff2|svg|png|jpg|webp)$">
    Header set Cache-Control "public, max-age=31536000, immutable"
</FilesMatch>
# ใช้ ?v=hash ใน URL เพื่อ cache busting
```

---

### 31.2 🔴 SEO — ค้นหาเจอ, แชร์สวย

#### S1 — Meta Tags ครบทุกหน้า
```php
// views/layout/head.php
<meta name="description" content="<?= $metaDescription ?? __('tagline') ?>">
<meta name="keywords" content="บรรณานุกรม, APA7, bibliography, อ้างอิง">
<meta name="author" content="Babybib">
<meta property="og:title" content="<?= $pageTitle ?>">
<meta property="og:description" content="<?= $metaDescription ?? __('tagline') ?>">
<meta property="og:image" content="<?= SITE_URL ?>/assets/images/og-image.png">
<meta property="og:url" content="<?= currentUrl() ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="<?= CURRENT_LANG === 'th' ? 'th_TH' : 'en_US' ?>">
<meta name="twitter:card" content="summary_large_image">
```

---

#### S2 — Sitemap XML
```php
// api/sitemap.php — auto-generate sitemap
// Public pages ที่ควรอยู่ใน sitemap:
// /th/, /en/, /th/generate, /en/generate, /th/start, /en/start
// /th/privacy, /en/privacy, /th/terms, /en/terms
```

---

#### S3 — robots.txt
```
User-agent: *
Allow: /th/
Allow: /en/
Disallow: /admin/
Disallow: /api/
Disallow: /users/
Sitemap: https://yourdomain.com/sitemap.xml
```

---

#### S4 — Structured Data (JSON-LD)
```html
<!-- Homepage -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Babybib",
  "description": "ระบบสร้างบรรณานุกรม APA 7th Edition",
  "applicationCategory": "EducationalApplication",
  "inLanguage": ["th", "en"],
  "offers": { "@type": "Offer", "price": "0" }
}
</script>
```

---

### 31.3 🔴 UX Upgrades — ประสบการณ์ใช้งานดีขึ้น

#### UX1 — Copy All Bibliography (คัดลอกทั้งรายการ)
**ใหม่:** ใน bibliography-list.php และ project-preview.php เพิ่มปุ่ม **"คัดลอกทั้งหมด"**
- เรียงลำดับ APA7 ก่อนคัดลอก
- Copy เป็น plain text (ไม่มี HTML tags)
- แยกไทย/อังกฤษอัตโนมัติ

---

#### UX2 — Duplicate Detection
**ปัญหาเดิม:** ผู้ใช้เพิ่มรายการซ้ำโดยไม่รู้
**V3:**
- เมื่อ save bibliography ใหม่ → ตรวจ similarity กับรายการที่มีอยู่ (title + year + author)
- ถ้าคล้ายกัน ≥ 85% → แสดง warning modal: "คุณอาจเคยบันทึกรายการนี้แล้ว"
- ให้ user เลือก: บันทึกใหม่ / ไปที่รายการเดิม / ยกเลิก

---

#### UX3 — Print-Friendly CSS สำหรับ Bibliography List
**ใหม่:** CSS สำหรับ print ที่ format ถูกต้องตาม APA7
```css
@media print {
  .navbar, .sidebar, .btn, .action-bar { display: none; }
  .bib-list { font-family: 'Times New Roman', serif; font-size: 12pt; }
  .bib-entry { text-indent: -0.5in; margin-left: 0.5in; margin-bottom: 0; }
  h1 { text-align: center; }
}
```
เพิ่มปุ่ม **"พิมพ์/Print"** บน project-preview.php

---

#### UX4 — Keyboard Shortcuts (Power Users)
```javascript
// ใน generate.php
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') generatePreview();  // Ctrl+Enter = preview
    if ((e.ctrlKey || e.metaKey) && e.key === 's') saveBibliography();     // Ctrl+S = save
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'c') copyAll();// Ctrl+Shift+C = copy
    if (e.key === 'Escape') clearForm();                                     // Esc = clear
});
```
แสดง shortcuts ใน tooltip (Preline hs-tooltip)

---

#### UX5 — Search History (localStorage)
**เดิม:** ไม่มี search history
**V3:**
- บันทึก 5 search queries ล่าสุดลง localStorage
- แสดงเป็น chip ใต้ search box
- คลิก chip → ค้นหาซ้ำทันที

---

#### UX6 — Editable Preview (Manual Override)
**มีในเดิม แต่ UX ไม่ชัด** — V3 ปรับ:
- เพิ่ม badge "แก้ไขด้วยตนเอง" เมื่อ user แก้ preview
- ปุ่ม "รีเซ็ต" เพื่อกลับไป auto-generated
- Warning: "การแก้ไขด้วยตนเองจะไม่ถูกบันทึกเป็น structured data"

---

#### UX7 — Smart Author Name Detection
**ปัญหาเดิม:** user ต้องรู้ว่าชื่อไทย/อังกฤษต่างกันอย่างไร
**V3:**
- Auto-detect ภาษาจากชื่อที่กรอก → แสดง format hint แบบ real-time
- ถ้าชื่อเป็นไทย → แสดง "ชื่อต้น + นามสกุล (ไม่ใช้ initials)"
- ถ้าชื่อเป็นอังกฤษ → แสดง "Last, F. M."

---

#### UX8 — Year Helper
**V3:**
- ปุ่ม **"ปีนี้"** ถัดจาก year field → auto-fill ปีปัจจุบัน (พ.ศ. หรือ ค.ศ.)
- Toggle: `พ.ศ.` / `ค.ศ.` → convert อัตโนมัติ
- Validation: year ต้องไม่เกินปีหน้า, ต้องไม่ต่ำกว่า 1800

---

#### UX9 — Bibliography Preview Font Options
**V3:** ใน preview box เพิ่ม font selector:
- `Times New Roman` (APA7 standard)
- `TH Sarabun New` (สำหรับ preview ภาษาไทย)
- `Angsana New`

---

#### UX10 — Bulk Operations บน Bibliography List
**ใหม่:** Checkbox multi-select บน bibliography-list.php
- เลือกหลายรายการ → **ย้ายไป Project** / **ลบ** / **Copy All**
- Select all / Deselect all

---

### 31.4 🟡 Feature Upgrades — ฟีเจอร์ใหม่

#### F1 — Smart Search ปรับปรุง: ISBN Checksum Validation
**V3:** ก่อนส่ง request ไป API → validate ISBN-10 / ISBN-13 checksum
```javascript
function validateISBN13(isbn) {
    const digits = isbn.replace(/[^0-9]/g, '');
    if (digits.length !== 13) return false;
    const sum = digits.split('').reduce((acc, d, i) => {
        return acc + parseInt(d) * (i % 2 === 0 ? 1 : 3);
    }, 0);
    return sum % 10 === 0;
}
```
**ผล:** ลด false positive ISBN → ลด unnecessary API calls

---

#### F2 — DOI Auto-format
**V3:** ใน DOI field → auto-strip prefix และ format:
- Input: `https://doi.org/10.1234/abc` หรือ `doi:10.1234/abc` หรือ `10.1234/abc`
- Store: `10.1234/abc` (clean DOI)
- Display: `https://doi.org/10.1234/abc` (full URL)

---

#### F3 — URL Validator + Auto-HTTPS
**V3:** ใน URL field:
- ตรวจ URL format → แสดง error ถ้าไม่ถูก
- Auto-prefix `https://` ถ้า user กรอกโดยไม่มี protocol
- ปุ่ม "เปิด URL" เพื่อทดสอบว่า URL ใช้งานได้

---

#### F4 — Bibliography Counter สด
**V3:** แสดงจำนวน bibliography แบบ live บน navbar:
```html
<!-- ใน navbar-user.php -->
<span class="badge"><?= $bibCount ?></span>
```
อัปเดตผ่าน AJAX ทุกครั้งที่ save ใหม่ (ไม่ต้อง reload)

---

#### F5 — Announcement System ปรับปรุง
**เดิม:** Toast แสดงครั้งเดียว session
**V3:**
- Announcement มี `is_dismissible` flag — บางอันปิดได้, บางอันปิดไม่ได้
- บันทึก dismissed announcement IDs ลง localStorage (ไม่แสดงซ้ำถ้าปิดแล้ว)
- Admin สามารถตั้ง `pinned` = แสดงทุกครั้งแม้เคยปิด

---

#### F6 — Bibliography Templates (Starter)
**ใหม่:** ใน generate.php เพิ่ม "ตัวอย่าง" สำหรับแต่ละประเภท:
- คลิก "ดูตัวอย่าง" → auto-fill form ด้วย dummy data
- ช่วยผู้ใช้ใหม่เข้าใจ format ที่ต้องกรอก

---

#### F7 — Project Color System ปรับปรุง
**เดิม:** color varchar(7) เก็บ hex
**V3:**
- 12 preset colors ให้เลือก (Preline color picker)
- แสดงสี project บน card, badge, และ sidebar

---

#### F8 — Activity Feed บน Dashboard
**V3:** แทนที่ "Recent 6 bibliographies" ด้วย activity timeline:
```
🕐 2 นาทีที่แล้ว  สร้างบรรณานุกรม "ชื่อหนังสือ..."
📂 1 ชั่วโมงที่แล้ว เพิ่มเข้าโครงการ "งานวิจัยXX"
💾 เมื่อวาน         Export โครงการ "บรรณานุกรม 2566"
```

---

#### F9 — ปุ่ม "สร้างรายการใหม่" หลัง Save (Quick Loop)
**V3:** หลัง save bibliography สำเร็จ:
- Toast: "บันทึกแล้ว! [สร้างรายการถัดไป →] [ดูรายการทั้งหมด]"
- "สร้างรายการถัดไป" → เคลียร์ form แต่เก็บ resource type และ project ไว้

---

#### F10 — Export .txt ปรับปรุง
**เดิม:** plain text ไม่มี encoding header
**V3:**
```php
header('Content-Type: text/plain; charset=UTF-8');
header('Content-Disposition: attachment; filename="bibliography_' . date('Ymd') . '.txt"');
// เพิ่ม BOM สำหรับ Windows Notepad
echo "\xEF\xBB\xBF";  // UTF-8 BOM
echo $bibText;
```
+ เพิ่ม separator ระหว่าง TH/EN section

---

### 31.5 🟡 Security Upgrades

#### SEC1 — Per-User Rate Limiting (แยกจาก per-IP)
**เดิม:** rate limit ต่อ IP เท่านั้น → ใช้ proxy หลีกเลี่ยงได้
**V3:** ถ้า logged in → rate limit ต่อ user_id ด้วย
```php
$limitKey = isLoggedIn() ? 'user_' . getCurrentUserId() : 'ip_' . $ipHash;
```

---

#### SEC2 — CSRF Token Rotation
**เดิม:** CSRF token อยู่ตลอด session
**V3:** Rotate token ทุก 30 นาที + invalidate เก่าเมื่อ form submit

---

#### SEC3 — Avatar Upload Hardening
**เดิม:** type check + resize
**V3 เพิ่ม:**
- Strip EXIF metadata (ใช้ PHP `imagecreatefromjpeg` → `imagejpeg`)
- Randomize filename (ไม่ใช้ predictable pattern)
- ตรวจ actual file content (ไม่ใช้แค่ extension/MIME from request)
- จำกัด max dimension 2000x2000px

---

#### SEC4 — Login Notification Email
**ใหม่:** เมื่อ login จาก device/IP ใหม่ → ส่ง email แจ้งเตือน (optional, user เปิด/ปิดได้)

---

#### SEC5 — Session Fixation Prevention
**V3:**
```php
// หลัง login สำเร็จเสมอ
session_regenerate_id(true);
// ลบ session เก่า + สร้างใหม่
```

---

### 31.6 🟡 Admin Panel Upgrades

#### A1 — Dashboard Charts (Chart.js v4)
**ใหม่ 4 charts:**

| Chart | Type | ข้อมูล |
|-------|------|--------|
| Visits (30 วัน) | Line | visits table daily count |
| Bibliographies (30 วัน) | Bar | bibliographies.created_at |
| Resource Types distribution | Doughnut | resource_type_id count |
| Users (ลงทะเบียนต่อเดือน) | Bar | users.created_at monthly |

```javascript
// Conditional load — เฉพาะ admin/index.php
const visitsChart = new Chart(ctx, {
    type: 'line',
    data: { labels: <?= json_encode($dates) ?>, datasets: [{
        label: 'ผู้เข้าชม',
        data: <?= json_encode($visitCounts) ?>,
        borderColor: '#8B5CF6',
        backgroundColor: 'rgba(139, 92, 246, 0.1)',
        fill: true,
        tension: 0.4
    }]},
    options: { responsive: true, plugins: { legend: { display: false } } }
});
```

---

#### A2 — Admin Bulk Actions บน User List
**ใหม่:** Multi-select users:
- Toggle `is_active` (เปิด/ปิด account)
- Force verify email
- Export user list เป็น CSV

---

#### A3 — System Health Dashboard
**ใหม่:** Tab "System" ใน Admin Settings:
- PHP version, Extensions ที่มี/ขาด
- Database size
- Disk usage: /uploads, /backups, /tmp
- Cache status (APCu / file)
- Last backup date
- API connectivity test (Google Books, CrossRef ฯลฯ)

---

#### A4 — Backup Schedule Display
**V3:** แสดงว่า backup ล่าสุดเมื่อไหร่ + แนะนำให้ backup ถ้าเกิน 7 วัน

---

### 31.7 🟢 Nice to Have

#### N1 — PWA Support (Progressive Web App)
```json
// manifest.json
{
  "name": "Babybib",
  "short_name": "Babybib",
  "start_url": "/th/",
  "display": "standalone",
  "background_color": "#FFFFFF",
  "theme_color": "#8B5CF6",
  "icons": [...]
}
```
ผู้ใช้สามารถ "Add to Home Screen" บน mobile → ใช้งานเหมือน app

---

#### N2 — Share Bibliography (Public Link)
**ใหม่:** สร้าง share link สำหรับ project:
- `/th/share/{token}` → แสดง bibliography list แบบ read-only
- Token หมดอายุใน 30 วัน (configurable)
- ไม่ต้อง login เพื่อดู

---

#### N3 — Feedback Widget (In-app)
**V3:** ปุ่ม "?" มุมขวาล่างทุกหน้า → Dropdown:
- "รายงานปัญหา" → feedback form
- "แนะนำฟีเจอร์"
- "ช่วยเหลือ" → ไปหน้า Help

---

#### N4 — Import from .bib / .ris (อนาคต)
**ไม่ทำใน V3 MVP** — วางไว้ใน roadmap:
- Import BibTeX → parse → create bibliographies ใน Babybib
- Import RIS → เหมือนกัน

---

#### N5 — OG Image Generator
**ใหม่:** Generate Open Graph image แบบ dynamic สำหรับ shared bibliography:
- PHP + GD library → สร้างรูปที่มี title + ข้อมูล + logo Babybib
- ใช้สำหรับ `/th/share/{token}` pages

---

#### N6 — Email Digest (Weekly Summary)
**ใหม่ (optional):** Admin สามารถ send weekly digest:
- จำนวน user ใหม่สัปดาห์นี้
- จำนวน bibliography สร้างใหม่
- Top resource types

---

### 31.8 สรุป — Priority Matrix

| # | Feature | Impact | Effort | Priority |
|---|---------|--------|--------|----------|
| P1 | APCu Cache | ⬆ High | Low | ✅ V3 MVP |
| P2 | Tailwind Purge | ⬆ High | Low | ✅ V3 MVP |
| P3 | Lazy Load JS | ⬆ Medium | Low | ✅ V3 MVP |
| S1 | Meta + OG Tags | ⬆ High | Low | ✅ V3 MVP |
| S2 | Sitemap | ⬆ Medium | Low | ✅ V3 MVP |
| UX1 | Copy All | ⬆ High | Low | ✅ V3 MVP |
| UX2 | Duplicate Detection | ⬆ High | Medium | ✅ V3 MVP |
| UX3 | Print CSS | ⬆ Medium | Low | ✅ V3 MVP |
| UX4 | Keyboard Shortcuts | ⬆ Medium | Low | ✅ V3 MVP |
| UX5 | Search History | ⬆ Medium | Low | ✅ V3 MVP |
| UX7 | Smart Author Detect | ⬆ High | Medium | ✅ V3 MVP |
| UX8 | Year Helper | ⬆ Medium | Low | ✅ V3 MVP |
| UX10 | Bulk Operations | ⬆ High | Medium | ✅ V3 MVP |
| F1 | ISBN Checksum | ⬆ Medium | Low | ✅ V3 MVP |
| F2 | DOI Auto-format | ⬆ Medium | Low | ✅ V3 MVP |
| F3 | URL Validator | ⬆ Medium | Low | ✅ V3 MVP |
| F9 | Quick Loop | ⬆ High | Low | ✅ V3 MVP |
| F10 | TXT Export BOM | ⬆ Medium | Low | ✅ V3 MVP |
| SEC1 | Per-User Rate Limit | ⬆ High | Low | ✅ V3 MVP |
| SEC3 | Avatar Hardening | ⬆ High | Medium | ✅ V3 MVP |
| SEC5 | Session Fixation | ⬆ High | Low | ✅ V3 MVP |
| A1 | Charts Dashboard | ⬆ Medium | Medium | ✅ V3 MVP |
| A3 | System Health | ⬆ Medium | Medium | ✅ V3 MVP |
| N1 | PWA | ⬆ Medium | Medium | 🔄 V3.1 |
| N2 | Share Link | ⬆ Medium | Medium | 🔄 V3.1 |
| N4 | Import .bib | ⬆ Low | High | 🔄 Future |
| N6 | Email Digest | ⬆ Low | High | 🔄 Future |

---

## 32. V3 Roadmap สรุป

```
V3 MVP (release แรก):
  ✅ Clean rewrite PHP Vanilla + Tailwind + Preline
  ✅ Bug fixes APA7 (10 จุด)
  ✅ Bug fixes Smart Search (9 จุด)
  ✅ i18n /th/ /en/ subdirectory
  ✅ Chart.js Admin Dashboard
  ✅ Production Security Hardening
  ✅ All 30+ resource types ครบ
  ✅ All UX upgrades ระดับ MVP (UX1-UX10)
  ✅ Performance (P1-P5)
  ✅ SEO (S1-S4)
  ✅ New APIs (DataCite, PubMed)
  ✅ Export Word ถูกต้อง (hanging indent, OOXML)

V3.1 (หลัง MVP):
  🔄 PWA (manifest + service worker)
  🔄 Share Link (/share/{token})
  🔄 Email digest

Future:
  📅 Import .bib / .ris
  📅 Citation styles อื่น (Vancouver, Chicago)
  📅 Browser extension
```

---

*อัปเดตโดย: Claude Sonnet 4.6 | วันที่: 29 พฤษภาคม 2568*
*เพิ่ม: Section 31 (Upgrade Opportunities — 32 items), Section 32 (Roadmap)*

---

## 17. Bug Audit — `apa7-formatter.js` (APA 7th Edition Violations)

> ตรวจสอบ ณ วันที่ 29 พ.ค. 2568 — ยืนยันทุกจุดจาก APA 7th Edition Manual (2020)
> **สถานะ: รอแก้ไขใน V3 (ยังไม่แตะ code เดิม)**

---

### 🔴 CRITICAL — APA7 Spec Violation (ผิดหลักการอ้างอิง)

#### Bug #1 — `formatBookAPA7` : Edition อยู่หลัง Year เมื่อไม่มีผู้แต่ง

**ฟังก์ชัน:** `formatBookAPA7(data, authorStr, lang)` — บรรทัด ~146–160

**ผลลัพธ์ปัจจุบัน (ผิด):**
```
<i>Title of Book</i>. (2023). (2nd ed.). Publisher.
```

**ผลลัพธ์ที่ถูกต้อง (APA7):**
```
<i>Title of Book</i> (2nd ed.). (2023). Publisher.
```

**สาเหตุ:** ใน `else` branch (กรณีไม่มี authorStr) มีการเพิ่ม year ก่อน แล้วค่อยเพิ่ม edition ทีหลัง

**แผนแก้ไข V3:**
- สร้าง title + edition ก่อน แล้วใส่ period + space
- จากนั้นใส่ `(year).`
- โครงสร้าง: `title + edition + ". " + "(year). " + publisher`

---

#### Bug #2 — `formatBookSeriesAPA7` : Edition/Volume อยู่หลัง Year เมื่อไม่มีผู้แต่ง

**ฟังก์ชัน:** `formatBookSeriesAPA7(data, authorStr, lang)` — บรรทัด ~185–199

**ผลลัพธ์ปัจจุบัน (ผิด):**
```
<i>Book Series Title</i>. (2023). (พิมพ์ครั้งที่ 2, เล่มที่ 3). Publisher.
```

**ผลลัพธ์ที่ถูกต้อง (APA7):**
```
<i>Book Series Title</i> (พิมพ์ครั้งที่ 2, เล่มที่ 3). (2023). Publisher.
```

**แผนแก้ไข V3:** ย้าย edition/volume parentheses มาก่อน year parenthetical ทั้งใน author และ no-author path

---

#### Bug #3 — `formatEbookDoiAPA7` และ `formatEbookNoDoiAPA7` : Edition อยู่หลัง Year เมื่อไม่มีผู้แต่ง

**ฟังก์ชัน:** `formatEbookDoiAPA7`, `formatEbookNoDoiAPA7` — บรรทัด ~265–310

**สาเหตุ:** เหมือน Bug #1 — ใช้โครงสร้าง else branch เดียวกัน

**แผนแก้ไข V3:** แก้ไขพร้อมกันกับ Bug #1 (pattern เดียวกัน)

---

#### Bug #4 — `formatDictionaryAPA7` : Edition อยู่หลัง Year และมี Double Space

**ฟังก์ชัน:** `formatDictionaryAPA7(data, lang)` — บรรทัด ~451–454

**ผลลัพธ์ปัจจุบัน (ผิด):**
```
<i>Dictionary Name</i>. (2023).  (2nd ed.). Publisher.
```
(สังเกต double space ก่อน `(2nd ed.)` และ edition อยู่หลัง year)

**ผลลัพธ์ที่ถูกต้อง (APA7):**
```
<i>Dictionary Name</i> (2nd ed.). (2023). Publisher.
```

**สาเหตุ 2 จุด:**
1. Edition เพิ่มหลัง year (ผิด)
2. `bib += ' (${...}). '` มีช่องว่างนำหน้า → double space หลัง period ของ year

**แผนแก้ไข V3:**
```
title + edition_in_parens + ". " + "(year). " + publisher
```

---

#### Bug #5 — `formatConferenceAPA7` : Type Check ผิด → Signifier ผิดเสมอ

**ฟังก์ชัน:** `formatConferenceAPA7(data, authorStr, lang, type)` — บรรทัด ~590

**โค้ดปัญหา:**
```javascript
const signifier = type === 'paper'  // ← 'paper' ไม่มีทางเป็น true เลย
    ? '[Paper presentation]'
    : `[${data.presentation_type || 'Poster'}]`;
```

**ที่ส่งค่า `type` มาจากภายนอก:**
- `conference_proceeding` → `type = 'published'`
- `conference_no_proceeding` → `type = 'no_proceeding'`
- `conference_presentation` → `type = 'presentation'`

`type === 'paper'` ไม่มีวันเป็น `true` → Paper presentation ทุกรายการจะได้ `[Poster]` หรือ `[presentation_type]` แทน

**ผลลัพธ์ปัจจุบัน (ผิด):**
```
Author. (2023, มกราคม). Title [Poster]. Conference Name, Bangkok.
```

**ผลลัพธ์ที่ถูกต้อง:**
```
Author. (2023, มกราคม). <i>Title</i> [Paper presentation]. Conference Name, Bangkok.
```

**แผนแก้ไข V3:**
```javascript
let signifier;
if (type === 'no_proceeding') {
    signifier = lang === 'th' ? '[การนำเสนอบทความ]' : '[Paper presentation]';
} else {
    // type === 'presentation'
    const presType = data.presentation_type;
    signifier = presType 
        ? `[${presType}]` 
        : (lang === 'th' ? '[โปสเตอร์]' : '[Poster presentation]');
}
```

---

#### Bug #6 — `formatConferenceAPA7` : Published Proceedings ใส่ Month/Day ใน Date

**ฟังก์ชัน:** `formatConferenceAPA7` — บรรทัด ~572

**โค้ดปัญหา:**
```javascript
// ใช้กับทุก type รวมถึง 'published'
bib += formatDateAPA7(year, data.month, data.day) + '. ';
```

**APA7 กำหนด:**
- Conference proceedings (published): ใช้ **year เท่านั้น** `(2023).`
- Conference presentation (no proceedings): ใช้ **year + month** `(2023, มกราคม).`

**ผลลัพธ์ปัจจุบัน (ผิด — กรณี published):**
```
Author. (2023, มกราคม 15). Paper title. In Editor (Ed.), Proceedings...
```

**ผลลัพธ์ที่ถูกต้อง:**
```
Author. (2023). Paper title. In Editor (Ed.), Proceedings...
```

**แผนแก้ไข V3:**
```javascript
if (type === 'published') {
    bib += `(${year}). `;
} else {
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
}
```

---

### 🟡 MEDIUM — Logic/Output Issues

#### Bug #7 — `formatAuthorsBibAPA7` : Editor Suffix Logic กรณีผสม Author+Editor

**ฟังก์ชัน:** `formatAuthorsBibAPA7` — บรรทัด ~45–52

**โค้ดปัญหา:**
```javascript
if (a.type === 'editor' || isEditor) {
    const suffix = lang === 'th' ? ' (บ.ก.)' : (authors.length > 1 ? ' (Eds.)' : ' (Ed.)');
    if (idx === authors.length - 1) name += suffix;  // ← suffix ใส่ที่คนสุดท้ายเสมอ
}
```

**กรณีปัญหา:** ถ้ามี authors แบบ mixed (บางคน type='editor' บางคนไม่ใช่) suffix จะถูกใส่ที่คนสุดท้าย แม้คนสุดท้ายจะไม่ใช่ editor

**แผนแก้ไข V3:** แยก logic — ถ้า `isEditor = true` (ทุกคนเป็น editor) → ใส่ suffix ที่คนสุดท้าย ; ถ้า `a.type === 'editor'` เฉพาะ → ใส่ suffix ต่อท้ายชื่อแต่ละคนที่เป็น editor

---

#### Bug #8 — `formatInTextCitationAPA7` : B.E. → A.D. Conversion เงื่อนไขไม่รัดกุม

**ฟังก์ชัน:** `formatInTextCitationAPA7` — บรรทัด ~931–935

**โค้ดปัญหา:**
```javascript
if (isEnglishRef && yNum > 2400) {
    yearText = String(yNum - 543);
} else if (lang === 'en' && yNum > 2400) {
    yearText = String(yNum - 543);
}
```

**ปัญหา:**
- threshold `2400` ไม่มีเหตุผลทาง logic ชัดเจน (ปี ค.ศ. 2400 ก็ถูกแปลงด้วย)
- ผู้ใช้อาจกรอก ค.ศ. ปกติเช่น `2023` — แต่ถ้า `isEnglishRef = false` และ `lang === 'en'` ก็แปลงด้วย ทำให้ได้ `1480` ซึ่งผิด
- ควรใช้ threshold `2500` (ปี พ.ศ. สูงสุดที่สมเหตุสมผล ≥ ปัจจุบัน พ.ศ. 2568)

**แผนแก้ไข V3:** ใช้ threshold `2500` และเพิ่ม condition ตรวจว่า user ได้เลือก input เป็น B.E. หรือ A.D. (จาก form field แยก)

---

#### Bug #9 — `parseGoogleBooksItem` ใน `smart_search.php` : Subtitle Empty String

**ฟังก์ชัน:** `parseGoogleBooksItem()` — บรรทัด ~881

**โค้ดปัญหา:**
```php
'title' => ($v['title'] ?? '') . (isset($v['subtitle']) ? ': ' . $v['subtitle'] : ''),
```

**ปัญหา:** `isset()` คืน `true` แม้ค่าเป็น `""` (empty string) → ชื่อหนังสือจะกลายเป็น `"Real Title: "` มีตัวคั่นท้ายโดยไม่มี subtitle จริง

**แผนแก้ไข V3:**
```php
'title' => ($v['title'] ?? '') . (!empty($v['subtitle']) ? ': ' . $v['subtitle'] : ''),
```

---

### 🟢 ENHANCEMENT — Improvements สำหรับ V3

#### Bug #10 — Missing Format Function สำหรับ `interview` / `personal_communication`

**สถานการณ์:** `personal_communication` มี resource type ใน DB (fields: `communicator_name, year, month, day, communication_type`) แต่ไม่มี format function ใน `apa7-formatter.js`

**APA7 กำหนด:** Personal communication **ไม่ปรากฏในรายการอ้างอิง (Reference List)** — อ้างได้เฉพาะ in-text citation เท่านั้น เช่น `(T. Smith, personal communication, January 15, 2023)`

**แผนแก้ไข V3:**
- เพิ่ม `formatPersonalCommunicationAPA7()` ที่คืน empty string สำหรับ reference list
- แต่เพิ่ม in-text citation format พิเศษ: `(ชื่อ, การสื่อสาร, วันที่)`

---

#### Bug #11 — `formatDictionaryOnlineAPA7` : ใช้ "สืบค้น...จาก" ซึ่ง APA7 ยกเลิกแล้ว

**ฟังก์ชัน:** `formatDictionaryOnlineAPA7` — บรรทัด ~474

**โค้ดปัญหา:**
```javascript
bib += lang === 'th' ? `สืบค้น ${data.accessed_date}, จาก ` : `Retrieved ${data.accessed_date}, from `;
```

**APA7 (2020) กำหนด:** ไม่ต้องใส่ "Retrieved from" หรือ "accessed date" สำหรับเนื้อหาที่มีเวอร์ชันถาวร (เช่น dictionary entries) — ยกเว้นเนื้อหาที่เปลี่ยนแปลงตลอดเวลา (เช่น Wikipedia, social media)

**แผนแก้ไข V3:** ลบ accessed_date ออกจาก format function default; ให้ใส่เฉพาะเมื่อ user ระบุว่าเนื้อหาอาจเปลี่ยนแปลง

---

#### Bug #12 — Author `middle` Field เป็น `undefined` กรณีมาจาก CrossRef Keyword Search

**ฟังก์ชัน:** `searchCrossRefKeyword()` ใน `smart_search.php` — บรรทัด ~1619

**โค้ดปัญหา:**
```php
$authors[] = [
    'firstName' => $given,
    'lastName'  => $family,
    'display'   => $display
    // ← ไม่มี 'middleName' key
];
```

**ผล:** `apa7-formatter.js` อ่าน `a.middle` → `undefined` → `extractInitials(undefined)` → คืน `''` → middle initial หายไป

**แผนแก้ไข V3:** เพิ่ม `'middleName' => ''` และ split `$given` ตาม space เหมือนที่ `searchCrossRef()` (DOI path) ทำ

---

## 18. Bug Audit — `api/smart_search.php` (External Database Connections)

> ตรวจสอบ ณ วันที่ 29 พ.ค. 2568
> **สถานะ: รอแก้ไข + อัปเกรดใน V3**

---

### 🔴 CRITICAL

#### Bug #1 — Google Books Thumbnail ใช้ `http://` → Mixed Content Error บน HTTPS

**ฟังก์ชัน:** `parseGoogleBooksItem()` — บรรทัด ~895

**โค้ดปัญหา:**
```php
'thumbnail' => $v['imageLinks']['thumbnail'] ?? ''
// Google Books API คืน: "http://books.google.com/..." (ไม่ใช่ https)
```

**ผล:** บน HTTPS deployment → browser บล็อก mixed content → รูปภาพหน้าปกไม่แสดง

**แผนแก้ไข V3:**
```php
'thumbnail' => isset($v['imageLinks']['thumbnail'])
    ? str_replace('http://', 'https://', $v['imageLinks']['thumbnail'])
    : ''
```

ใช้กับทุกที่ที่ thumbnail มาจาก Google Books (รวม `searchGoogleBooksByISBN`, `searchGoogleBooksThai`, `searchGoogleBooksByKeyword`)

---

#### Bug #2 — Rate Limit Comment ผิด (15 vs 30)

**บรรทัด ~42-45:**
```php
// ─── Rate Limiting (15 requests / minute / IP) ─────────
$rateLimit = 30; // max requests per minute  ← ขัดแย้งกัน
```

**แผนแก้ไข V3:** แก้ comment ให้ตรงกับค่าจริง และเพิ่ม per-user rate limit แยกออกจาก per-IP

---

### 🟡 MEDIUM

#### Bug #3 — OpenAlex ไม่ได้ใช้ Polite Pool → Rate Limit ต่ำ

**ฟังก์ชัน:** `searchOpenAlex()`, `searchOpenAlexThai()` — บรรทัด ~1042, 1511

**สถานการณ์:** OpenAlex ให้ "polite pool" ที่มี rate limit สูงขึ้นโดยเพียงแค่ส่ง `mailto:` parameter

**แผนแก้ไข V3:**
```php
// เพิ่ม mailto parameter ทุก OpenAlex URL
$mailtoParam = '&mailto=' . urlencode('admin@babybib.com');
$url = "https://api.openalex.org/works/doi:..." . $mailtoParam;
$url = "https://api.openalex.org/works?search=..." . $mailtoParam;
```
เพิ่ม `OPENALEX_EMAIL` ใน `.env`

---

#### Bug #4 — `searchByURL` เรียก scraper ผ่าน `SITE_URL` (Internal HTTP Call)

**ฟังก์ชัน:** `searchByURL()` — บรรทัด ~1101

**โค้ดปัญหา:**
```php
$scraperUrl = SITE_URL . '/api/scraper/web.php?url=' . urlencode($url);
$response = httpGet($scraperUrl, 12);
```

**ปัญหา:**
- ถ้า `SITE_URL` ผิดหรือ server ไม่ได้ listen on localhost → silent 404
- HTTP call หาตัวเอง = ไม่มีประสิทธิภาพ (network roundtrip โดยไม่จำเป็น)
- ถ้า CSP บล็อก self-referencing หรือ vhost ผิด → ล้มเหลวเงียบ

**แผนแก้ไข V3:** เปลี่ยนเป็น `require_once` โดยตรง แล้วเรียกฟังก์ชัน scraper โดยตรงแทน HTTP call:
```php
// แทนที่ HTTP call ด้วย:
require_once __DIR__ . '/../scraper/web.php'; // หรือ extract function
$scraperResult = scrapeWebPage($url);
```

---

#### Bug #5 — `parseGoogleBooksItem` : `isset()` กรณี Subtitle เป็น Empty String

(เหมือน Bug #9 ใน Section 17 — ดูรายละเอียดข้างบน)

---

#### Bug #6 — CrossRef Keyword Authors ขาด `middleName` Key

(เหมือน Bug #12 ใน Section 17 — ดูรายละเอียดข้างบน)

---

#### Bug #7 — ThaiJO Scraper ใช้ Single Subdomain (so01 เท่านั้น)

**ฟังก์ชัน:** `searchThaiJO()` — บรรทัด ~1409

**โค้ดปัญหา:**
```php
$url = "https://so01.tci-thaijo.org/index.php/index/search/search?query=" . urlencode($query);
```

**ปัญหา:** ThaiJO มีหลาย subdomain (`so01`, `so02`, `so03`, `so04`, `so05`, `li01`, `li02`, `ph01`, `ph02`, `ph03`, ...) วารสารส่วนใหญ่อยู่บน servers อื่น → ผลการค้นหา `so01` ไม่ครอบคลุม

**แผนแก้ไข V3:** ใช้ ThaiJO API endpoint หรือค้นจาก main search URL ที่ครอบคลุมทุก subdomain:
```php
// Main search endpoint (ครอบคลุมทุก journal)
$url = "https://www.tci-thaijo.org/index.php/index/search/results?query=" . urlencode($query);
```
และ/หรือ parallel search หลาย subdomain

---

#### Bug #8 — ThaiLIS Scraper พึ่งพา HTML Structure เก่า (Fragile)

**ฟังก์ชัน:** `searchThaiLIS()` — บรรทัด ~436

**ปัญหา:** Scraping `bgcolor="#EBF3F9"` เป็น HTML attribute เก่ามาก ถ้า ThaiLIS อัปเดต UI → ผิดทันที

**แผนแก้ไข V3:** ตรวจสอบว่า ThaiLIS มี API/JSON endpoint หรือไม่; ถ้าไม่มีให้ใช้ try/catch robust scraping และ return empty array ถ้า HTML structure เปลี่ยน (แทนการพัง)

---

#### Bug #9 — ไม่มี CURLOPT_ENCODING → Response อาจ Compressed แต่ Decode ไม่ได้

**ฟังก์ชัน:** `httpGet()`, `httpGetMulti()` — บรรทัด ~213, ~265

**แผนแก้ไข V3:** เพิ่ม:
```php
CURLOPT_ENCODING => '',  // '' = accept all encodings (gzip, br, deflate)
```
cURL จะ auto-decompress response

---

### 🟢 ENHANCEMENT — อัปเกรด API Sources ใน V3

#### Upgrade #1 — เพิ่ม DataCite API (DOI Resolution)

**เหตุผล:** DataCite จัดการ DOI สำหรับ datasets, preprints, grey literature ที่ CrossRef ไม่ครอบคลุม

**Endpoint:**
```
GET https://api.datacite.org/dois/{doi}
```

**ข้อมูลที่ได้:** title, authors, year, publisher, resource type
**ไม่ต้อง API Key** — ใช้ได้เลย

**แผน V3:** เพิ่ม `searchDataCite(string $doi): ?array` และเรียกหลัง CrossRef ใน `searchByDOI()`

---

#### Upgrade #2 — เพิ่ม NCBI PubMed / PMC (สำหรับบทความสุขภาพ/การแพทย์)

**เหตุผล:** วิทยานิพนธ์/บทความด้านสุขภาพของไทยหลายชิ้นอยู่ใน PubMed

**Endpoints:**
```
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term={query}&retmode=json
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&id={pmid}&retmode=json
```

**ไม่ต้อง API Key** (ควรใส่ `&email=` เพื่อ higher rate limit)

---

#### Upgrade #3 — เพิ่ม ISBN ISBN13 Validation ที่แม่นยำขึ้น

**ปัญหาเดิม:** `detectInputType()` ตรวจ ISBN แบบ regex แต่ไม่ validate checksum

**แผน V3:** เพิ่ม ISBN-10 / ISBN-13 checksum validation ก่อนส่งไปค้นหา → ลด false positive

---

#### Upgrade #4 — Cache TTL แยกตาม Query Type

**ปัจจุบัน:** ทุก query ใช้ cache 5 นาที

**แผน V3:**
```php
$cacheTTL = match($type) {
    'isbn'    => 86400,  // 24 ชั่วโมง (ISBN metadata ไม่เปลี่ยน)
    'doi'     => 3600,   // 1 ชั่วโมง
    'url'     => 300,    // 5 นาที (web content เปลี่ยนบ่อย)
    'keyword' => 600,    // 10 นาที
    default   => 300,
};
```

---

#### Upgrade #5 — เพิ่ม User-Agent ที่ดีขึ้นสำหรับ CrossRef (Polite Pool)

**CrossRef แนะนำ:** ใส่ `mailto:` ใน User-Agent เพื่อ polite pool (เหมือน OpenAlex)

**แผน V3:**
```php
CURLOPT_USERAGENT => 'Babybib/3.0 (Educational Bibliography Tool; mailto:support@babybib.com)',
```

---

#### Upgrade #6 — เพิ่ม TDC/ThaiLIS v2 API (ถ้ามี)

**แผน V3:** ตรวจสอบ API ใหม่ของ ThaiLIS/TDC หลังจากที่ระบบมีการอัปเกรดในปี 2567

---

#### Upgrade #7 — Google Books API Key Rotation ปรับให้ Deterministic

**ปัจจุบัน:** `array_rand(array_flip($validKeys))` — random ทุก request

**ปัญหา:** อาจโดน rate limit ซ้ำ key เดิมหลายครั้งติดกัน

**แผน V3:** ใช้ round-robin ตาม request count หรือ hash ของ query เพื่อกระจาย key อย่างสม่ำเสมอ

---

## 19. สรุป Bug ทั้งหมดและแผนแก้ไขใน V3

### apa7-formatter.js — Bug Summary

| # | Bug | Severity | Function | แผนแก้ไข |
|---|-----|----------|----------|----------|
| 1 | Edition หลัง Year (no-author book) | 🔴 Critical | `formatBookAPA7` | ย้าย edition ก่อน year |
| 2 | Edition หลัง Year (no-author book series) | 🔴 Critical | `formatBookSeriesAPA7` | ย้าย edition/volume ก่อน year |
| 3 | Edition หลัง Year (no-author ebook) | 🔴 Critical | `formatEbookDoiAPA7`, `formatEbookNoDoiAPA7` | ย้าย edition ก่อน year |
| 4 | Edition หลัง Year + Double Space (dictionary) | 🔴 Critical | `formatDictionaryAPA7` | ย้าย edition ก่อน year, ลบ leading space |
| 5 | Conference signifier ไม่มีวันได้ `[Paper presentation]` | 🔴 Critical | `formatConferenceAPA7` | แก้ type comparison |
| 6 | Published proceedings ใส่ month/day ใน date | 🔴 Critical | `formatConferenceAPA7` | split date format ตาม type |
| 7 | Editor suffix ผิด กรณี mixed author types | 🟡 Medium | `formatAuthorsBibAPA7` | แยก logic editor vs regular |
| 8 | B.E.→A.D. threshold 2400 ไม่รัดกุม | 🟡 Medium | `formatInTextCitationAPA7` | เพิ่ม threshold เป็น 2500, เพิ่ม form field |
| 9 | `accessed_date` ใน online dictionary (APA7 ยกเลิกแล้ว) | 🟡 Medium | `formatDictionaryOnlineAPA7` | ลบ default, ใส่เฉพาะเมื่อ user เลือก |
| 10 | ไม่มี format สำหรับ `personal_communication` | 🟢 Enhancement | — | เพิ่ม function, return empty ref list |

### smart_search.php — Bug Summary

| # | Bug | Severity | Function | แผนแก้ไข |
|---|-----|----------|----------|----------|
| 1 | Thumbnail HTTP (mixed content) | 🔴 Critical | `parseGoogleBooksItem` | `str_replace http→https` |
| 2 | Rate limit comment ผิด | 🟡 Medium | global | แก้ comment |
| 3 | OpenAlex ไม่มี polite pool | 🟡 Medium | `searchOpenAlex`, `searchOpenAlexThai` | เพิ่ม `mailto=` param |
| 4 | `searchByURL` ใช้ HTTP call หาตัวเอง | 🟡 Medium | `searchByURL` | เปลี่ยนเป็น direct function call |
| 5 | CrossRef keyword authors ขาด middleName | 🟡 Medium | `searchCrossRefKeyword` | เพิ่ม middleName key + split given |
| 6 | ThaiJO เข้าแค่ so01 subdomain | 🟡 Medium | `searchThaiJO` | ใช้ main search URL |
| 7 | ThaiLIS scraper fragile HTML | 🟡 Medium | `searchThaiLIS` | เพิ่ม robust error handling |
| 8 | ไม่มี CURLOPT_ENCODING | 🟢 Enhancement | `httpGet`, `httpGetMulti` | เพิ่ม `CURLOPT_ENCODING => ''` |
| 9 | Google Books Key Rotation แบบ random | 🟢 Enhancement | `getGoogleBooksApiKey` | เปลี่ยนเป็น round-robin |

### API Sources — อัปเกรด V3

| Source | สถานะเดิม | V3 Plan |
|--------|-----------|---------|
| DataCite | ไม่มี | **เพิ่ม** — DOI datasets/preprints |
| PubMed/NCBI | ไม่มี | **เพิ่ม** — บทความสุขภาพ |
| CrossRef Polite Pool | ไม่มี | **เพิ่ม** — mailto ใน User-Agent |
| OpenAlex Polite Pool | ไม่มี | **เพิ่ม** — mailto parameter |
| ThaiJO (multi-server) | so01 เท่านั้น | **อัปเกรด** — main search URL |
| ISBN Checksum Validation | regex เท่านั้น | **เพิ่ม** — ISBN-10/13 checksum |
| Cache TTL | 5 นาที ทุก type | **อัปเกรด** — แยกตาม query type |

---

## 20. ลำดับการแก้ไข Bug ในขั้นตอน Migration

**ก่อนเริ่ม copy ไฟล์ไป V3:**

```
Step A: แก้ Bug APA7 (#1–#6 Critical ก่อน) ใน apa7-formatter.js
Step B: แก้ Bug Smart Search (#1 Thumbnail Critical ก่อน)
Step C: ทดสอบ output ของ APA7 formatter ทุก resource type
Step D: Copy ไฟล์ที่แก้แล้วไป V3
Step E: ทยอยแก้ Bug ระดับ Medium ระหว่าง build V3
Step F: เพิ่ม API sources ใหม่ (DataCite, PubMed) ใน Phase 9
```

---

---

## 21. Code Quality Audit — เดิมแย่แค่ไหน และ V3 ควรเขียนใหม่อะไรบ้าง

> **Decision (29 พ.ค. 2568):** ถ้า code เดิมไม่สะอาดหรือไม่เสถียร → เขียนใหม่ทั้งหมดได้เลย ยังได้ฟีเจอร์เดิมครบ แต่ clean กว่า, เสถียรกว่า

---

### 21.1 Code Smell Report — ไฟล์หลัก

#### `generate.php` — **185,975 bytes (186 KB!) → เขียนใหม่ทั้งหมด**
- ไฟล์เดียวใหญ่ที่สุดของโปรเจค — รวม PHP, HTML, CSS (inline style 100+ บรรทัด), JS ทุกอย่างไว้ด้วยกัน
- Inline `<style>` หลายร้อยบรรทัดใน PHP file
- Inline `<script>` logic ยาวมาก (bibliography form, smart search, author editor, preview ทั้งหมด)
- ไม่มี separation of concerns
- ยากต่อการ debug และ maintain
- **V3 plan:** แยกเป็น: `generate.php` (PHP only) + `app/generate.js` (JS module) + Tailwind classes

---

#### `includes/header.php` — **เขียนใหม่**
- ใช้ Tailwind CDN แบบ suppress warning (`console.warn` override) — ไม่ควรทำใน production
- มี inline `tailwind.config` embed ใน HTML — ทำให้ config กระจัดกระจาย
- โหลด Lucide + Alpine + FontAwesome ทุกหน้า แม้บางหน้าไม่ต้องการ
- **V3 plan:** `src/Views/layout/head.php` พร้อม built CSS, เลือก load JS ตามหน้า

---

#### `admin/` pages (11 ไฟล์) — **เขียนใหม่ทั้งหมด**
- ทุกไฟล์มี inline CSS ซ้ำกัน (admin layout ซ้ำกัน 80%)
- ไม่มี shared layout wrapper — ทุกหน้าต้อง require header+sidebar ด้วยตัวเอง
- Table HTML ซ้ำกันทุกหน้า (ไม่มี component)
- Modal HTML ซ้ำกัน 5-6 ครั้งในไฟล์เดียว
- **V3 plan:** Preline DataTable + hs-overlay modal + shared admin layout

---

#### `assets/css/main.css` (1,973 บรรทัด) — **ยกเลิก ไม่ copy**
- CSS custom ทั้งหมด — ซ้ำซ้อนกับ Tailwind
- มี custom utility classes เกือบเหมือน Tailwind (`.flex`, `.gap-4`, `.text-center` etc.)
- ยากต่อการ maintain — ไม่รู้ว่า class ไหนใช้อยู่จริง
- **V3 plan:** ลบทั้งหมด ใช้ Tailwind utility + Preline แทน

---

#### `assets/css/components.css` + `animations.css` — **ยกเลิก ไม่ copy**
- Component CSS ทับซ้อน Preline components
- Animation ส่วนใหญ่ทำได้ด้วย Tailwind `animate-*`
- **V3 plan:** เก็บเฉพาะ animation พิเศษที่ Tailwind ไม่มี → ย้ายไป `assets/css/input.css` ใน `@layer utilities`

---

#### `assets/css/pages/` (7 ไฟล์) — **ยกเลิก ไม่ copy**
- Page-specific CSS แยกไฟล์ แต่ส่วนใหญ่เป็น layout ที่ทำด้วย Tailwind ได้
- **V3 plan:** ใช้ Tailwind utility class inline ในแต่ละ page PHP แทน

---

#### `includes/functions.php` — **เขียนใหม่บางส่วน**
- Logic ดี แต่ function ยาว + ไม่มี type hints
- `getDB()` singleton ดี แต่ควรย้ายไป `Database` class
- Helper functions รวมกันทุกอย่างในไฟล์เดียว ควรแยกตาม domain
- **V3 plan:** แยกเป็น `src/Helpers/BibHelper.php`, `src/Helpers/UserHelper.php`, etc.

---

#### `includes/session.php` — **เขียนใหม่**
- มีทั้ง session config, start, helper functions รวมกัน
- ไม่มี return type / PHPDoc
- **V3 plan:** `src/Core/Session.php` — clean class-based

---

#### `api/smart_search.php` — **เขียนใหม่โครงสร้าง แต่ reuse logic**
- ไฟล์ใหญ่ 1,940 บรรทัด รวม utility + all search functions
- Logic ดีมาก แต่ควรแยกเป็น class/module
- มี bug ยืนยันแล้ว 9 จุด (ดู Section 18)
- **V3 plan:** `src/Api/search/SmartSearch.php` (class) + แก้ bug ทั้งหมด + เพิ่ม DataCite/PubMed

---

#### `assets/js/apa7-formatter.js` — **เขียนใหม่ทั้งหมด (clean)**
- Logic ถูกต้องส่วนใหญ่ แต่มี bug สำคัญ 6 จุด (ดู Section 17)
- ไม่มี JSDoc / type annotations
- Function naming ดี แต่ parameter handling ไม่ consistent
- **V3 plan:** เขียนใหม่ทั้งไฟล์ ใช้ logic เดิมเป็น reference แต่แก้ bug ทุกจุด เพิ่ม JSDoc

---

#### `assets/js/tour.js` — **เขียนใหม่เล็กน้อย**
- Logic ดี แต่ CSS class names ต้องเปลี่ยนทั้งหมด
- **V3 plan:** เขียนใหม่ โดย reuse step definitions + DOM element selectors เดิม

---

#### `assets/js/main.js` — **เขียนใหม่ทั้งหมด**
- รวม global functions ทุกอย่าง (toast, modal, dropdown, language toggle, etc.)
- ส่วนใหญ่ทำด้วย Preline ได้
- **V3 plan:** `assets/js/app.js` — init Preline + เฉพาะ custom helpers ที่ Preline ไม่มี

---

### 21.2 ไฟล์ที่ Logic ดี — Reuse เป็น Reference (แต่เขียน clean ใหม่)

| ไฟล์ | ใช้ Logic จาก | เขียนใหม่อย่างไร |
|------|--------------|----------------|
| `api/auth/*.php` | ทั้งหมด | Copy + ปรับ path + เพิ่ม type hints |
| `api/bibliography/*.php` | ทั้งหมด | Copy + ปรับ path |
| `api/projects/*.php` | ทั้งหมด | Copy + ปรับ path |
| `api/template/*.php` | ทั้งหมด | Copy + ปรับ path (PhpWord logic ดี) |
| `api/smart_search.php` | Search logic | เขียนใหม่เป็น class, แก้ bug |
| `assets/js/apa7-formatter.js` | Format functions | เขียนใหม่ clean, แก้ bug |
| `includes/config.php` | DB config | เขียนใหม่ clean (ลด magic) |
| `includes/functions.php` | Query functions | แยก + เขียนใหม่ตาม domain |
| `lang/th.php`, `lang/en.php` | ทั้งหมด | **Copy ตรงๆ ไม่ต้องเปลี่ยน** |
| `database/database.sql` | Schema | **Copy ตรงๆ** |
| `database/resource_types.sql` | Seed data | **Copy ตรงๆ** |

---

### 21.3 สิ่งที่ดีอยู่แล้ว — เก็บไว้ทั้งหมด

| Feature | สถานะ |
|---------|-------|
| Database Schema (14 tables, indexes) | ✅ ดีมาก ไม่ต้องเปลี่ยน |
| CSRF Protection (token system) | ✅ ดี — copy pattern |
| Security Headers | ✅ ดี — copy + อัปเกรด CSP |
| Rate Limiting (file-based) | ✅ ดี — copy + แก้ comment |
| Smart Search routing logic | ✅ ดีมาก — reuse เป็น reference |
| APA7 format logic (แยก function) | ✅ ดี — reuse แต่ต้องแก้ bug |
| Email verification (OTP) | ✅ ดี — copy |
| PhpWord DOCX templates | ✅ ดีมาก — copy ตรงๆ |
| Language system (th.php/en.php) | ✅ ดี — copy ตรงๆ |
| Google Books Key Rotation | ✅ ดี — เก็บ + อัปเกรด |

---

### 21.4 Direction สรุปสำหรับ V3

```
┌─────────────────────────────────────────────────────┐
│  V3 Strategy: CLEAN REWRITE + FEATURE PARITY        │
├─────────────────────────────────────────────────────┤
│  Frontend (PHP Views + CSS):                        │
│    → เขียนใหม่ทั้งหมด ด้วย Tailwind + Preline       │
│    → ไม่ copy HTML/CSS เดิมมาเลย                    │
│                                                     │
│  Business Logic (PHP + JS):                         │
│    → ใช้ logic เดิมเป็น reference                    │
│    → เขียนใหม่ clean พร้อมแก้ bug ทุกจุด             │
│    → เพิ่ม type hints, PHPDoc, error handling        │
│                                                     │
│  Data Layer (DB + .env):                            │
│    → Copy ตรงๆ ไม่เปลี่ยน schema                   │
│    → Copy .env template                             │
│                                                     │
│  Static Assets:                                     │
│    → apa7-formatter.js: เขียนใหม่ clean (แก้ bug)   │
│    → templates/*.docx: Copy ตรงๆ                   │
│    → lang/*.php: Copy ตรงๆ                         │
│    → images/*: Copy ตรงๆ                           │
└─────────────────────────────────────────────────────┘
```

---

---

## 22. UI/UX Redesign Plan — V3 (ทำใหม่ทั้งหมด)

> **Decision:** เขียน UI/UX ใหม่ทั้งหมด ทันสมัยขึ้น ใช้งานง่ายขึ้น แต่รักษา Design Language สีม่วง + เรียบ + พรีเมียม
> ใช้ Tailwind CSS + Preline UI เป็นฐาน

---

### 22.1 Design Language V3

#### Color System (เหมือนเดิม แต่ขยาย)
```
Primary:    #8B5CF6  (Violet 500)
Accent:     #D946EF  (Fuchsia 500) — gradient pair
Surface:    #FFFFFF  background cards
Muted:      #F5F3FF  (Violet 50)  — subtle highlight
Text:       #0F172A  (Slate 900)  — body text
Text-2:     #475569  (Slate 600)  — secondary
Border:     #E2E8F0  (Slate 200)  — default border
```

#### Typography V3 (ยืนยันแล้ว)
- **Thai body:** `Tahoma` (system font, ไม่ต้อง load) — แต่ปรับให้ดูบางลง:
  ```css
  body { font-weight: 400; letter-spacing: 0.01em; }  /* ไม่ใช้ 500+ เป็น default */
  ```
- **English:** `Inter` (ไม่เปลี่ยน)
- **Brand Logo:** `Comfortaa` weight 700 (ไม่เปลี่ยน)
- **Monospace (preview box):** `ui-monospace, SFMono-Regular, Menlo, monospace` (system)

#### Spacing System
- ใช้ Tailwind spacing (4px base unit)
- Container max-width: `1280px` เหมือนเดิม
- Page padding: `px-4 sm:px-6 lg:px-8`

#### Elevation / Shadow
- Cards: `shadow-sm` (subtle)
- Modals: `shadow-xl`
- Dropdowns: `shadow-lg ring-1 ring-black/5`
- Primary button: `shadow-primary` (violet glow)

---

### 22.2 UX Pain Points เดิม → แก้ใน V3

| Pain Point | สิ่งที่ผิดในเดิม | V3 Solution |
|-----------|----------------|-------------|
| Generate page หนักมาก | PHP 186KB + form ซับซ้อน | Split step-by-step form |
| Resource type selector | Grid ปุ่มเล็กๆ หลายสิบปุ่ม | Tab category + card grid |
| Author editor | Add/remove ไม่ intuitive | Drag-to-reorder + cleaner add button |
| Smart Search | Input หาที่ยากและ UX ไม่ชัด | Search bar prominent ด้านบน form |
| Preview ดูไม่ชัด | Font, spacing ไม่ดี | Preview box styled เหมือน print |
| Save bibliography | ไม่ชัดว่าบันทึกแล้ว | Toast + animation confirm |
| Mobile generate | Split-view พัง | Accordion form + fixed preview bottom sheet |
| Dashboard ดู sparse | Stats card ใหญ่เกิน | Compact stats + activity feed |
| Admin tables | Plain HTML table | Preline DataTable with search |
| Register multi-step | ฟอร์มยาว (ทุกอย่างในหน้าเดียว) | Preline Stepper (2 steps) |
| Help pages | Static long text | Accordion FAQ + examples |
| Sort page | Copy/paste ยุ่งยาก | Textarea + drag-and-drop list |
| Profile page | Basic form only | Tab: General / Security / Danger Zone |

---

### 22.3 Redesign Per Page

---

#### 🏠 Homepage (`/index.php`) — Hero + Features

**เดิม:** Single column, basic hero
**V3:**
- Hero section: gradient background `bg-gradient-to-br from-violet-50 to-fuchsia-50`, headline ใหญ่, CTA button 2 ปุ่ม
- Features grid: 3 คอลัมน์, icon + title + description + mini preview
- Stats bar: จำนวน user, จำนวน bibliographies สร้างแล้ว, จำนวน resource types
- How it works: numbered steps (1→2→3)
- Resource types showcase: pill badges แสดงประเภทที่รองรับ
- CTA section: gradient background เชิญสมัคร

**Components:**
- `hs-tooltip` สำหรับ resource type pills
- Tailwind `animate-gradient` สำหรับ hero
- Counter animation (เมื่อ scroll ถึง)

---

#### 📝 Generate Page (`/generate.php`) — หน้าหลัก (CORE)

**เดิม:** Split view 1.2fr / 0.8fr, form ยาวมาก, resource type เป็น grid ปุ่ม
**V3 Layout:** Split view ยังอยู่ แต่ปรับ UX ทุกส่วน

**Left Column — Form Area:**
```
┌─────────────────────────────────────────┐
│ 🔍 Smart Search Bar (prominent, ด้านบน) │
│    [Search ISBN, DOI, URL, or keyword]  │
│    Results dropdown ↓                   │
├─────────────────────────────────────────┤
│ Resource Type Selector                  │
│  [Books][Journals][Theses][Online]...   │  ← Preline Tabs (horizontal scroll)
│  ┌──────┐ ┌──────┐ ┌──────┐            │
│  │📚 หนังสือ│ │📰 วารสาร│ │🎓 วิทยา │            │  ← Card grid (3 per row)
│  └──────┘ └──────┘ └──────┘            │
├─────────────────────────────────────────┤
│ Form Fields (dynamic per type)          │
│  Authors section ← collapsible card    │
│  Year + Core fields                     │
│  Additional fields ← "Show more" toggle │
├─────────────────────────────────────────┤
│ [🔗 Secondary Source] toggle           │
│ Language: [ไทย] [English]              │
│ Project: [Select project ▼]            │
│                                         │
│ [Generate Preview] [Save & Next →]      │
└─────────────────────────────────────────┘
```

**Right Column — Preview (Sticky):**
```
┌─────────────────────────────────────────┐
│ 📋 Preview                              │
│ ┌─────────────────────────────────────┐ │
│ │ [APA7 Bibliography output here]     │ │  ← Font: serif, 1.5 spacing
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ In-text Citation                        │
│  Parenthetical: (สมชาย, 2566)          │
│  Narrative: สมชาย (2566)               │
├─────────────────────────────────────────┤
│ [📋 Copy Bib] [📋 Copy Citation] [💾 Save]│
├─────────────────────────────────────────┤
│ 📖 Tips for this resource type          │
│  ข้อมูลสำคัญสำหรับการลงรายการ...       │
└─────────────────────────────────────────┘
```

**Mobile (< 768px):**
- Form column full width
- Preview เป็น bottom sheet ที่ slide up (เมื่อ preview พร้อม)
- Resource type selector: horizontal scroll pills

**UX Improvements:**
- Smart Search: แสดง loading spinner ขณะค้นหา, ผลลัพธ์เป็น card แต่ละอัน (thumbnail + title + year + source)
- Authors: เพิ่มด้วยปุ่ม `+ เพิ่มผู้แต่ง`, drag handle ✤ สำหรับ reorder, type dropdown (general/editor/etc.)
- Year field: เพิ่ม helper text "ค.ศ. หรือ พ.ศ." + validation แบบ real-time
- Required fields: highlight ด้วย `*` สีแดง + inline error message
- Onboarding tour: ปรับให้ใช้ Preline overlay แทน custom spotlight

---

#### 👤 Auth Pages

**Login (`/login.php`):**
- Clean centered card `max-w-md mx-auto`
- Logo + tagline ด้านบน
- Email + Password fields (Preline hs-input)
- "Remember me" checkbox
- Social proof: "ใช้งานโดยนักศึกษา X+ คน"
- Link forgot password

**Register (`/register.php`) — Multi-step (Preline Stepper):**
```
Step 1: ข้อมูลบัญชี
  - Username, ชื่อ, นามสกุล, Email, Password, Confirm Password
  - Password strength indicator
  - [ถัดไป →]

Step 2: ข้อมูลองค์กร
  - ประเภทองค์กร (Select)
  - ชื่อองค์กร, จังหวัด
  - LIS CMU toggle (พร้อม tooltip อธิบาย)
  - [← ก่อนหน้า] [สมัครสมาชิก ✓]
```

**Email Verify (`/verify.php`):**
- OTP input แบบ 6 ช่อง แยกกัน (แต่ละช่อง 1 ตัวเลข)
- Countdown timer สำหรับ resend
- Auto-focus ช่องถัดไปเมื่อกรอกครบ

---

#### 📊 User Dashboard (`/users/dashboard.php`)

**เดิม:** Stats cards + recent bibs list
**V3:**
```
┌──────────────────────────────────────────────────────┐
│ 👋 สวัสดี, [ชื่อ]!                    [+ สร้างใหม่] │
├────────────┬────────────┬────────────┬───────────────┤
│ 📚 XXX     │ 📂 XX      │ 📄 XXX     │ 📅 วันนี้     │
│ บรรณานุกรม │ โครงการ   │ ส่งออกแล้ว │ เข้าสู่ระบบ  │
├────────────┴────────────┴────────────┴───────────────┤
│ บรรณานุกรมล่าสุด                         [ดูทั้งหมด] │
│  ┌─────────────────────────────────────────────────┐ │
│  │ 📚 [icon] ชื่อหนังสือ...  (2023)   [แก้ไข][ลบ]│ │
│  │ 📰 [icon] ชื่อบทความ...   (2566)   [แก้ไข][ลบ]│ │
│  └─────────────────────────────────────────────────┘ │
├──────────────────────────────────────────────────────┤
│ โครงการของฉัน                           [ดูทั้งหมด] │
│  ┌─────────┐ ┌─────────┐ ┌──────────┐              │
│  │ 📂 โปรเจค│ │ 📂 โปรเจค│ │ ➕ สร้าง │              │
│  │ 12 รายการ│ │ 5 รายการ │ │ โปรเจค  │              │
│  └─────────┘ └─────────┘ └──────────┘              │
└──────────────────────────────────────────────────────┘
```

---

#### 📋 Bibliography List (`/users/bibliography-list.php`)

**เดิม:** Plain table
**V3:**
- Preline DataTable (client-side search + sort + pagination)
- Filter bar: ประเภท | ภาษา | โครงการ | ปี
- เลือกหลายรายการ (bulk delete/move)
- แต่ละ row: icon ประเภท + title preview + year + project tag + actions
- Inline copy button ทุก row

---

#### 📁 Projects (`/users/projects.php`)

**เดิม:** List แบบ plain
**V3:**
- Card grid 3 col (desktop) / 1 col (mobile)
- แต่ละ card: สี project (color picker) + ชื่อ + จำนวน bib + สร้างเมื่อ + actions
- Create card: `+ สร้างโครงการใหม่` (dashed border card)
- Click card → project-preview.php

---

#### 📄 Report Templates (`/users/report-template.php`)

**เดิม:** Template cards แบบ basic
**V3:**
- Cards ใหญ่ขึ้น พร้อม preview image/thumbnail ของแต่ละ template
- Tag badges: `ทั่วไป` / `วิจัย` / `มีโลโก้` / `ฝึกงาน`
- Hover effect: show "เลือก template นี้" overlay

---

#### 🎛️ Admin Panel

**เดิม:** Custom sidebar + plain tables
**V3:**
- `hs-sidebar` (collapsible, icon+label)
- Dashboard: stat cards + line chart (visits) + recent activity feed
- ทุก table: Preline DataTable
- ทุก form/modal: Preline hs-overlay
- Admin notifications: badge บน sidebar icon

**Admin Sidebar Structure:**
```
🏠 Dashboard
👥 ผู้ใช้
📚 บรรณานุกรม
📂 โครงการ
📢 ประกาศ
💬 Feedback
⚙️ ตั้งค่า
  └─ ทั่วไป
  └─ อีเมล
  └─ Limits
💾 สำรองข้อมูล
📋 Logs
```

---

#### ❓ Help Pages (`/help/*.php`)

**เดิม:** Long static text walls
**V3:**
- Preline `hs-accordion` สำหรับ FAQ-style
- Search ใน help (client-side filter)
- ตัวอย่างจริง: input → output preview
- Tab: ไทย / English examples

---

#### 🔄 Sort Page (`/sort.php`)

**เดิม:** Textarea paste + sort button
**V3:**
- แท็บ: `วางข้อความ` / `จาก Project`
- Paste mode: Textarea + "Sort Now" button
- Project mode: เลือก project → แสดง list → drag-to-reorder → copy
- Output: styled preview box

---

### 22.4 Component Library V3 (Preline-based)

| Component | Preline | Custom Note |
|-----------|---------|-------------|
| Navbar (guest) | `hs-navbar` | brand gradient logo |
| Navbar (user) | `hs-navbar` + `hs-dropdown` | notification badge |
| Sidebar (admin) | `hs-sidebar` | collapsible |
| Tab bar | `hs-tabs` | resource category |
| Modal / Dialog | `hs-overlay` | animated entry |
| Toast notifications | `hs-toast` | top-right stack |
| Form inputs | `hs-input` + Tailwind | violet focus ring |
| Select / Combobox | `hs-select` | author type, org type |
| DataTable | `hs-datatable` | admin tables |
| Accordion | `hs-accordion` | help pages, FAQ |
| Stepper | `hs-stepper` | register flow |
| Tooltip | `hs-tooltip` | helper hints |
| Dropdown | `hs-dropdown` | actions menu |
| Loading | Custom (keep) | branded spinner |
| Onboarding Tour | Custom (`tour.js`) | rebuilt for V3 |
| OTP Input | Custom | 6-box separated |
| Author Editor | Custom | drag-to-reorder |
| Smart Search Results | Custom | card dropdown |
| Bibliography Preview | Custom | serif + 1.5 spacing |
| Color Picker (project) | Custom | 8 preset colors |
| Language Toggle | Custom | pill style (keep) |

---

### 22.5 Animation & Micro-interaction Plan

| Element | Animation | Tailwind/Custom |
|---------|-----------|----------------|
| Page load | fade-in from below | `animate-fade-in` custom |
| Card hover | `translateY(-2px)` + shadow grow | Tailwind `transition hover:-translate-y-0.5` |
| Button click | scale down | `active:scale-95` |
| Toast | slide in from right | CSS keyframe |
| Modal open | scale + fade | Preline built-in |
| Tab switch | underline slide | CSS transition |
| Preview update | flash highlight | yellow flash → clear |
| Save success | check icon bounce | CSS keyframe |
| Stats counter | number increment | Vanilla JS |
| Navbar brand hover | gradient shift | CSS transition |

---

### 22.6 Responsive Breakpoints

| Breakpoint | Width | Layout |
|-----------|-------|--------|
| `sm` | 640px | Navbar mobile, single column |
| `md` | 768px | 2-col stats, 2-col projects |
| `lg` | 1024px | Split view generate (form+preview) |
| `xl` | 1280px | Max container, 3-col projects |

**Mobile-first approach:** ออกแบบ mobile ก่อน ขยายไป desktop

---

### 22.7 Accessibility (a11y) Plan

- ทุก interactive element มี `aria-label`
- Focus visible: `ring-2 ring-primary ring-offset-2` ทุกที่
- Color contrast: text บน primary background ≥ 4.5:1
- Form errors: `aria-describedby` เชื่อม input กับ error message
- Skip to main content link (สำหรับ screen readers)
- Keyboard navigation ทุก dropdown/modal (Preline รองรับแล้ว)

---

*อัปเดตโดย: Claude Sonnet 4.6 | วันที่: 29 พฤษภาคม 2568*
*เพิ่ม: Section 22 (UI/UX Redesign Plan — Full V3)*
