# Babybib V3 — Master Build Prompt
> เอกสารนี้คือ **คำสั่งหลัก** สำหรับสั่งให้ AI/agent ลงมือสร้างโปรเจค Babybib ใหม่ทั้งหมดจากศูนย์
> รายละเอียดเชิงลึกทุกหัวข้ออยู่ใน `docs/MIGRATION_PLAN_V3.md` — เอกสารนี้คือเวอร์ชันสั่งงานที่กลั่นมาแล้ว
> **กติกาเหล็ก:** ฟีเจอร์ต้องครบเท่าของเดิม 100% — ห้ามตัดออกแม้แต่ตัวเดียว แต่ UI/UX ต้องสะอาด ทันสมัย และเสถียรกว่าเดิม

---

## 0. บทบาทและเป้าหมาย (Role & Goal)

คุณคือวิศวกร full-stack ที่กำลัง **เขียนใหม่ทั้งหมด (Clean Rewrite)** ของระบบสร้างบรรณานุกรม APA7 ฉบับภาษาไทยชื่อ "Babybib"

**เป้าหมาย:**
- สร้างโปรเจคใหม่ในโฟลเดอร์ `babybib_new` แยกจากของเดิมโดยสิ้นเชิง
- เก็บ **ฟีเจอร์เดิมครบทุกตัว** (ดู §3 Feature Checklist)
- ใช้ **data layer เดิม** (DB schema, .env, lang files, .docx templates, รูปภาพ) — copy ตรง ๆ ไม่เปลี่ยน
- ใช้ **business logic เดิมเป็น reference** แต่เขียนใหม่ให้ clean + แก้ bug ที่ยืนยันแล้วทุกจุด (§7)
- **UI/CSS เขียนใหม่ทั้งหมด** ด้วย Tailwind + Preline — ห้าม copy HTML/CSS เดิมมาเลย
- เน้น UI ที่ **สะอาด เรียบ พรีเมียม** คงโทนสีม่วง/ชมพู

**Design Language:** สีม่วง (#8B5CF6) + ชมพู (#D946EF) gradient, ฟอนต์ไทย Tahoma (บาง), อังกฤษ Inter, โลโก้ Comfortaa, มุมโค้ง, เงานุ่ม, light mode เท่านั้น

---

## 1. หลักการทำงาน (Operating Rules) — อ่านก่อนเริ่มเสมอ

1. **ทำทีละ slice แล้วทดสอบ** — อย่าเขียนรวดเดียว 1000 บรรทัด สร้างทีละหน้า/ฟีเจอร์ แล้วเปิดทดสอบจริงบน `http://localhost/babybib_new/`
2. **Feature parity ก่อน feature ใหม่** — ทำของเดิมให้ครบและทำงานได้ก่อน แล้วค่อยเพิ่ม upgrade (§8)
3. **ความเรียบง่ายมาก่อนความฉลาด** — ใช้ Preline component สำเร็จรูปแทนการเขียน custom เว้นแต่จำเป็น (ดูตาราง §6.4)
4. **ห้ามแตะ schema ฐานข้อมูล** — copy ตรง ๆ ข้อมูลเดิมต้องใช้ต่อได้ทันที
5. **แก้ bug ที่ระบุไว้ทุกจุด (§7) ก่อน copy logic** — ห้าม copy code ที่มี bug มาทั้งดุ้น
6. **ถ้าเจอความไม่ชัดเจน/ขัดแย้ง → หยุดถาม** อย่าเดาแล้วลุยต่อ
7. **CSRF + sanitize + prepared statement ทุกที่** — security เป็น non-negotiable (§9)
8. **ทุก output ที่มาจาก user ต้อง `htmlspecialchars()`** และทุก query ต้องเป็น PDO prepared statement
9. commit เป็น atomic commit ความหมายชัด — ห้าม commit `.env`, `vendor/`, `node_modules/`, `assets/css/app.css`

---

## 2. Tech Stack (คงเดิม + อัปเกรด build)

| ชั้น | เทคโนโลยี |
|---|---|
| Backend | PHP Vanilla (ไม่มี framework) + PDO MySQL |
| CSS | **Tailwind CSS 3 (npm build, ไม่ใช่ CDN)** + (สำคัญหลักคือใช้ daisyui npm i -D daisyui@latest และ @plugin "daisyui";) |
| JS | Vanilla JS + Alpine.js 3 (form state) + Preline JS |
database Mysql
| Charts | Chart.js 4 (โหลดเฉพาะ admin dashboard) |
| Icons | Font Awesome 6 เป็นหลัก |
| Fonts | Comfortaa (brand), Inter (EN/UI), Tahoma (TH body) |
| Email | PHPMailer (Composer) |
| Export | PhpWord (Composer) — .docx |
| Search APIs | Google Books (3 keys rotation), Open Library, CrossRef, OpenAlex, Semantic Scholar, Web Scraper + **เพิ่ม DataCite, PubMed** |
| Cache | APCu → File cache fallback (`/tmp/`) |

---

## 3. Feature Checklist — ต้องครบทุกข้อ (ห้ามขาด)

> นี่คือสัญญาว่าระบบใหม่ต้องทำได้เท่าเดิม ใช้เป็น acceptance checklist

### 🔐 Authentication
- [ ] Register (username, name, surname, email, password, org_type, org_name, province, is_lis_cmu) — multi-step (Preline Stepper 2 ขั้น)
- [ ] Email Verification (OTP 6 หลัก, ช่องแยก 6 ช่อง, auto-focus, countdown resend)
- [ ] Login + rate limiting (login_attempts) + `session_regenerate_id(true)` หลัง login
- [ ] Forgot Password (OTP email) + Reset Password
- [ ] Profile Update (avatar upload → convert .webp, org info) — tab General/Security/Danger Zone
- [ ] Delete Account
- [ ] Session timeout (default 600s), CSRF token, security headers, CAPTCHA

### 📚 Bibliography Core (หัวใจของระบบ — สำคัญที่สุด)
- [ ] **30+ resource types ครบทุกตัว** ตาม field spec ใน `MIGRATION_PLAN_V3.md §27`:
  - Books (5): book, book_series, book_chapter, ebook_doi, ebook_no_doi
  - Journals (5): journal_article, ejournal_doi, ejournal_no_doi, ejournal_print, ejournal_only
  - Reference (4): dictionary, dictionary_online, encyclopedia, encyclopedia_online
  - Newspapers (2): newspaper_print, newspaper_online
  - Reports (4): report, research_report, government_report, institutional_report
  - Conferences (3): conference_proceeding, conference_no_proceeding, conference_presentation
  - Theses (3): thesis_unpublished, thesis_website, thesis_database
  - Online (5): webpage, social_media, royal_gazette, patent_online, personal_communication
  - Media (6): infographic, slides_online, webinar, youtube_video, podcast, podcast_series
  - Others (1): ai_generated
- [ ] **Author Types 9 ประเภท** (general, anonymous, pseudonym, royal, nobility, monk, editor, organization, translator) + กฎ formatting TH/EN ตาม §28
- [ ] **Author Editor** — เพิ่ม/ลบ/ลากจัดลำดับ (drag-to-reorder)
- [ ] **Secondary Source (อ้างถึงใน)** — toggle ได้ทุก type → เพิ่ม orig_author, orig_year
- [ ] **Smart Search** — auto-detect ISBN/DOI/URL/Keyword, Thai + Global layer, rate limit 30/min/IP + cache
- [ ] **Live Preview** — บรรณานุกรม + in-text citation (parenthetical + narrative) real-time
- [ ] Copy to clipboard (bib + citation), Edit mode, Year validation (พ.ศ./ค.ศ.)
- [ ] Bibliography output language toggle (ไทย/English) **แยกอิสระจาก UI language**
- [ ] Guest ใช้ได้: สร้าง + preview + copy + sort + export .txt — แต่ save/projects/export .docx ต้อง login (§ Permission Matrix)

### 📂 Projects & Library
- [ ] CRUD Projects (max 30/user), เพิ่ม bib เข้า project (max 300 bib/user), ย้าย bib ระหว่าง project
- [ ] Bibliography list (กรอง/ค้นหา/sort) — Preline DataTable + bulk select
- [ ] Export project เป็น .txt / .docx (sorted APA7)

### 📄 Report Templates (DOCX)
- [ ] template_academic_general / _logo / _research / internship
- [ ] Upload โลโก้สถาบันเอง (CMU default), เลือก project ที่จะแนบ bibliography
- [ ] **Export ถูกต้อง:** HTML→OOXML conversion, hanging indent 0.5", Thai font + Unicode normalization (§29)

### 🔄 Sort & Summary
- [ ] sort.php (paste & sort APA7 + จาก project), summary.php (bib ล่าสุด + export)

### 📊 User Dashboard
- [ ] Stats (bib/project count), recent bibs, activity history (7 วัน), announcement toast

### 👁️ Onboarding Tour
- [ ] Interactive tour สำหรับ user ใหม่ + localStorage flag (`babybib_tour_completed`)

### ℹ️ Help Pages
- [ ] help/author, help/place, help/publisher, start, privacy, terms (Accordion + ตัวอย่าง)

### 🛡️ Admin Panel (`/admin/` — ภาษาไทยล้วน ไม่มี lang prefix)
- [ ] Dashboard (stats + Chart.js: visits/bibs/resource types/users), User mgmt (CRUD + toggle active)
- [ ] Bibliography mgmt, Project mgmt, Announcements (CRUD + date range), Feedback (pending/read/resolved + response)
- [ ] System Settings (site name, limits, email config, provinces), Backup (.sql.gz create/download/delete)
- [ ] Logs, Admin notifications (unread badge)

### 📧 Email / 🌐 i18n / 🔒 Security / 📈 Analytics
- [ ] PHPMailer OTP (verify + reset), SMTP config ผ่าน admin, test email button
- [ ] i18n ไทย/English ผ่าน subdirectory `/th/` `/en/` + hreflang SEO (§5)
- [ ] CSRF, security headers (CSP ปรับสำหรับ Preline/FA/Google Fonts), rate limiting, .htaccess protect
- [ ] Visit tracking, activity logs, rating system, support/report system

---

## 4. โครงสร้างโฟลเดอร์ (Project Structure)

```
babybib_new/
├── index.php                 ← Front Controller (ทุก request หน้าเว็บผ่านที่นี่)
├── .htaccess                 ← routing /th/ /en/ + security + cache headers
├── .env / .env.example       ← copy จากเดิม
├── composer.json             ← phpmailer + phpword
├── package.json              ← tailwindcss, preline, alpinejs, chart.js
├── tailwind.config.js / postcss.config.js
│
├── src/
│   ├── Config/    → config.php, env.php, email-config.php
│   ├── Core/      → Session.php, Security.php, Database.php, Response.php
│   ├── Helpers/   → functions.php, i18n.php (url(), langSwitchUrl(), __()), EmailHelper.php, VisitTracker.php
│   └── Pages/     → home, generate, sort, summary, start, login, register, verify,
│                    forgot-password, reset-password, privacy, terms,
│                    users/*, help/*, errors/* (403,404,500)
│
├── admin/         ← admin panel (ไม่มี lang prefix, ไทยล้วน)
├── api/           ← endpoints (ไม่มี lang prefix): auth/, bibliography/, projects/,
│                    search/smart.php, template/, scraper/, feedback/, rating/, support/, admin/
├── views/
│   ├── layout/    → head, navbar-guest, navbar-user, sidebar-admin, footer, footer-admin
│   └── components/→ toast, modal, loading, announcement-toast
│
├── assets/
│   ├── css/       → input.css (commit) + app.css (build output, ไม่ commit)
│   ├── js/        → app.js (Preline init + helpers), apa7-formatter.js (เขียนใหม่ clean), tour.js
│   ├── images/    → copy จากเดิม
│   └── templates/ → *.docx copy จากเดิม
│
├── lang/          → th.php, en.php (copy ตรง ๆ)
├── database/      → database.sql, resource_types.sql (copy ตรง ๆ)
├── uploads/avatars/ , backups/ , logs/ , tmp/   (มี .htaccess/.gitkeep)
└── scripts/       → backup_database.sh, deploy.sh, build.sh
```

**กฎ routing:** `index.php` parse URL → ดึง lang (`th`/`en`) จาก prefix → ดึง page → include `src/Pages/{page}.php`
- `/` → redirect `/th/`
- `/api/...` และ `/admin/...` ไม่ผ่าน front controller (ไม่มี lang prefix)
- `SITE_URL` auto-detect local subfolder vs production root (อ่านจาก `.env`)

---

## 5. i18n — Subdirectory Strategy

- URL: `/th/generate`, `/en/login` ... default = `/th/`
- `CURRENT_LANG` = UI language (จาก URL), `BIB_LANG` = ภาษา output บรรณานุกรม (จาก form toggle, เก็บใน `$_SESSION['bib_lang']`) — **สอง toggle แยกกัน**
- Helper: `url('generate')`, `langSwitchUrl('en')`, `__($key)`
- hreflang tags + canonical ใน `head.php`
- Admin force ภาษาไทยเสมอ; API ไม่ใช้ i18n routing
- ส่งตัวแปรไป JS: `const BIB_LANG = '...'; const UI_LANG = '...';`

---

## 6. Design System (Tailwind Config)

### 6.1 สี
```
primary: #8B5CF6 (DEFAULT) / dark #7C3AED / darker #6D28D9 / light #EDE9FE + scale 50-900
accent (fuchsia): #D946EF
success #10B981 / warning #F59E0B / danger #EF4444 / info #3B82F6
surface #FFF / muted #F5F3FF / text #0F172A / text-2 #475569 / border #E2E8F0
```
gradient: `primary-gradient` (135deg #6366F1→#8B5CF6→#D946EF), `brand-gradient` (#180d36→#8B5CF6→#CF23CF→#180d36)
shadow: `primary` = `0 4px 14px 0 rgba(139,92,246,.3)`

### 6.2 ฟอนต์
- TH body: Tahoma (system, weight 400, letter-spacing 0.01em — ดูบาง)
- EN/UI: Inter (Google Fonts, preconnect + display=swap)
- Brand: Comfortaa 700 + brand-gradient clip
- Preview box: serif (Times New Roman) / monospace

### 6.3 input.css layers
- `@layer base`: body font-thai, `body.lang-en` → font-sans
- `@layer components`: `.brand-logo` (gradient clip), `.btn-primary-gradient`
- เก็บเฉพาะ animation ที่ Tailwind ไม่มีไว้ใน `@layer utilities`

### 6.4 Component mapping — ใช้ Preline แทน custom
| ใช้ Preline | เก็บ custom (Preline ไม่มี/ไม่เหมาะ) |
|---|---|
| navbar, dropdown, overlay/modal, sidebar, tabs, datatable, input, select, accordion, stepper, tooltip, toast | loading spinner (branded), onboarding tour, OTP input (6 ช่อง), author editor (drag), smart search results (card), bibliography preview, project color picker, language toggle (pill) |

**สำคัญ:** เรียก `window.HSStaticMethods.autoInit()` หลัง DOM loaded; โหลด Alpine หลัง Preline (หรือ defer)

---

## 7. Bug ที่ต้องแก้ก่อน copy logic (บังคับ)

> รายละเอียดเต็มใน `MIGRATION_PLAN_V3.md §17–§20` — ห้าม copy code ที่มี bug เหล่านี้มาทั้งดุ้น

### `apa7-formatter.js` — เขียนใหม่ clean + แก้:
1. 🔴 `formatBookAPA7` — edition ต้องอยู่**ก่อน** year เมื่อไม่มีผู้แต่ง: `title (ครั้งที่). (year). publisher`
2. 🔴 `formatBookSeriesAPA7` — ย้าย edition/volume มาก่อน year
3. 🔴 `formatEbookDoiAPA7` / `formatEbookNoDoiAPA7` — เหมือน #1
4. 🔴 `formatDictionaryAPA7` — edition ก่อน year + ลบ double space
5. 🔴 `formatConferenceAPA7` — type check ผิด (`type==='paper'` ไม่มีวันจริง) → แก้เป็น `no_proceeding`/`presentation`
6. 🔴 `formatConferenceAPA7` — published proceedings ใช้ year เท่านั้น (ไม่ใส่ month/day)
7. 🟡 `formatAuthorsBibAPA7` — editor suffix logic กรณี mixed author types
8. 🟡 `formatInTextCitationAPA7` — B.E.→A.D. ใช้ threshold 2500 + ดู form field ว่าเลือก พ.ศ./ค.ศ.
9. 🟡 `formatDictionaryOnlineAPA7` — ลบ "สืบค้น...จาก" default (APA7 ยกเลิก) ใส่เฉพาะเนื้อหาเปลี่ยนบ่อย
10. 🟢 เพิ่ม `formatPersonalCommunicationAPA7` — คืน empty ใน reference list, มีเฉพาะ in-text
11. เพิ่ม middleName key เมื่อมาจาก CrossRef keyword

### `api/search/smart.php` (เขียนใหม่เป็น class) — แก้:
1. 🔴 Google Books thumbnail `http://`→`https://` (mixed content)
2. แก้ rate limit comment (15→30) + เพิ่ม per-user rate limit
3. OpenAlex + CrossRef เพิ่ม `mailto=` polite pool
4. `searchByURL` เปลี่ยนจาก HTTP self-call เป็น direct function call
5. subtitle ใช้ `!empty()` แทน `isset()`
6. CrossRef keyword authors เพิ่ม `middleName`
7. ThaiJO ใช้ main search URL (ไม่ใช่ so01 เดียว)
8. ThaiLIS scraper robust error handling
9. เพิ่ม `CURLOPT_ENCODING => ''`
10. Google Books key rotation → round-robin
11. **เพิ่ม API ใหม่:** DataCite (DOI datasets/preprints), PubMed/NCBI (สุขภาพ)
12. ISBN-10/13 checksum validation, cache TTL แยกตาม query type

---

## 8. Upgrade ที่เพิ่มใน V3 MVP (ทำหลัง feature parity ครบ)

> รายละเอียด `MIGRATION_PLAN_V3.md §31` — ทำเฉพาะที่ติดป้าย ✅ V3 MVP

- **Performance:** APCu cache, Tailwind purge/minify, lazy-load JS (Chart.js เฉพาะ admin, tour.js เฉพาะ user ใหม่), font preconnect+swap, HTTP cache headers
- **SEO:** meta + OG tags ทุกหน้า, sitemap.xml, robots.txt, JSON-LD
- **UX:** Copy All, duplicate detection (≥85%), print CSS, keyboard shortcuts (Ctrl+Enter/S), search history, smart author name detect, year helper (พ.ศ./ค.ศ. + ปีนี้), bulk operations, quick loop หลัง save, txt export + BOM
- **Search:** ISBN checksum, DOI auto-format, URL validator + auto-https
- **Security:** per-user rate limit, CSRF token rotation, avatar hardening (strip EXIF, randomize filename, content check, max 2000px), session fixation prevention
- **Admin:** Chart.js dashboard (4 charts), bulk user actions, system health tab, backup schedule display

(เลื่อนไป V3.1: PWA, share link, email digest. อนาคต: import .bib/.ris, citation styles อื่น)

---

## 9. Security Checklist (production hardening — บังคับ)

- [ ] CSRF token ทุก `<form method=POST>` และทุก AJAX POST
- [ ] PDO prepared statements ทุก query, `htmlspecialchars()` ทุก output จาก user
- [ ] Security headers + CSP (whitelist Preline/FA/Google Fonts; img-src รวม books.google.com, covers.openlibrary.org)
- [ ] Rate limit: login 5/5min, smart_search 30/min, register 3/hour/IP — แยก per-user + per-IP
- [ ] Avatar upload: finfo MIME check, max 2MB, convert .webp, strip EXIF, randomize filename
- [ ] Session: httponly, samesite=Lax, secure (HTTPS), regenerate id on login
- [ ] `APP_KEY` ≥32 chars สำหรับ HMAC token hashing
- [ ] Admin middleware: ตรวจ `role==='admin'` ทุก admin page + API
- [ ] `.htaccess` protect: vendor, src, database, backups, logs, tmp, scripts, lang, .env, composer.*, package*.json
- [ ] Production: HTTPS redirect, display_errors off + log_errors on (นอก webroot), Options -Indexes, DB user ไม่ใช่ root

---

## 10. ลำดับการสร้าง (Build Order)

```
Phase 0  Setup: สร้าง babybib_new/, npm + composer, tailwind.config, copy data layer
         (lang, database, templates, images, .env), .htaccess + Front Controller + i18n helper
Phase 1  Core infra: Config, Database, Session, Security, functions, EmailHelper, head/footer, toast/loading
Phase 2  Layout: navbar-guest/user, sidebar-admin, footer, mobile responsive, language toggle, announcement toast
Phase 3  Auth pages + auth API (login, register stepper, verify OTP, forgot/reset, captcha)
Phase 4  Home + public (start, privacy, terms, help/*, error pages)
Phase 5  ⭐ CORE Generate page: resource selector (tabs+cards), dynamic form 30+ types, author editor,
         smart search bar, live preview, secondary source, edit mode, save (AJAX), guest mode + tour
Phase 6  User area: dashboard, bibliography-list (DataTable+bulk), projects (card grid), project-view,
         report-template, report-builder, activity, profile (tabs)
Phase 7  Sort + Summary
Phase 8  Admin panel ทั้งหมด + admin API + Chart.js
Phase 9  Export system: DOCX (PhpWord, HTML→OOXML, hanging indent), bib/project export, DataCite/PubMed
Phase 10 Polish: build --minify, cache headers, security review, CSRF test, mobile + cross-browser test
```

**Priority:** Core infra → Generate page → Auth → User area → Admin → Export → Sort/Summary → Help → Tour → Rating/Support

---

## 11. สิ่งที่ Copy ตรง ๆ vs เขียนใหม่

| Copy ตรง ๆ | เขียนใหม่ clean (logic เดิมเป็น reference) | เขียนใหม่ทั้งหมด (ห้าม copy) |
|---|---|---|
| `lang/th.php`, `lang/en.php` | `api/auth/*`, `api/bibliography/*`, `api/projects/*`, `api/template/*` (ปรับ path + type hints) | ทุก HTML/View, ทุก CSS |
| `database/*.sql` | `api/smart_search.php` → class + แก้ bug | `apa7-formatter.js` (clean + แก้ bug) |
| `assets/templates/*.docx` | `includes/functions.php` → แยกตาม domain | `assets/js/main.js` → `app.js` (Preline init) |
| `assets/images/*` | `includes/config/session/security` → `src/Core/*` | ทุกหน้าใน `src/Pages/`, `admin/`, `views/` |
| `.env`, `.env.example`, `scripts/backup_database.sh` | `tour.js` (reuse step + selector, เปลี่ยน CSS class) | `assets/css/*` (แทนด้วย Tailwind build) |

---

## 12. Definition of Done

- [ ] ทุกข้อใน §3 Feature Checklist ทำงานได้จริงบน `http://localhost/babybib_new/th/`
- [ ] resource type ทั้ง 30+ ตัว generate APA7 ถูกต้อง (ทดสอบ output ทุกตัว ทั้ง ไทย/อังกฤษ)
- [ ] bug §7 แก้ครบทุกจุด — มีหลักฐานว่า output ถูกต้องตาม APA7 manual
- [ ] i18n `/th/` `/en/` สลับได้ + bib language แยกอิสระ
- [ ] Export .docx เปิดบน Word (Win + Mac) ถูกต้อง: Thai font, hanging indent, ไม่มี HTML tag โผล่, ตัวเอียงถูก
- [ ] Security checklist §9 ครบ — CSRF/rate limit/avatar/session ทดสอบแล้ว
- [ ] Mobile responsive + cross-browser ผ่าน
- [ ] `npm run build --minify` สำเร็จ, CSS เล็ก (~15-30KB), ไม่มี Tailwind CDN warning
- [ ] ข้อมูลเดิมจาก DB schema เดิมใช้งานได้ทันที (import แล้วใช้ต่อได้)

---

## 13. วิธีเริ่ม (Quick Start)

```bash
mkdir /Applications/XAMPP/xamppfiles/htdocs/babybib_new && cd $_
git init
npm init -y
npm install -D tailwindcss@3 autoprefixer postcss @tailwindcss/forms @tailwindcss/typography
npm install preline alpinejs chart.js
npx tailwindcss init -p
php ../babybib/composer.phar require phpmailer/phpmailer phpoffice/phpword
# copy data layer
cp -r ../babybib/lang ../babybib/database ./
cp -r ../babybib/assets/templates ./assets/templates
cp -r ../babybib/assets/images ./assets/images
cp ../babybib/.env.example ./.env.example && cp .env.example .env
# แก้ SITE_URL=http://localhost/babybib_new ใน .env
npm run dev   # watch CSS
```

ทดสอบ: `http://localhost/babybib_new/th/generate` และ `/en/generate`

---

*สั่งงานจาก: MIGRATION_PLAN_V3.md (4,142 บรรทัด) | รายละเอียดเชิงลึกทุกหัวข้ออ้างอิงเอกสารนั้น*
