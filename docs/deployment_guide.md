# คู่มือการติดตั้งและ Deploy ระบบ Babybib (Deployment Guide)

เอกสารฉบับนี้สรุปขั้นตอนและสิ่งที่ต้องเตรียมสำหรับการนำระบบ Babybib จากเครื่อง Local (XAMPP) ไปติดตั้งบนเครื่อง Server จริง (Production)

---

## 1. การเตรียม Server (System Requirements)
เพื่อให้ระบบทำงานได้สมบูรณ์ Server ควรมีคุณสมบัติดังนี้:
*   **Operating System:** Linux (แนะนำ Ubuntu 20.04/22.04) หรือ Shared Hosting ทั่วไป
*   **Web Server:** Apache (แนะนำ) หรือ Nginx
*   **Language:** PHP 7.4 หรือ 8.x ขึ้นไป
*   **PHP Extensions ที่จำเป็น:**
    *   `curl` (สำคัญมากสำหรับ Smart Search)
    *   `pdo_mysql` (ใช้เชื่อมต่อฐานข้อมูล)
    *   `mbstring` (ใช้จัดการภาษาไทย)
    *   `json`
    *   `libxml` / `dom` (ใช้ขูดข้อมูลบทความวิจัย)
*   **Database:** MySQL 5.7+ หรือ MariaDB 10.x+
*   **SSL Certificate (HTTPS):** จำเป็นมากเพื่อให้ API ทำงานได้ถูกต้องและปลอดภัย

---

## 2. การเตรียมฐานข้อมูล (Database Migration)
1.  **Export:** จากหน้า Local (phpMyAdmin) ให้เลือกฐานข้อมูล `babybib_db` แล้วกด Export เป็นไฟล์ `.sql`
2.  **Create:** บน Hosting ใหม่ ให้สร้างฐานข้อมูลชื่อเดียวกัน (หรือชื่ออื่นตามความเหมาะสม) และตั้งค่า Collation เป็น `utf8mb4_unicode_ci`
3.  **Import:** นำไฟล์ `.sql` ที่ Export มา ทำการ Import เข้าสู่ฐานข้อมูลใหม่บน Server

---

## 3. การอัปโหลดและตั้งค่าไฟล์ (File Upload & Configuration)
1.  **Upload:** อัปโหลดไฟล์ทั้งหมดในโปรเจกต์ขึ้นไปไว้ที่ `public_html` หรือ Directory ที่ต้องการ
2.  **Setup Environment (`.env`):** เนื่องจากระบบใช้ไฟล์ `.env` ในการเก็บค่าสำคัญ (ผ่าน `includes/env.php`) ให้ตรวจสอบและแก้ไขไฟล์ `.env` บน Server ดังนี้:
    *   `DB_HOST`: ตั้งเป็น `localhost` (ส่วนใหญ่) หรือ API Host ของ DB
    *   `DB_NAME`: ชื่อฐานข้อมูลบน Server
    *   `DB_USER`: Username ฐานข้อมูล
    *   `DB_PASS`: Password ฐานข้อมูล
    *   `SITE_URL`: เปลี่ยนจาก `http://localhost/babybib` เป็น URL จริง (เช่น `https://yourdomain.com`)
    *   `SITE_ENV`: เปลี่ยนจาก `development` เป็น `production`
    *   `SESSION_COOKIE_SECURE`: ตั้งเป็น `1` เมื่อใช้งานผ่าน HTTPS

---

## 4. การจัดการสิทธิ์โฟลเดอร์ (Folder Permissions)
ตรวจสอบสิทธิ์ (chmod) ของโฟลเดอร์ที่ระบบต้องมีการเขียนไฟล์:
*   **Temporary Cache:** โดยปกติระบบใช้ `/tmp` ของ OS แต่ถ้าต้องการความปลอดภัยมากขึ้น สามารถตั้งโฟลเดอร์ cache เฉพาะในโปรเจกต์และตั้งสิทธิ์เป็น `775`
*   **Logs:** หากมีการเก็บ Log ไฟล์พิเศษ ต้องมั่นใจว่าโฟลเดอร์นั้น Web Server สามารถเขียนข้อมูลลงไปได้

---

## 5. การตั้งค่าความปลอดภัยเพิ่มเติม (Post-Deployment)
1.  **HTTPS Redirect:** ตั้งค่าให้ระบบเปลี่ยนเส้นทางจาก http ไป https เสมอ (ผ่านไฟล์ `.htaccess`)
2.  **Error Display:** ตรวจสอบใน `SITE_ENV` ว่าเป็น `production` เพื่อป้องกันไม่ให้ระบบโชว์ Error ที่เป็นความลับของโค้ดให้ผู้ใช้ทั่วไปเห็น
3.  **API Keys:** หากมีการใช้ Google Books API Key ในจำนวนมาก ให้เอาไปใส่ในอาร์เรย์ `GOOGLE_BOOKS_API_KEYS` ใน `includes/config.php` หรือ `.env` เพื่อสลับการใช้งาน (Rotation)

---

## 6. Checklist ก่อนเปิดใช้งานจริง
- [ ] ฐานข้อมูลเชื่อมต่อได้ปกติ (เช็คหน้า Login)
- [ ] Smart Search ค้นหาได้ปกติ (เช็คระบบ cURL)
- [ ] ภาษารองรับภาษาไทย (ไม่เป็นเครื่องหมายคำถาม)
- [ ] รูปภาพหน้าปกหนังสือแสดงผลได้ปกติ
- [ ] มีการตั้งค่า timezone ใน `.env` เป็น `Asia/Bangkok`

---
*Last Updated: 2024-03-03*
