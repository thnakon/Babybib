# 🚀 Babybib - คู่มือขึ้น Production

> คู่มือนี้รวบรวมข้อมูลสำคัญและขั้นตอนทั้งหมดที่ต้องทำก่อนนำระบบ Babybib ขึ้น Production

---

## 📋 สารบัญ

1. [System Requirements](#system-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Configuration Files](#configuration-files)
4. [Database Setup](#database-setup)
5. [Security Checklist](#security-checklist)
6. [Email Configuration](#email-configuration)
7. [Backup Setup](#backup-setup)
8. [Deployment Steps](#deployment-steps)
9. [Post-Deployment Verification](#post-deployment-verification)
10. [Maintenance Guide](#maintenance-guide)

---

## 1. System Requirements

### Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 7.4+ | 8.1+ |
| MySQL | 5.7+ | 8.0+ |
| Apache | 2.4+ | 2.4+ |
| RAM | 512MB | 2GB+ |
| Disk Space | 1GB | 5GB+ |

### Required PHP Extensions

```
- pdo_mysql
- mbstring
- json
- fileinfo
- gd หรือ imagick
- zip
- openssl
```

ตรวจสอบด้วยคำสั่ง:
```bash
php -m | grep -E "(pdo_mysql|mbstring|json|fileinfo|gd|zip|openssl)"
```

---

## 2. Pre-Deployment Checklist

### ✅ สิ่งที่ต้องทำก่อน Deploy

- [ ] **เปลี่ยน Database Credentials** ใน `includes/config.php`
- [ ] **เปลี่ยน RewriteBase** ใน `.htaccess` (ถ้าไม่ได้อยู่ใน `/babybib_db/`)
- [ ] **ตั้งค่า HTTPS** และเปิด `cookie_secure = 1`
- [ ] **ตั้งค่า `display_errors = 0`** สำหรับ Production
- [ ] **ลบไฟล์ทดสอบ** ถ้ามี
- [ ] **สร้างโฟลเดอร์ที่จำเป็น** (logs, backups)
- [ ] **ตั้งค่า Permissions** ให้ถูกต้อง
- [ ] **ตรวจสอบ Email SMTP** ว่าทำงานได้

---

## 3. Configuration Files

### 3.1 includes/config.php

```php
// ======== เปลี่ยนค่าเหล่านี้สำหรับ Production ========

// Database
define('DB_HOST', 'localhost');           // เปลี่ยนตาม hosting
define('DB_NAME', 'babybib_db');           // ชื่อ database
define('DB_USER', 'your_db_user');         // ⚠️ เปลี่ยนจาก root
define('DB_PASS', 'your_secure_password'); // ⚠️ ตั้งรหัสที่แข็งแกร่ง

// Site URL
define('SITE_URL', 'https://yourdomain.com'); // ⚠️ ใส่ domain จริง

// Error display
ini_set('display_errors', 0);  // ⚠️ ต้องเป็น 0 สำหรับ Production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

// Session
ini_set('session.cookie_secure', 1);  // ⚠️ เปิดเมื่อใช้ HTTPS
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
```

### 3.2 .htaccess

```apache
# ⚠️ เปลี่ยน RewriteBase ตามตำแหน่งที่ติดตั้ง
RewriteBase /                 # ถ้าอยู่ที่ root
RewriteBase /babybib_db/      # ถ้าอยู่ใน subdirectory

# Error Pages - ปรับ path ตาม RewriteBase
ErrorDocument 403 /errors/403.php
ErrorDocument 404 /errors/404.php
ErrorDocument 500 /errors/500.php
```

---

## 4. Database Setup

### 4.1 สร้าง Database ใหม่

```sql
CREATE DATABASE babybib_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'babybib_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON babybib_db.* TO 'babybib_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4.2 Import Schema

```bash
mysql -u babybib_user -p babybib_db < database/database.sql
mysql -u babybib_user -p babybib_db < database/resource_types.sql
```

### 4.3 เปลี่ยนรหัสผ่าน Admin

> ⚠️ **สำคัญมาก!** รหัสผ่านเริ่มต้นคือ `Admin@123` ต้องเปลี่ยนทันที!

```sql
-- สร้างรหัสผ่านใหม่ (ทำใน PHP)
echo password_hash('YourNewSecurePassword', PASSWORD_DEFAULT);

-- อัปเดตในฐานข้อมูล
UPDATE users SET password = '$2y$10$YourHashedPasswordHere' WHERE id = 1;
```

---

## 5. Security Checklist

### ✅ รายการตรวจสอบความปลอดภัย

| รายการ | ไฟล์ | สถานะ |
|--------|------|-------|
| Upload PHP Prevention | `uploads/.htaccess` | ✅ |
| Logs Protection | `logs/.htaccess` | ✅ |
| Backups Protection | `backups/.htaccess` | ✅ |
| Security Headers | `includes/security-headers.php` | ✅ |
| CSRF Protection | `includes/session.php` | ✅ |
| XSS Protection | ตลอดทั้งระบบ | ✅ |
| SQL Injection Prevention | Prepared Statements | ✅ |

### Folder Permissions

```bash
# ตั้งค่า permissions
chmod 755 /path/to/babybib/
chmod 755 /path/to/babybib/uploads/
chmod 755 /path/to/babybib/uploads/avatars/
chmod 755 /path/to/babybib/logs/
chmod 755 /path/to/babybib/backups/

# ให้ web server เป็นเจ้าของ (Linux)
chown -R www-data:www-data /path/to/babybib/uploads/
chown -R www-data:www-data /path/to/babybib/logs/
chown -R www-data:www-data /path/to/babybib/backups/
```

---

## 6. Email Configuration

### 6.1 การตั้งค่า Gmail App Password

1. เข้า Google Account → Security
2. เปิด 2-Step Verification
3. ไปที่ App passwords
4. สร้าง App password ใหม่
5. คัดลอก 16-digit password

### 6.2 ตั้งค่าในระบบ

1. Login เป็น Admin
2. ไปที่ Settings → การตั้งค่าอีเมล (SMTP)
3. กรอก:
   - SMTP Username: `your-email@gmail.com`
   - Email App Password: `xxxx xxxx xxxx xxxx`

### 6.3 ทดสอบการส่งอีเมล

```php
// ทดสอบด้วยการ register user ใหม่
// หรือใช้ Password Reset
```

---

## 7. Backup Setup

### 7.1 Manual Backup

```bash
# Backup Database
./scripts/backup_database.sh

# Backup Files
./scripts/backup_files.sh

# List Backups
./scripts/restore_backup.sh

# Restore
./scripts/restore_backup.sh babybib_db_20260114_123456.sql.gz
```

### 7.2 Automated Backup (Cron)

```bash
# เปิด crontab
crontab -e

# เพิ่มบรรทัดนี้ (Backup ทุกวัน 02:00)
0 2 * * * /path/to/babybib/scripts/backup_database.sh --cron

# Backup ทุกสัปดาห์ (วันอาทิตย์ 03:00)
0 3 * * 0 /path/to/babybib/scripts/backup_files.sh
```

### 7.3 Backup ผ่าน Web

- เข้า Admin Panel → สำรองข้อมูล
- กด "สร้าง Backup"
- ดาวน์โหลดไฟล์เก็บไว้

---

## 8. Deployment Steps

### Step 1: เตรียมไฟล์

```bash
# สร้าง production bundle
zip -r babybib_production.zip . -x "*.git*" -x "*.DS_Store"
```

### Step 2: Upload ไฟล์

- ใช้ FTP/SFTP upload ไปยัง server
- หรือใช้ Git: `git clone` / `git pull`

### Step 3: สร้างโฟลเดอร์

```bash
mkdir -p logs backups
chmod 755 logs backups uploads uploads/avatars
```

### Step 4: แก้ไข Config

```bash
# แก้ไขไฟล์ config
nano includes/config.php
nano .htaccess
```

### Step 5: Import Database

```bash
mysql -u username -p database_name < database/database.sql
mysql -u username -p database_name < database/resource_types.sql
```

### Step 6: ทดสอบ

1. เข้าหน้าแรก
2. ลอง Login
3. ตรวจสอบ Error Logs

---

## 9. Post-Deployment Verification

### ✅ Checklist หลัง Deploy

- [ ] หน้าแรกแสดงผลถูกต้อง
- [ ] Login/Logout ทำงานได้
- [ ] Register + Email Verification ทำงานได้
- [ ] สร้างบรรณานุกรมได้
- [ ] Export เป็น DOCX ได้
- [ ] Admin Panel เข้าถึงได้
- [ ] Backup ทำงานได้
- [ ] Error Pages แสดงถูกต้อง (ลอง /asdfasdf)
- [ ] HTTPS ทำงานได้ (ถ้าเปิดใช้)
- [ ] ไม่มี Error ใน logs

### ตรวจสอบ Logs

```bash
# PHP Errors
tail -f logs/php-errors.log

# Security Events
tail -f logs/security.log

# Backup Logs
tail -f logs/backup.log
```

---

## 10. Maintenance Guide

### 10.1 การอัปเดตระบบ

```bash
# Backup ก่อนอัปเดต
./scripts/backup_database.sh
./scripts/backup_files.sh

# อัปเดตไฟล์
git pull origin main

# Clear cache (ถ้ามี)
```

### 10.2 การ Monitor

**รายวัน:**
- ตรวจสอบ Error Logs
- ตรวจสอบ Disk Space

**รายสัปดาห์:**
- Review Activity Logs
- ตรวจสอบ Backup Files
- ตรวจสอบ Performance

**รายเดือน:**
- Security Audit
- Database Optimization
- Update Dependencies

### 10.3 Troubleshooting

| ปัญหา | สาเหตุ | แก้ไข |
|-------|--------|-------|
| 500 Error | PHP Error | ดู `logs/php-errors.log` |
| Login ไม่ได้ | Session/Cookie | ตรวจสอบ `cookie_secure` |
| Email ไม่ส่ง | SMTP Config | ตรวจสอบ App Password |
| Upload ไม่ได้ | Permissions | `chmod 755 uploads/` |
| Backup ไม่ได้ | Permissions | `chmod 755 backups/` |

---

## 📞 Contact & Support

- **Developer**: Babybib Team
- **Documentation**: `/docs/`
- **Issues**: ติดต่อผู้ดูแลระบบ

---

## 📄 Version History

| Version | Date | Changes |
|---------|------|---------|
| 2.0.0 | 2026-01-14 | Production Ready - Added security, backups, indexes |
| 1.0.0 | 2025-12-XX | Initial Release |

---

> 📌 **หมายเหตุ:** เอกสารนี้อัปเดตล่าสุด 2026-01-14
