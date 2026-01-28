# 🚀 Babybib Pre-Deployment Checklist
## ตรวจสอบก่อน Deploy ขึ้น Production

---

## 🔴 ต้องทำ (CRITICAL - ก่อน Deploy)

### 1. Database Security
- [ ] **ตั้งรหัสผ่าน MySQL** 
  - ไฟล์: `includes/config.php` บรรทัด 22
  - เปลี่ยนจาก `define('DB_PASS', '');` เป็น `define('DB_PASS', 'your_secure_password');`

### 2. HTTPS Setup
- [ ] **เปิดใช้งาน SSL Certificate** บนเซิร์ฟเวอร์
- [ ] **เปลี่ยน session.cookie_secure**
  - ไฟล์: `includes/config.php` บรรทัด 16
  - เปลี่ยนจาก `ini_set('session.cookie_secure', 0);` เป็น `ini_set('session.cookie_secure', 1);`

### 3. Database Schema
- [ ] **Import ฐานข้อมูลเริ่มต้น** - `database/schema.sql`
- [ ] **รัน SQL เพิ่มเติม** ใน folder `sql/`:
  - `add_indexes.sql` (สำคัญสำหรับ Performance)
  - `email_verification_table.sql`
  - `password_reset_table.sql`
  - `rating_table.sql`
  - `support_reports_table.sql`
  - `visits_table.sql`

### 4. Folder Permissions
```bash
chmod 755 /path/to/babybib_db
chmod 755 /path/to/babybib_db/uploads
chmod 755 /path/to/babybib_db/uploads/avatars
chmod 755 /path/to/babybib_db/api/cache
chmod 755 /path/to/babybib_db/logs
chmod 755 /path/to/babybib_db/backups
```

---

## 🟡 ควรทำ (RECOMMENDED)

### 5. Email Configuration
- [ ] **ตั้งค่า SMTP** สำหรับส่งอีเมลยืนยัน/รีเซ็ตรหัสผ่าน
  - ตรวจสอบไฟล์: `includes/mailer.php` (ถ้ามี)
  - หรือใช้ Extension เช่น PHPMailer

### 6. Session Configuration
- [ ] **ตรวจสอบ Session Timeout**
  - ปัจจุบัน: 10 นาที (600 วินาที)
  - ไฟล์: `includes/session.php` บรรทัด 17
  - ปรับได้ตามต้องการ

### 7. Backup Setup
- [ ] **ตั้งค่า Cron Job สำหรับ Backup อัตโนมัติ**
```bash
# ทำ backup ทุกวันตอน 02:00
0 2 * * * /usr/bin/mysqldump -u root -p'PASSWORD' babybib_db | gzip > /path/to/backups/backup_$(date +\%Y\%m\%d).sql.gz
```

### 8. Error Logging
- [ ] **ตรวจสอบ Log Directory มีสิทธิ์เขียน**
- [ ] **ตั้งค่า Log Rotation** เพื่อไม่ให้ไฟล์ใหญ่เกินไป

---

## 🟢 เสร็จแล้ว (COMPLETED)

| รายการ | สถานะ |
|--------|-------|
| ✅ DEV MODE ปิดแล้ว | `api/auth/login.php` |
| ✅ Rate Limiting เพิ่มแล้ว | 5 ครั้ง/15 นาที |
| ✅ Security Headers เพิ่มแล้ว | `.htaccess` |
| ✅ Sensitive Files Protection | `.htaccess` |
| ✅ SQL Injection Protected | PDO Prepared Statements |
| ✅ XSS Protected | `sanitize()` function |
| ✅ CSRF Protection | Token system |
| ✅ Password Hashing | `password_hash()` + `password_verify()` |
| ✅ Smart Validation | ตรวจสอบฟิลด์ก่อนบันทึก |

---

## 📋 Environment Variables (แนะนำ)

สร้างไฟล์ `.env` (จะถูก ignore โดย git):

```env
# Database
DB_HOST=localhost
DB_NAME=babybib_db
DB_USER=your_db_user
DB_PASS=your_secure_password

# Site
SITE_URL=https://yourdomain.com
SITE_ENV=production

# Email (Optional)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your@email.com
SMTP_PASS=your_app_password
```

---

## 🧪 Pre-Launch Testing Checklist

- [ ] Register ผู้ใช้ใหม่
- [ ] Email Verification
- [ ] Login / Logout
- [ ] Forgot Password
- [ ] สร้างบรรณานุกรม (ทุกประเภท)
- [ ] Smart Search (ISBN, DOI, Keyword)
- [ ] Edit / Delete บรรณานุกรม
- [ ] Export to Word/PDF
- [ ] Admin Dashboard
- [ ] Mobile Responsive

---

## 🔗 Post-Launch

- [ ] ตรวจสอบ Google Search Console
- [ ] ตั้งค่า Google Analytics (ถ้าต้องการ)
- [ ] Monitor Error Logs สัปดาห์แรก
- [ ] Backup ข้อมูลรายวัน

---

*สร้างโดย Antigravity AI Assistant - 2026-01-28*
