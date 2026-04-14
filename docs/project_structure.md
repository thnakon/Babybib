# Babybib: อธิบายโครงสร้างโฟลเดอร์และไฟล์

เอกสารนี้สรุปหน้าที่ของโฟลเดอร์และไฟล์หลักในโปรเจกต์ Babybib เพื่อช่วยให้ไล่ดูโค้ดได้เร็วขึ้น โดยอธิบายจากโครงสร้างจริงใน repo และตรวจไฟล์แกนหลักบางส่วนเพิ่มเติม

## ภาพรวมระบบ

Babybib เป็นระบบสร้างบรรณานุกรมตามรูปแบบ APA 7 ที่มีทั้งฝั่งหน้าเว็บ, ระบบสมาชิก, หน้าแอดมิน, ชุด API สำหรับทำงานแบบ AJAX, ระบบ export เอกสาร, ระบบค้นหาข้อมูลอัตโนมัติ, และสคริปต์ดูแลระบบ เช่น backup และ SQL migration

## Root Level

| ไฟล์/โฟลเดอร์ | หน้าที่ |
| --- | --- |
| `.env` | เก็บค่าตั้งค่าจริงของระบบ เช่น database, URL, email, app key |
| `.env.example` | ตัวอย่างค่าตั้งค่าสำหรับใช้เป็นแม่แบบตอนติดตั้งระบบ |
| `.git/` | ข้อมูลภายในของ Git repository |
| `.gitignore` | ระบุไฟล์/โฟลเดอร์ที่ Git ไม่ต้องติดตาม |
| `.htaccess` | ตั้งค่าพฤติกรรม Apache เช่น rewrite, security, access rules |
| `README.md` | เอกสารแนะนำโปรเจกต์และความสามารถหลักของระบบ |
| `index.php` | หน้าแรกของเว็บไซต์ แสดงภาพรวมระบบ ปุ่มเริ่มสร้างบรรณานุกรม และข้อมูลแนะนำการใช้งาน |
| `generate.php` | หน้าหลักของระบบสำหรับสร้างและแก้ไขบรรณานุกรม APA 7 |
| `start.php` | หน้าคู่มือเริ่มต้นการใช้งานสำหรับผู้ใช้ |
| `summary.php` | หน้าสรุปหรือรวมผลลัพธ์ที่สร้างจากข้อมูลบรรณานุกรม |
| `sort.php` | หน้าอธิบายหรือช่วยเรื่องการเรียงลำดับรายการบรรณานุกรม |
| `babybib2.php` | ไฟล์หน้าเว็บอีกตัวของระบบ ชื่อสื่อว่าอาจเป็นหน้า legacy, รุ่นทดลอง, หรือหน้าทดสอบบางส่วนของ Babybib |
| `features.html` | หน้า static แสดงคุณสมบัติหรือฟีเจอร์ของระบบ |
| `login.php` | หน้าเข้าสู่ระบบ |
| `register.php` | หน้าสมัครสมาชิก |
| `forgot-password.php` | หน้าเริ่มกระบวนการลืมรหัสผ่าน |
| `reset-password.php` | หน้าตั้งรหัสผ่านใหม่หลังยืนยัน token แล้ว |
| `verify.php` | หน้ายืนยันอีเมลหรือยืนยันรหัสของผู้ใช้ |
| `help-author.php` | หน้า help อธิบายวิธีกรอกข้อมูลผู้แต่ง/ผู้เขียน |
| `help-place.php` | หน้า help อธิบายวิธีกรอกข้อมูลสถานที่พิมพ์หรือสถานที่เผยแพร่ |
| `help-publisher.php` | หน้า help อธิบายวิธีกรอกข้อมูลสำนักพิมพ์ |
| `privacy.php` | หน้านโยบายความเป็นส่วนตัว |
| `terms.php` | หน้าเงื่อนไขการใช้งาน |
| `admin/` | หน้าจัดการระบบสำหรับผู้ดูแล |
| `api/` | ชุด endpoint ฝั่ง backend สำหรับงาน asynchronous และ CRUD |
| `assets/` | ไฟล์ static ทั้ง CSS, JavaScript, font และรูปภาพ |
| `backups/` | พื้นที่เก็บไฟล์ backup ที่ระบบสร้างไว้ |
| `database/` | SQL ชุดตั้งต้นสำหรับสร้างฐานข้อมูลหลัก |
| `docs/` | เอกสารประกอบระบบ การ deploy และแม่แบบรายงาน |
| `errors/` | หน้า error เช่น 403, 404, 500 |
| `includes/` | ไฟล์ include กลาง เช่น config, session, header, helper |
| `lang/` | ไฟล์ข้อความหลายภาษา เช่น ไทยและอังกฤษ |
| `logs/` | โฟลเดอร์ log สำหรับบันทึกเหตุการณ์และข้อผิดพลาด |
| `node_modules/` | dependency ฝั่ง frontend/build tool ถ้ามีการใช้ npm |
| `scripts/` | shell script สำหรับ backup และ restore |
| `sql/` | ไฟล์ SQL แบบ migration/patch สำหรับเพิ่มตารางหรือคอลัมน์ |
| `tmp/` | โฟลเดอร์ไฟล์ชั่วคราว เช่น cache ค้นหาและ cache rating |
| `uploads/` | ไฟล์ที่ผู้ใช้อัปโหลด เช่น รูป avatar |
| `users/` | หน้าเว็บสำหรับผู้ใช้ที่ล็อกอินแล้ว |
| `vendor/` | ไลบรารีภายนอกที่ติดตั้งมา เช่น PHPMailer |

## โฟลเดอร์ `admin/`

หน้าฝั่งผู้ดูแลระบบ ใช้ดูสถิติและจัดการข้อมูลส่วนกลางของระบบ

| ไฟล์ | หน้าที่ |
| --- | --- |
| `admin/index.php` | หน้า dashboard ของแอดมิน แสดงสถิติผู้ใช้ บรรณานุกรม โปรเจกต์ และ feedback |
| `admin/announcements.php` | จัดการประกาศที่จะแสดงให้ผู้ใช้เห็น |
| `admin/backups.php` | จัดการรายการ backup และการดาวน์โหลด/ลบ backup |
| `admin/bibliographies.php` | ดูและจัดการบรรณานุกรมทั้งหมดในระบบ |
| `admin/feedback.php` | ดู feedback หรือข้อเสนอแนะจากผู้ใช้ |
| `admin/logs.php` | ดู log การใช้งานหรือเหตุการณ์สำคัญของระบบ |
| `admin/notifications.php` | ดูการแจ้งเตือนฝั่งแอดมิน |
| `admin/profile.php` | แก้ไขข้อมูลโปรไฟล์ผู้ดูแล |
| `admin/projects.php` | ดูและจัดการโปรเจกต์ของผู้ใช้ |
| `admin/settings.php` | ตั้งค่าระบบ เช่น email, ข้อความ, รายการค่ากลาง |
| `admin/users.php` | จัดการผู้ใช้ เช่น เพิ่ม แก้ไข ลบ หรือเปลี่ยนสถานะ |

## โฟลเดอร์ `users/`

หน้าสำหรับผู้ใช้ทั่วไปหลังล็อกอินแล้ว

| ไฟล์ | หน้าที่ |
| --- | --- |
| `users/dashboard.php` | หน้า dashboard ผู้ใช้ แสดงสถิติ, รายการบรรณานุกรมล่าสุด, quick actions |
| `users/activity-history.php` | หน้าแสดงประวัติการใช้งานหรือกิจกรรมของผู้ใช้ |
| `users/bibliography-list.php` | หน้าแสดงรายการบรรณานุกรมทั้งหมดของผู้ใช้ |
| `users/profile.php` | หน้าแก้ไขข้อมูลโปรไฟล์ผู้ใช้ |
| `users/project-preview.php` | หน้า preview โปรเจกต์หรือข้อมูลที่กำลังจะ export |
| `users/projects.php` | หน้าจัดการโปรเจกต์ของผู้ใช้ |
| `users/report-builder.php` | หน้าสร้างรายงานจากข้อมูลในระบบ |
| `users/report-template.php` | หน้าเลือกหรือกำหนด template สำหรับรายงาน |

## โฟลเดอร์ `includes/`

เป็นแกนกลางของระบบ ใช้รวมไฟล์ที่หลายหน้าต้องใช้ร่วมกัน

| ไฟล์ | หน้าที่ |
| --- | --- |
| `includes/config.php` | โหลด environment, ตั้งค่าระบบ, ตั้ง session/cookie, เชื่อมฐานข้อมูล, CSRF, helper ระดับต่ำ |
| `includes/env.php` | โหลดค่า environment จากไฟล์ `.env` |
| `includes/functions.php` | รวม helper function ระดับธุรกิจ เช่น resource types, project/bibliography counting, formatting, sorting |
| `includes/session.php` | จัดการ session, การล็อกอิน, current user, การป้องกันหน้า protected |
| `includes/header.php` | ส่วนหัว HTML กลางของทุกหน้า และ asset ที่ต้องโหลดร่วมกัน |
| `includes/footer.php` | footer สำหรับหน้าผู้ใช้หรือหน้าสาธารณะ |
| `includes/footer-admin.php` | footer สำหรับหน้าแอดมิน |
| `includes/navbar-guest.php` | เมนูนำทางสำหรับผู้เยี่ยมชมที่ยังไม่ล็อกอิน |
| `includes/navbar-user.php` | เมนูนำทางสำหรับผู้ใช้ที่ล็อกอินแล้ว |
| `includes/sidebar-admin.php` | เมนูด้านข้างของหน้าแอดมิน |
| `includes/announcement-toast.php` | แสดงประกาศหรือข้อความแจ้งเตือนแบบ toast |
| `includes/email-config.php` | รวม logic อ่าน/จัดการค่าตั้งค่า email |
| `includes/email-helper.php` | helper สำหรับส่งอีเมล เช่น verification หรือ reset password |
| `includes/security-headers.php` | เพิ่ม HTTP security headers |
| `includes/security-utils.php` | รวม utility ด้านความปลอดภัยเพิ่มเติม |
| `includes/start.php` | include ช่วยหน้าเริ่มต้นหรือข้อมูลคู่มือที่ใช้ร่วมกัน |
| `includes/visit-tracker.php` | เก็บสถิติการเข้าชมหรือ visit tracking |

## โฟลเดอร์ `api/`

API ของระบบ ใช้รับ request จากหน้าเว็บหรือ JavaScript โดยแบ่งเป็นหมวดตามงาน

### ไฟล์ระดับบนของ `api/`

| ไฟล์/โฟลเดอร์ | หน้าที่ |
| --- | --- |
| `api/smart_search.php` | endpoint ค้นหาข้อมูลบรรณานุกรมแบบอัจฉริยะจากหลายแหล่งข้อมูล |
| `api/search_fallback.json` | ชุดข้อมูลสำรองเมื่อการค้นหาจากภายนอกล้มเหลวหรือใช้เป็น fallback |
| `api/admin/` | endpoint สำหรับงานจัดการระบบของแอดมิน |
| `api/auth/` | endpoint สำหรับสมัครสมาชิก, ล็อกอิน, รีเซ็ตรหัสผ่าน, ยืนยันตัวตน |
| `api/bibliography/` | endpoint สำหรับสร้าง/แก้ไข/ลบ/ย้าย/export บรรณานุกรม |
| `api/cache/` | งานเกี่ยวกับ cache ของข้อมูลที่เรียกใช้บ่อย |
| `api/export/` | งาน export ข้อมูลหรือไฟล์ออกจากระบบ |
| `api/feedback/` | รับ feedback หรือแบบประเมินจากผู้ใช้ |
| `api/isbn/` | ค้นหรือแปลงข้อมูลจาก ISBN |
| `api/projects/` | จัดการโปรเจกต์ของผู้ใช้ |
| `api/rating/` | รับคะแนนหรือผลประเมิน |
| `api/scraper/` | ดึง metadata จากเว็บภายนอกผ่าน scraping |
| `api/support/` | รับคำร้อง support หรือรายงานปัญหา |
| `api/template/` | ทำงานเกี่ยวกับแม่แบบรายงานและการ export เอกสาร |
| `api/user/` | แก้ไขข้อมูลบัญชีผู้ใช้ |
| `api/utils/` | utility endpoint หรือ helper สำหรับ API |

### `api/admin/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `api/admin/create-announcement.php` | เพิ่มประกาศใหม่ |
| `api/admin/update-announcement.php` | แก้ไขประกาศ |
| `api/admin/delete-announcement.php` | ลบประกาศ |
| `api/admin/create-backup.php` | สร้าง backup |
| `api/admin/download-backup.php` | ดาวน์โหลดไฟล์ backup |
| `api/admin/delete-backup.php` | ลบ backup |
| `api/admin/create-user.php` | สร้างผู้ใช้ใหม่โดยแอดมิน |
| `api/admin/delete-user.php` | ลบผู้ใช้ |
| `api/admin/update-user.php` | แก้ไขข้อมูลผู้ใช้ |
| `api/admin/update-user-details.php` | อัปเดตรายละเอียดเชิงลึกของผู้ใช้ |
| `api/admin/update-bibliography.php` | แก้ไขข้อมูลบรรณานุกรมในมุมมองแอดมิน |
| `api/admin/update-project.php` | แก้ไขโปรเจกต์ในมุมมองแอดมิน |
| `api/admin/delete-feedback.php` | ลบ feedback |
| `api/admin/update-feedback.php` | อัปเดตสถานะหรือข้อมูล feedback |
| `api/admin/notifications.php` | ดึงรายการแจ้งเตือนแอดมิน |
| `api/admin/mark-notifications-read.php` | ทำเครื่องหมายว่าอ่าน notification แล้ว |
| `api/admin/update-settings.php` | บันทึกค่าตั้งค่าระบบ |
| `api/admin/test-email-settings.php` | ทดสอบการตั้งค่า email |

### `api/auth/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `api/auth/login.php` | รับคำขอเข้าสู่ระบบ |
| `api/auth/logout.php` | ออกจากระบบ |
| `api/auth/register.php` | สมัครสมาชิก |
| `api/auth/forgot-password.php` | ขอ reset password |
| `api/auth/reset-password.php` | ยืนยันและตั้งรหัสใหม่ |
| `api/auth/change-password.php` | เปลี่ยนรหัสผ่านขณะล็อกอินอยู่ |
| `api/auth/verify-code.php` | ตรวจสอบรหัสยืนยัน |
| `api/auth/resend-code.php` | ส่งรหัสยืนยันซ้ำ |
| `api/auth/get-captcha.php` | สร้างหรือคืนค่า captcha |
| `api/auth/update-profile.php` | แก้ไขข้อมูลโปรไฟล์ผ่านฝั่ง auth |
| `api/auth/upload-avatar.php` | อัปโหลดรูปโปรไฟล์ |
| `api/auth/remove-avatar.php` | ลบรูปโปรไฟล์ |
| `api/auth/delete-account.php` | ลบบัญชีผู้ใช้ |

### `api/bibliography/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `api/bibliography/create.php` | สร้างบรรณานุกรมใหม่ |
| `api/bibliography/delete.php` | ลบบรรณานุกรม |
| `api/bibliography/export.php` | export รายการบรรณานุกรม |
| `api/bibliography/move.php` | ย้ายบรรณานุกรมไปยังโปรเจกต์อื่น |
| `api/bibliography/preview.php` | สร้าง preview ก่อนบันทึกหรือ export |
| `api/bibliography/update_project.php` | อัปเดตความสัมพันธ์ระหว่างบรรณานุกรมกับโปรเจกต์ |

### `api/projects/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `api/projects/create.php` | สร้างโปรเจกต์ |
| `api/projects/delete.php` | ลบโปรเจกต์ |
| `api/projects/get-content.php` | ดึงเนื้อหาหรือรายการในโปรเจกต์ |
| `api/projects/update.php` | แก้ไขโปรเจกต์ |

### `api/template/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `api/template/export-report.php` | สร้างไฟล์รายงานจาก template เช่น รายงานวิชาการ และ export เป็น DOCX |
| `api/template/export-report-internship.php` | Logic พิเศษสำหรับการสร้างและ export รายงานผลการฝึกประสบการณ์ (ฝึกงาน) |
| `api/template/export-report-research.php` | Logic พิเศษสำหรับการสร้างและ export รายงานวิจัย หรือรายงานโปรเจกต์ |
| `api/template/export-report-logo.php` | Logic สำหรับการสร้างรายงานแบบที่มีการใส่ตราสถาบัน (Logo) บนหน้าปก |
| `api/template/get-project-bibs.php` | ดึงรายการบรรณานุกรมของโปรเจกต์เพื่อใช้ประกอบรายงาน |

### `api/user/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `api/user/update-profile.php` | อัปเดตข้อมูลโปรไฟล์ผู้ใช้ |
| `api/user/upload-avatar.php` | อัปโหลดรูป avatar ของผู้ใช้ |

### โฟลเดอร์ API ที่ไม่ได้เปิดดูไฟล์ย่อยทั้งหมด

| โฟลเดอร์ | หน้าที่โดยรวม |
| --- | --- |
| `api/cache/` | จัดการ cache ชั่วคราวเพื่อลดการเรียกซ้ำ |
| `api/export/` | export รูปแบบอื่นนอกเหนือจากรายงาน template |
| `api/feedback/` | รับและบันทึก feedback จากผู้ใช้ |
| `api/isbn/` | เรียกบริการค้นหาหนังสือจาก ISBN |
| `api/rating/` | รับคะแนนความพึงพอใจหรือ rating |
| `api/scraper/` | scrape ข้อมูล metadata จากเว็บปลายทาง |
| `api/support/` | ระบบแจ้งปัญหา/ส่งคำร้องช่วยเหลือ |
| `api/utils/` | utility เฉพาะทางที่ API อื่นเรียกใช้ |

## โฟลเดอร์ `assets/`

| โฟลเดอร์ | หน้าที่ |
| --- | --- |
| `assets/css/` | stylesheet แยกตาม layout และแต่ละหน้า |
| `assets/fonts/` | font ที่ใช้ในระบบ |
| `assets/images/` | รูปภาพ, logo, illustration |
| `assets/js/tour.js` | ระบบ Interactive Onboarding Tour (สอนใช้งานสำหรับผู้ใช้ใหม่) |
| `assets/js/apa7-formatter.js` | ชุดคำสั่งสำหรับจัดรูปแบบข้อความบรรณานุกรมตามมาตรฐาน APA 7 |
| `assets/js/main.js` | JavaScript กลางสำหรับ UI และ AJAX ทั่วไป |

## โฟลเดอร์ `database/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `database/database.sql` | schema หลักของฐานข้อมูล |
| `database/resource_types.sql` | ข้อมูลตั้งต้นสำหรับชนิดทรัพยากรบรรณานุกรม |

## โฟลเดอร์ `docs/`

ใช้เก็บเอกสารประกอบทั้งสำหรับนักพัฒนาและเอกสารแม่แบบรายงาน

| ไฟล์/โฟลเดอร์ | หน้าที่ |
| --- | --- |
| `docs/deployment_guide.md` | คู่มือ deploy ระบบแบบ Markdown |
| `docs/deployment_guide.txt` | คู่มือ deploy ระบบแบบ text |
| `docs/smart_search_architecture.md` | อธิบายสถาปัตยกรรมของ Smart Search |
| `docs/smart_search_samples.json` | ตัวอย่าง payload/response สำหรับ Smart Search |
| `docs/template-รายงานผลการฝึกประสบการณ์.docx` | แม่แบบเอกสารรายงานฝึกประสบการณ์สำหรับ export |
| `docs/thesis_master_template_guide.md` | คู่มือใช้งาน template วิทยานิพนธ์ระดับปริญญาโท |
| `docs/thesis/` | เอกสารแยกตามหัวข้อของรายงาน/วิทยานิพนธ์ |
| `docs/project_structure.md` | เอกสารนี้ ใช้อธิบายโครงสร้างไฟล์และโฟลเดอร์ของโปรเจกต์ |

### `docs/thesis/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `docs/thesis/section_4_1_system_development.md` | คู่มือเนื้อหาส่วนการพัฒนาระบบ |
| `docs/thesis/section_4_2_system_functionality.md` | คู่มือเนื้อหาส่วนความสามารถของระบบ |
| `docs/thesis/section_4_3_system_testing.md` | คู่มือเนื้อหาส่วนการทดสอบระบบ |

## โฟลเดอร์ `errors/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `errors/403.php` | หน้าแจ้งว่าไม่มีสิทธิ์เข้าถึง |
| `errors/404.php` | หน้าไม่พบไฟล์หรือ route |
| `errors/500.php` | หน้า error ภายในระบบ |

## โฟลเดอร์ `lang/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `lang/en.php` | ข้อความภาษาอังกฤษของระบบ |
| `lang/th.php` | ข้อความภาษาไทยของระบบ |

## โฟลเดอร์ `scripts/`

| ไฟล์ | หน้าที่ |
| --- | --- |
| `scripts/backup_database.sh` | สคริปต์สำรองฐานข้อมูล |
| `scripts/backup_files.sh` | สคริปต์สำรองไฟล์ของระบบ |
| `scripts/restore_backup.sh` | สคริปต์กู้คืน backup |

## โฟลเดอร์ `sql/`

ไฟล์ในโฟลเดอร์นี้เป็น migration หรือ patch SQL สำหรับเพิ่มความสามารถให้ฐานข้อมูลเดิม

| ไฟล์ | หน้าที่ |
| --- | --- |
| `sql/add_indexes.sql` | เพิ่ม index เพื่อปรับปรุงประสิทธิภาพ query |
| `sql/add_lis_cmu_column.sql` | เพิ่มคอลัมน์เกี่ยวกับสถานะ LIS CMU |
| `sql/add_profile_picture.sql` | เพิ่มรองรับรูปโปรไฟล์ |
| `sql/create_test_user_usedcase.sql` | สร้างข้อมูลทดสอบสำหรับ user use case |
| `sql/email_verification_table.sql` | เพิ่มตารางหรือโครงสร้างสำหรับยืนยันอีเมล |
| `sql/login_attempts_table.sql` | เพิ่มตารางเก็บประวัติหรือการป้องกัน login attempts |
| `sql/password_reset_table.sql` | เพิ่มโครงสร้างสำหรับ reset password |
| `sql/rating_table.sql` | เพิ่มตาราง rating/ประเมินผล |
| `sql/support_reports_table.sql` | เพิ่มตารางรายงานปัญหา/คำร้อง support |
| `sql/verify_all_users.sql` | SQL สำหรับปรับสถานะยืนยันผู้ใช้แบบครั้งเดียว |
| `sql/visits_table.sql` | เพิ่มตารางเก็บสถิติการเข้าชม |

## โฟลเดอร์ `tmp/`

| โฟลเดอร์ | หน้าที่ |
| --- | --- |
| `tmp/babybib_rate/` | เก็บไฟล์ชั่วคราวที่เกี่ยวกับระบบ rating |
| `tmp/babybib_search_cache/` | cache ผลการค้นหาเพื่อลดการเรียกบริการภายนอก |

## โฟลเดอร์ `uploads/`

| ไฟล์/โฟลเดอร์ | หน้าที่ |
| --- | --- |
| `uploads/.htaccess` | ป้องกันการเข้าถึงหรือกำหนดข้อจำกัดในโฟลเดอร์ uploads |
| `uploads/avatars/` | เก็บรูปโปรไฟล์ของผู้ใช้ |

## โฟลเดอร์ `vendor/`

| โฟลเดอร์ | หน้าที่ |
| --- | --- |
| `vendor/phpmailer/` | ไลบรารี PHPMailer สำหรับส่งอีเมลจากระบบ |

## โฟลเดอร์อื่นที่ควรรู้

| โฟลเดอร์ | หน้าที่ |
| --- | --- |
| `backups/` | พื้นที่เก็บไฟล์สำรองที่สร้างจากระบบหรือสคริปต์ |
| `logs/` | เก็บ log การทำงาน เช่น error log, activity log หรือ audit data |
| `node_modules/` | dependency จาก npm ถ้าโปรเจกต์ใช้ frontend tooling เพิ่มเติม |

## สรุปการแบ่งชั้นของระบบ

1. หน้าเว็บหลักอยู่ที่ root, `users/`, และ `admin/`
2. ส่วน logic กลางและ bootstrap อยู่ที่ `includes/`
3. งาน backend แบบ request/response แยกไว้ใน `api/`
4. โครงสร้างฐานข้อมูลและ migration อยู่ที่ `database/` กับ `sql/`
5. ไฟล์ static และไฟล์ที่ผู้ใช้อัปโหลดแยกเป็น `assets/` และ `uploads/`
6. งานดูแลระบบและเอกสารเสริมอยู่ที่ `scripts/`, `docs/`, `backups/`, `logs/`

## หมายเหตุ

- ไฟล์บางตัว เช่น `babybib2.php` และบางโฟลเดอร์ใน `api/` ที่ไม่ได้เปิดอ่านทุกไฟล์ ถูกอธิบายจากชื่อไฟล์และบริบทของระบบร่วมกับโครงสร้างปัจจุบัน
- ถ้าต้องการ เอกสารนี้สามารถต่อยอดเป็นแผนภาพสถาปัตยกรรม หรือแยกเป็นคู่มือดูโค้ดสำหรับนักพัฒนาใหม่ได้