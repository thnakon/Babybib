# Babybib: อธิบายโค้ดเชิงลึกรายโฟลเดอร์

เอกสารนี้ต่อยอดจาก `docs/project_structure.md` โดยเน้นอธิบายการทำงานเชิงลึกของโฟลเดอร์หลักที่มีผลต่อ flow ของระบบมากที่สุด ได้แก่ `includes/`, `api/`, และ `users/`

เป้าหมายของเอกสารนี้คือช่วยให้คนที่เพิ่งเข้ามาดูโค้ดเข้าใจว่า request หนึ่งครั้งวิ่งผ่านไฟล์ไหนบ้าง, logic หลักถูกวางไว้ตรงไหน, และแต่ละชั้นของระบบรับผิดชอบเรื่องอะไร

## ภาพรวมเชิงสถาปัตยกรรม

Babybib ใช้โครงสร้างแบบ PHP แบบ page-based application ผสมกับ AJAX API

ลำดับการทำงานโดยทั่วไปมีรูปแบบนี้

1. ผู้ใช้เปิดหน้าเว็บ เช่น `generate.php` หรือ `users/projects.php`
2. หน้าเว็บ include `includes/header.php`
3. `header.php` จะดึง `session.php`, `functions.php`, และ security header ที่จำเป็น
4. ถ้าเป็นการกระทำแบบ async จาก JavaScript หน้าเว็บจะยิง request ไปที่ `api/...`
5. API จะตรวจ method, session, CSRF, validation และค่อยทำงานกับฐานข้อมูลผ่าน `getDB()`
6. API ส่งผลลัพธ์กลับเป็น JSON แล้วหน้าเว็บนำไป render ต่อ

ดังนั้นถ้ามองแบบง่ายที่สุด

- `includes/` = bootstrap + shared logic + security
- `users/` = UI ของผู้ใช้ที่ล็อกอินแล้ว
- `api/` = endpoint สำหรับบันทึก/ดึง/แก้ไขข้อมูลจริง

## 1. โฟลเดอร์ `includes/`: แกนกลางของระบบ

โฟลเดอร์นี้เป็นจุดที่แทบทุกหน้าต้องพึ่งพา เพราะรวมทั้ง config, session, database connection, helper function, layout กลาง และ security

### 1.1 `includes/config.php`

ไฟล์นี้เป็นจุด bootstrap ระดับล่างของระบบ มีหน้าที่หลักดังนี้

1. โหลดค่าจาก `.env` ผ่าน `env.php`
2. เปิด/ปิด error reporting ตาม environment
3. ตั้งค่า session cookie ให้ปลอดภัยขึ้น เช่น `httponly`, `samesite`, `secure`
4. ประกาศค่าคงที่สำคัญ เช่น database config, `SITE_URL`, `SITE_NAME`, quota ของผู้ใช้, timeout
5. สร้างฟังก์ชันกลางที่หลายไฟล์ใช้ร่วมกัน เช่น
   - `getDB()` สำหรับเชื่อมต่อฐานข้อมูลด้วย PDO
   - `sanitize()` สำหรับกรองข้อมูล input เบื้องต้น
   - `generateCSRFToken()` และ `verifyCSRFToken()`
   - `jsonResponse()` สำหรับตอบ JSON แล้วจบ execution ทันที
   - `logActivity()` สำหรับบันทึกเหตุการณ์ลง activity log

ข้อสังเกตเชิงออกแบบ

- ระบบใช้ PDO แบบ singleton ผ่าน `static $pdo` ทำให้ request เดียวไม่เปิด connection ซ้ำหลายรอบ
- มีแนวคิด “self-healing schema” อยู่บางส่วน เช่นบางฟังก์ชันพยายามตรวจและเพิ่มคอลัมน์/ตารางที่จำเป็นให้ฐานข้อมูลเก่า
- `logActivity()` มี cleanup log ของสมาชิกอัตโนมัติ แต่เก็บ log ของ admin/system ไว้ยาวกว่า

### 1.2 `includes/session.php`

ไฟล์นี้เป็นชั้นควบคุมการยืนยันตัวตนและสถานะผู้ใช้

หน้าที่หลัก

1. เริ่ม session หลังจาก `config.php` ตั้งค่า cookie แล้ว
2. บังคับตรวจ CSRF อัตโนมัติสำหรับ request ไปยัง `/api/` ที่เป็น `POST`, `PUT`, `PATCH`, `DELETE`
3. ตรวจ inactivity timeout ของ session
4. ให้ helper สำคัญ เช่น
   - `isLoggedIn()`
   - `isAdmin()`
   - `getCurrentUserId()`
   - `getCurrentUser()`
   - `setUserSession()`
   - `destroySession()`
   - `requireAuth()`
   - `requireAdmin()`
   - `getCurrentLanguage()`
5. bootstrap ระบบหลายภาษาโดยโหลดไฟล์ใน `lang/` และประกาศฟังก์ชัน `__()`

ข้อสังเกตสำคัญ

- ถ้า request เป็น AJAX และ session หมดอายุ ระบบจะตอบ JSON แทนการ redirect ตรง ๆ
- การตรวจ CSRF ถูกวางไว้ระดับ session layer ทำให้ endpoint ใหม่ที่ include ไฟล์นี้มักได้การป้องกันไปด้วย
- `getCurrentUser()` ไม่ใช่แค่ดึง user แต่ยังพยายาม ensure ว่าคอลัมน์ `profile_picture` และ `student_id` มีอยู่ในฐานข้อมูลด้วย

### 1.3 `includes/functions.php`

ไฟล์นี้เก็บ helper เชิงธุรกิจของแอป ไม่ใช่ bootstrap ระดับระบบ

ตัวอย่างฟังก์ชันสำคัญ

- `getResourceTypes()` และ `getResourceTypeById()` สำหรับดึงประเภททรัพยากร
- `getResourceCategories()` สำหรับจัดหมวดหมู่ เช่น หนังสือ, วารสาร, วิทยานิพนธ์, ออนไลน์
- `getProvinces()` สำหรับอ่านค่าจังหวัดจาก system settings
- `countUserBibliographies()` และ `countUserProjects()` สำหรับ quota และ dashboard
- `canCreateBibliography()` และ `canCreateProject()` สำหรับ enforce limit
- `cleanupOldBibliographies()` ลบบรรณานุกรมที่เก่ามากอัตโนมัติ
- `formatDate()` และ `formatThaiDate()` สำหรับแสดงวันที่
- `getBibliographySortKey()` สำหรับหา key ที่ใช้เรียงบรรณานุกรม
- `sortBibliographies()` สำหรับเรียงตามกติกา APA

จุดเด่นของไฟล์นี้คือมี logic ที่ “เข้าใจกติกาบรรณานุกรม” โดยตรง เช่น

1. เรียงภาษาไทยก่อนภาษาอังกฤษ
2. เรียงตามผู้แต่งหรือ title fallback
3. ใช้ปีและ `year_suffix` แยกรายการที่ผู้แต่งและปีซ้ำกัน

### 1.4 `includes/header.php`

ไฟล์นี้เป็น HTML entry point ของแทบทุกหน้าเว็บ

หน้าที่หลัก

1. include security headers ก่อน output
2. include `session.php` และ `functions.php`
3. ตั้งค่า title, language, metadata
4. inject CSS/JS กลางของระบบ
5. inject CSRF token ลงใน `<meta name="csrf-token">`
6. โหลด Tailwind CDN, Alpine.js, Font Awesome, Lucide, และไฟล์ CSS/JS หลักของระบบ

นัยสำคัญของไฟล์นี้คือ ถ้าหน้าไหน include `header.php` จะได้ครบทั้ง

- session
- database helpers
- language system
- security headers
- CSRF token สำหรับ frontend
- asset กลางของ UI

### 1.5 ไฟล์ include อื่น ๆ

| ไฟล์ | บทบาทเชิงลึก |
| --- | --- |
| `navbar-guest.php` | render เมนูของผู้เยี่ยมชม เช่น login/register |
| `navbar-user.php` | render เมนูผู้ใช้ที่ล็อกอินแล้ว และมักเชื่อมกับ dashboard/projects/profile |
| `sidebar-admin.php` | โครงนำทางฝั่ง admin |
| `announcement-toast.php` | แสดงประกาศที่ดึงมาจากระบบส่วนกลาง |
| `email-config.php` | รวมค่าตั้งค่า email ที่แอดมินจัดการได้ |
| `email-helper.php` | helper ส่งอีเมลเชิงธุรกิจ เช่น verify/reset |
| `security-headers.php` | ป้องกัน clickjacking, MIME sniffing และนโยบาย browser อื่น ๆ |
| `security-utils.php` | helper ด้าน security ที่ใช้ซ้ำหลายจุด |
| `visit-tracker.php` | บันทึกสถิติการเข้าชมเพื่อนำไปวิเคราะห์ภายหลัง |

## 2. โฟลเดอร์ `api/`: ชั้นธุรกิจและข้อมูล

ถ้า `includes/` คือแกนกลางของระบบ, `api/` ก็คือชั้นที่รับคำสั่งจริงจาก frontend เพื่ออ่าน/เขียนข้อมูล

รูปแบบร่วมกันของ API ส่วนใหญ่คือ

1. ตั้ง `Content-Type: application/json`
2. include `session.php` หรือ `config.php`
3. ตรวจ HTTP method
4. ตรวจสิทธิ์ด้วย `requireAuth()` หรือ `requireAdmin()`
5. parse input จาก JSON body หรือ query string
6. validate ข้อมูล
7. query ฐานข้อมูล
8. logActivity ถ้าจำเป็น
9. ส่งผลตอบกลับด้วย `jsonResponse()` หรือ `echo json_encode(...)`

### 2.1 `api/auth/`: การยืนยันตัวตนและจัดการบัญชี

ไฟล์เด่นคือ `api/auth/login.php`

flow ของไฟล์นี้

1. รับ JSON body ที่มี `login`, `password`, `remember`
2. ตรวจว่าข้อมูลครบหรือไม่
3. ใช้ IP-based rate limiting ผ่านตาราง `login_attempts`
4. ค้นหาผู้ใช้จาก username หรือ email
5. ตรวจ password ด้วย `password_verify()`
6. ถ้าเปิดระบบยืนยันอีเมล จะบล็อก user ที่ยังไม่ verify
7. ถ้าสำเร็จจะเรียก `setUserSession()`
8. บันทึก activity log
9. ส่ง URL ที่ควร redirect ตาม role เช่น admin หรือ user

ความหมายเชิงออกแบบ

- login logic ไม่ได้แค่ตรวจ credential แต่รวม brute-force protection ไว้ใน endpoint เดียว
- API ตอบ redirect path กลับไปให้ frontend ตัดสินใจ redirect เอง

ไฟล์อื่นในหมวดนี้ เช่น `register.php`, `forgot-password.php`, `reset-password.php`, `verify-code.php`, `resend-code.php`, `delete-account.php` จะเป็นส่วนต่อขยายของ lifecycle บัญชีผู้ใช้

### 2.2 `api/bibliography/`: หัวใจของระบบสร้างบรรณานุกรม

ไฟล์ที่สำคัญมากคือ `api/bibliography/create.php`

ไฟล์นี้ทำทั้ง “สร้างใหม่” และ “แก้ไข” รายการบรรณานุกรมใน endpoint เดียว

flow หลัก

1. รับ JSON จากหน้า generate
2. บังคับให้ผู้ใช้ล็อกอินก่อน
3. ตรวจ quota ด้วย `canCreateBibliography()`
4. อ่านข้อมูลหลัก เช่น `resource_type_id`, `project_id`, `language`, `bibliography_text`, citations, ปี และข้อมูลรายละเอียดอื่น ๆ
5. sanitize และจัดรูปข้อมูลเป็น JSON เพื่อเก็บลงคอลัมน์ `data`
6. คำนวณ `author_sort_key` จากผู้แต่งคนแรกหรือ title
7. ตรวจว่าเป็นการ edit หรือ create
8. ตรวจว่าประเภททรัพยากรและ project มีอยู่จริงและเป็นของผู้ใช้คนนี้
9. คำนวณ `year_suffix` เมื่อมีกรณี author + year ซ้ำ
10. update หรือ insert ลงตาราง `bibliographies`
11. update counter ในตาราง `users` และ `projects`
12. บันทึก activity log

จุดสำคัญทางธุรกิจของ endpoint นี้

- ใช้ `strip_tags(..., '<i>')` เพื่อรักษา italic ของ bibliography text
- แยก `bibliography_text`, `citation_parenthetical`, `citation_narrative` ชัดเจน
- รองรับการ disambiguation ตามหลัก APA ด้วย suffix ไทยหรืออังกฤษ
- ดูแล count ของ project และ user ไปพร้อมกัน ไม่ได้เก็บเฉพาะรายการบรรณานุกรมอย่างเดียว

สรุปสั้น ๆ: ไฟล์นี้คือจุดที่ “ข้อความบรรณานุกรมที่ preview แล้ว” ถูกแปลงเป็นข้อมูลถาวรในระบบ

### 2.3 `api/projects/`: หน่วยรวมบรรณานุกรมเป็นชุดงาน

ตัวอย่างที่อ่านคือ `api/projects/get-content.php`

หน้าที่ของไฟล์นี้

1. รับ `projectId`
2. ตรวจว่า project นี้เป็นของ user ที่ล็อกอินอยู่จริง
3. ดึงข้อมูล project
4. ดึง bibliographies ทั้งหมดใน project
5. เรียงลำดับแบบไทยก่อน, อังกฤษทีหลัง, แล้วเรียงตาม sort key, ปี, suffix
6. apply disambiguation อีกรอบก่อนส่งกลับ

เหตุผลที่ endpoint นี้สำคัญ

- หน้า preview โปรเจกต์และ report builder ใช้มันเป็นแหล่งข้อมูลรวมของเอกสาร
- แม้ใน database จะมี `year_suffix` อยู่แล้ว แต่ endpoint ยังจัดเรียงและปรับผลลัพธ์ให้พร้อม render อีกรอบ

### 2.4 `api/template/`: จุดเชื่อมจาก bibliography ไปสู่รายงานเต็มรูปแบบ

ไฟล์ที่เด่นที่สุดคือ `api/template/export-report.php`

endpoint นี้ทำงานระดับ document generation มากกว่าระดับรายการข้อมูล

สิ่งที่ไฟล์นี้ทำ

1. รับ `payload` จาก report builder
2. validate `template`, `format`, `coverData`, `projectId`
3. ถ้าเป็น template ฝึกงานแบบ DOCX จะคืนไฟล์แม่แบบสำเร็จรูปทันที
4. sanitize ข้อมูลหน้าปกและ normalize ภาษาไทย
5. กำหนด font, margin, body size ตาม template ที่เลือก
6. ดึง bibliography จาก project ถ้าผู้ใช้เลือก project ไว้
7. เรียง bibliographies และ apply disambiguation
8. map โครงสร้างบทของ template เช่น academic report, research, thesis, thesis_master
9. export เป็น DOCX หรือ PDF ตามคำขอ

ไฟล์นี้บอกชัดว่าระบบ Babybib ไม่ได้หยุดแค่ “สร้างรายการอ้างอิง” แต่ขยายไปถึง “ประกอบเอกสารรายงานทั้งเล่ม”

### 2.5 `api/smart_search.php`: ชั้นค้นหาอัจฉริยะ

ไฟล์นี้ทำหน้าที่เป็น unified search endpoint สำหรับ ISBN, DOI, URL และ keyword

แนวคิดหลักของไฟล์นี้

1. auto-detect ชนิด input
2. แยกการค้นหาแบบไทยและ global source
3. มี file-based rate limiting ต่อ IP
4. มี file-based cache เพื่อลดการยิง API ภายนอกซ้ำ
5. support หลาย data source เช่น Google Books, Open Library, CrossRef, OpenAlex และ scraper
6. ส่ง metadata ที่ normalize แล้วกลับไปให้ frontend เติมฟอร์มอัตโนมัติ

สิ่งที่น่าสนใจเชิงสถาปัตยกรรม

- ปล่อย session lock ด้วย `session_write_close()` เพื่อไม่ให้ search ยาว ๆ ล็อก session ทั้ง request
- ใช้ `tmp/babybib_search_cache` และ `tmp/babybib_rate` เป็น storage ชั่วคราว
- เก็บ `source_errors` กลับไปด้วย ทำให้ frontend สามารถบอกได้ว่าบางบริการภายนอกล้มเหลว

### 2.6 ลักษณะร่วมของ API ทั้งระบบ

จากไฟล์ที่อ่านพบ pattern ที่ใช้ซ้ำบ่อย

1. ใช้ PHP endpoint แยกไฟล์ตาม resource/action ชัดเจน
2. ใช้ helper กลางจาก `includes/` มากกว่าทำซ้ำในแต่ละ endpoint
3. ใช้ DB โดยตรงผ่าน PDO มากกว่ามี service layer หนา ๆ
4. logic ธุรกิจสำคัญถูกฝังใน endpoint โดยตรงพอสมควร
5. หลาย endpoint พยายามทำงานให้ “ครบ transaction ทางความหมาย” เช่น update count, log activity, validate ownership พร้อมกัน

## 3. โฟลเดอร์ `users/`: ชั้น UI สำหรับสมาชิก

โฟลเดอร์นี้คือพื้นที่ของหน้าที่ผู้ใช้ล็อกอินแล้วใช้งานประจำ เช่น dashboard, projects, bibliography list, profile และ report builder

ลักษณะของไฟล์ใน `users/`

1. เป็น PHP page ที่ render HTML/CSS/JS โดยตรง
2. include `header.php` และ `navbar-user.php`
3. query ข้อมูลเริ่มต้นจากฐานข้อมูลเพื่อ render หน้าแรก
4. บางหน้าใช้ JavaScript ยิง API เพิ่มเติมภายหลัง
5. เน้นประสบการณ์ใช้งานมากกว่า logic เชิงระบบล้วน ๆ

### 3.1 `users/dashboard.php`

หน้า dashboard ผู้ใช้มีหน้าที่หลัก 3 ส่วน

1. ดึงข้อมูลผู้ใช้ปัจจุบัน
2. ดึงสถิติ เช่น จำนวนบรรณานุกรม, จำนวนโปรเจกต์, quota ที่เหลือ
3. ดึงบรรณานุกรมล่าสุดเพื่อแสดง recent activity

จุดสำคัญของหน้านี้คือทำตัวเป็น “control panel” ของผู้ใช้ ไม่ได้เป็นแค่หน้า welcome เฉย ๆ แต่มี quick action ไปยังการสร้าง bibliography, สร้าง project, export project, และแก้ไข profile

### 3.2 `users/bibliography-list.php`

หน้านี้เป็นหน้าจัดการบรรณานุกรมที่บันทึกไว้ทั้งหมดของผู้ใช้

สิ่งที่ทำ

1. เรียก `cleanupOldBibliographies()` เพื่อลบรายการที่เก่าเกิน 2 ปี
2. รองรับ pagination
3. รองรับ search, filter ตาม resource type และ project
4. รองรับ sort order
5. join ตาราง resource type และ project เพื่อ render บริบทครบในหน้าเดียว

นัยสำคัญ

- หน้า UI นี้เชื่อมกับ business rule เรื่อง retention policy โดยตรง
- ไม่ใช่แค่แสดงรายการ แต่เป็นจุดจัดการ lifecycle ของ bibliography ที่ผู้ใช้สร้างสะสมไว้

### 3.3 `users/projects.php`

ไฟล์นี้เป็นหน้าจัดการโปรเจกต์แบบ workspace view

หน้าที่หลัก

1. ดึงรายการ project ของ user พร้อมจำนวน bibliography ต่อ project
2. รองรับ search, sort, pagination
3. render layout แบบ 3 คอลัมน์
   - ซ้าย: รายชื่อ project
   - กลาง: กระดาษ preview แบบ A4
   - ขวา: เครื่องมือ/บริบทเพิ่มเติม

ความหมายเชิง UX และสถาปัตยกรรม

- หน้า project ไม่ได้เป็น list ธรรมดา แต่ทำตัวเป็น workspace สำหรับจัดชุดบรรณานุกรม
- preview กลางหน้าสื่อชัดว่าระบบพยายามให้ผู้ใช้มอง bibliographies ในรูปเอกสารจริง ไม่ใช่ข้อมูลดิบในตารางอย่างเดียว

### 3.4 `users/report-template.php`

หน้านี้เป็น entry point ของ feature สร้างรายงาน

บทบาทของไฟล์นี้

1. ให้ผู้ใช้เลือก template รายงาน
2. แยกโหมด guest กับ logged-in user
3. แสดง card ของ template หลายรูปแบบ เช่น academic, research, internship, thesis_master
4. อธิบายโครงสร้างและจุดเด่นของแต่ละ template

กล่าวอีกแบบ หน้านี้คือ “catalog ของ report generator” ก่อนผู้ใช้จะเข้าสู่ builder จริง

### 3.5 `users/report-builder.php`

ไฟล์นี้เป็นหนึ่งในหน้าที่ซับซ้อนที่สุดของฝั่งผู้ใช้

หน้าที่หลัก

1. รับ `templateId` จาก query string
2. รองรับ guest mode และ member mode
3. โหลดรายการ project ของผู้ใช้ถ้าล็อกอินอยู่
4. เตรียมข้อความ UI ทั้งไทย/อังกฤษจำนวนมากในตัวแปร `$builderText`
5. render หน้าสำหรับกรอกข้อมูลรายงาน เช่น
   - cover data
   - project bibliography
   - abstract
   - acknowledgment
   - appendix
   - approval page
   - author biography
6. ใช้ข้อมูลเหล่านี้ส่งต่อไปยัง `api/template/export-report.php`

ความสำคัญของไฟล์นี้

- เป็นตัวกลางระหว่างโลกของ “ข้อมูลบรรณานุกรม” กับโลกของ “เอกสารรายงานทั้งเล่ม”
- เป็นหน้าที่ทำให้ feature export เอกสารของระบบมีมูลค่าเพิ่มเกินกว่าระบบ citation generator ทั่วไป

## 4. ตัวอย่าง flow จริงในระบบ

### กรณีที่ 1: ผู้ใช้ล็อกอิน

1. เปิด `login.php`
2. frontend ยิง request ไป `api/auth/login.php`
3. endpoint ตรวจ rate limit, password, verification
4. `setUserSession()` ถูกเรียกใน `session.php`
5. frontend redirect ไป `users/dashboard.php` หรือ `admin/index.php`

### กรณีที่ 2: ผู้ใช้สร้างบรรณานุกรมใหม่

1. เปิด `generate.php`
2. หน้าโหลด `header.php` และ helper กลางทั้งหมด
3. ผู้ใช้กรอกข้อมูลหรือใช้ Smart Search
4. ถ้าใช้ค้นหา ระบบเรียก `api/smart_search.php`
5. frontend เอาผลค้นหามาเติมฟอร์ม
6. เมื่อกดบันทึก จะยิงไป `api/bibliography/create.php`
7. endpoint validate, คำนวณ sort key/suffix, บันทึก DB, update counters, log activity
8. หน้า bibliography list หรือ dashboard สามารถดึงข้อมูลใหม่ไปแสดงต่อได้

### กรณีที่ 3: ผู้ใช้สร้างรายงานจาก project

1. เปิด `users/report-template.php`
2. เลือก template แล้วเข้าสู่ `users/report-builder.php`
3. builder โหลด project ของผู้ใช้
4. เมื่อเลือก project จะเรียก `api/template/get-project-bibs.php` หรือ `api/projects/get-content.php`
5. เมื่อผู้ใช้กด export จะส่ง payload ไป `api/template/export-report.php`
6. endpoint ประกอบข้อมูลหน้าปก + bibliography + chapter structure
7. ส่งกลับเป็นไฟล์ DOCX หรือ PDF

## 5. สิ่งที่ควรรู้ก่อนแก้โค้ดชุดนี้

ถ้าจะเริ่มแก้ระบบนี้ ควรจำ 6 ข้อนี้ก่อน

1. หน้า PHP ส่วนใหญ่ไม่ได้บางมาก แต่มีทั้ง query, layout, style และบางครั้ง JS ในไฟล์เดียว
2. `includes/header.php` สำคัญมาก เพราะเป็น bootstrap ระดับหน้าเว็บ
3. `includes/session.php` เป็นจุดที่ควบคุม auth, timeout, language และ CSRF พร้อมกัน
4. API จำนวนมากไม่ได้มี service layer แยก ดังนั้น business logic จะอยู่ใน endpoint โดยตรง
5. ระบบ bibliography มี logic เฉพาะด้าน APA เช่น sort key, Thai-first sorting, year suffix disambiguation
6. feature report builder เป็นชั้นบนของระบบ และพึ่งทั้ง projects, bibliographies, template definitions และ export logic พร้อมกัน

## 6. คำแนะนำในการไล่ดูโค้ดต่อ

ถ้าจะอ่านโค้ดต่อแบบมีลำดับ แนะนำให้อ่านตามนี้

1. `includes/config.php`
2. `includes/session.php`
3. `includes/functions.php`
4. `generate.php`
5. `api/smart_search.php`
6. `api/bibliography/create.php`
7. `users/projects.php`
8. `users/report-template.php`
9. `users/report-builder.php`
10. `api/template/export-report.php`

ลำดับนี้จะทำให้เห็นภาพตั้งแต่ฐานระบบ ไปจนถึง flow สำคัญที่สุดของ Babybib คือ “ค้นข้อมูล → สร้างบรรณานุกรม → รวมเป็นโปรเจกต์ → export รายงาน”

## หมายเหตุ

- เอกสารนี้อธิบายเชิงลึกจากไฟล์ที่เปิดอ่านจริงเป็นหลัก และใช้ชื่อไฟล์/โครงสร้าง repo ช่วยเติมภาพรวมในส่วนที่ไม่ได้เปิดทุกไฟล์
- หากต้องการทำต่อ สามารถแตกเอกสารฉบับนี้ออกเป็น 3 ฉบับย่อยได้ทันที ได้แก่ deep dive ของ `includes`, deep dive ของ `api`, และ deep dive ของ `users`