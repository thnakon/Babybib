# 🚀 Babybib Pre-Deployment Checklist
## ตรวจสอบก่อน Deploy ขึ้น Production

**อัปเดตล่าสุด:** 28 มกราคม 2569

---

## � ขั้นตอนการ Deploy (Overview)

```
1. Upload โค้ดไปยัง Server
2. สร้างและตั้งค่าไฟล์ .env
3. Import ฐานข้อมูล
4. ตั้งค่า Folder Permissions
5. ตรวจสอบ HTTPS/SSL
6. ทดสอบทุก Feature
7. Go Live! 🎉
```

---

## �🔴 ต้องทำ (CRITICAL - ก่อน Deploy)

### 1. สร้างไฟล์ .env บน Production Server

⚠️ **ห้าม Upload ไฟล์ .env จาก Development!** ให้สร้างใหม่บน Server โดยตรง

```bash
# บน Server
cd /path/to/babybib_db
cp .env.example .env
nano .env   # หรือ vi .env
```

**ค่าที่ต้องแก้ไขสำหรับ Production:**

```env
# Database Configuration (สำคัญมาก!)
DB_HOST=localhost
DB_NAME=babybib_db
DB_USER=babybib_user        # ไม่ใช้ root
DB_PASS=STRONG_PASSWORD_HERE  # รหัสผ่านที่แข็งแรง

# Site Configuration
SITE_URL=https://yourdomain.com  # ใช้ HTTPS!
SITE_NAME=Babybib
SITE_ENV=production              # เปลี่ยนเป็น production!

# Session Security
SESSION_COOKIE_SECURE=1          # เปิดเมื่อใช้ HTTPS

# Debug Mode
DEBUG_MODE=false                 # ปิด Debug Mode!

# Session Timeout
SESSION_TIMEOUT=1800             # 30 นาที (ปรับได้)

# Timezone
TIMEZONE=Asia/Bangkok
```

### 2. Database Setup

```bash
# 1. สร้าง Database และ User (ทำบน MySQL)
mysql -u root -p
```

```sql
-- สร้าง Database
CREATE DATABASE babybib_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- สร้าง User (ไม่ใช้ root!)
CREATE USER 'babybib_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON babybib_db.* TO 'babybib_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# 2. Import Schema
mysql -u babybib_user -p babybib_db < database/schema.sql

# 3. รัน SQL เพิ่มเติม (ตามลำดับ)
mysql -u babybib_user -p babybib_db < sql/add_indexes.sql
mysql -u babybib_user -p babybib_db < sql/email_verification_table.sql
mysql -u babybib_user -p babybib_db < sql/password_reset_table.sql
mysql -u babybib_user -p babybib_db < sql/rating_table.sql
mysql -u babybib_user -p babybib_db < sql/support_reports_table.sql
mysql -u babybib_user -p babybib_db < sql/visits_table.sql
```

### 3. HTTPS / SSL Setup

- [ ] **ติดตั้ง SSL Certificate** (Let's Encrypt ฟรี)
- [ ] **Force HTTPS** ใน `.htaccess` (มีอยู่แล้ว - ตรวจสอบว่าเปิดใช้งาน)
- [ ] **ตั้งค่า SESSION_COOKIE_SECURE=1** ใน `.env`

```bash
# ติดตั้ง Let's Encrypt (Ubuntu/Debian)
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

### 4. Folder Permissions

```bash
# ตั้ง Permissions ที่ถูกต้อง
cd /path/to/babybib_db

# โฟลเดอร์หลัก
chmod 755 .
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# โฟลเดอร์ที่ต้องเขียนได้
chmod 775 uploads
chmod 775 uploads/avatars
chmod 775 api/cache
chmod 775 logs
chmod 775 backups

# ไฟล์ที่ต้องปกป้อง (ห้าม Web เข้าถึง)
chmod 600 .env
```

### 5. ตรวจสอบ .htaccess Security

เปิดไฟล์ `.htaccess` และตรวจสอบว่ามี:

```apache
# Block access to sensitive files
<FilesMatch "^\.env|\.git|composer\.(json|lock)$">
    Require all denied
</FilesMatch>

# Block access to sensitive directories
RedirectMatch 403 ^/\.git
RedirectMatch 403 ^/sql
RedirectMatch 403 ^/database
RedirectMatch 403 ^/docs
RedirectMatch 403 ^/backups
```

---

## 🟡 ควรทำ (RECOMMENDED)

### 6. Email Configuration (ถ้าต้องการระบบอีเมล)

```env
# เพิ่มใน .env
MAIL_ENABLED=true
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password      # App Password, ไม่ใช่รหัสปกติ!
SMTP_FROM_NAME=Babybib
SMTP_FROM_EMAIL=noreply@yourdomain.com
```

> 📌 **หมายเหตุ Gmail:** ต้องสร้าง [App Password](https://myaccount.google.com/apppasswords) ไม่ใช่รหัสผ่านปกติ

### 7. Backup Automation

```bash
# สร้าง Cron Job สำหรับ Daily Backup
crontab -e

# เพิ่มบรรทัดนี้ (Backup ทุกวันตอน 02:00)
0 2 * * * /usr/bin/mysqldump -u babybib_user -p'PASSWORD' babybib_db | gzip > /path/to/babybib_db/backups/backup_$(date +\%Y\%m\%d).sql.gz

# ลบ backup เก่ากว่า 30 วัน (Optional)
0 3 * * * find /path/to/babybib_db/backups -name "*.sql.gz" -mtime +30 -delete
```

### 8. Error Logging

```bash
# ตรวจสอบ Error Log
tail -f /var/log/apache2/error.log
# หรือ
tail -f /path/to/babybib_db/logs/error.log
```

### 9. Performance Optimization

- [ ] **เปิด OPcache** (PHP)
- [ ] **เปิด Gzip Compression** (มีใน .htaccess แล้ว)
- [ ] **ตั้งค่า Browser Caching** (มีใน .htaccess แล้ว)

```bash
# ตรวจสอบ OPcache
php -i | grep opcache
```

---

## 🟢 สิ่งที่ทำไปแล้ว (COMPLETED)

| รายการ | สถานะ | หมายเหตุ |
|--------|-------|----------|
| ✅ Environment Variables (.env) | เสร็จ | ใช้ `env()` helper |
| ✅ DEV MODE ปิดแล้ว | เสร็จ | `api/auth/login.php` |
| ✅ Rate Limiting | เสร็จ | 5 ครั้ง/15 นาที |
| ✅ Security Headers | เสร็จ | `.htaccess` |
| ✅ Sensitive Files Protection | เสร็จ | `.htaccess` |
| ✅ SQL Injection Protected | เสร็จ | PDO Prepared Statements |
| ✅ XSS Protected | เสร็จ | `sanitize()` function |
| ✅ CSRF Protection | เสร็จ | Token system |
| ✅ Password Hashing | เสร็จ | `password_hash()` + `password_verify()` |
| ✅ API Caching | เสร็จ | Session + File cache |
| ✅ Session Timeout | เสร็จ | Configurable via .env |

---

## 🧪 Pre-Launch Testing Checklist

เปิดเว็บบน Production และทดสอบทุกฟีเจอร์:

### Authentication
- [ ] ลงทะเบียนผู้ใช้ใหม่
- [ ] Email Verification (ถ้าเปิดใช้)
- [ ] Login / Logout
- [ ] Forgot Password
- [ ] Session Timeout (รอ 30 นาทีแล้ว Logout อัตโนมัติ)

### Core Features
- [ ] สร้างบรรณานุกรมใหม่ (ทุกประเภท)
- [ ] Edit / Delete บรรณานุกรม
- [ ] สร้าง Project และรวมบรรณานุกรม
- [ ] Export to Word
- [ ] Export to PDF

### User Management
- [ ] Edit Profile
- [ ] Change Password
- [ ] Upload Avatar

### Admin Features
- [ ] Admin Dashboard
- [ ] User Management
- [ ] Activity Logs
- [ ] Support Reports
- [ ] Database Backup

### Responsiveness
- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

---

## 🔗 Post-Launch Tasks

### สัปดาห์แรก
- [ ] Monitor Error Logs ทุกวัน
- [ ] ตรวจสอบ Database Performance
- [ ] Backup ข้อมูลทุกวัน
- [ ] ตอบกลับ Support Reports

### เดือนแรก
- [ ] ตรวจสอบ Google Search Console (SEO)
- [ ] ตั้งค่า Google Analytics (ถ้าต้องการ)
- [ ] วิเคราะห์ User Feedback
- [ ] ปรับปรุงตาม User Request

---

## 🆘 Troubleshooting

### ปัญหาที่พบบ่อย

**1. Database Connection Failed**
```bash
# ตรวจสอบ .env
cat .env | grep DB_

# ทดสอบ Connection
mysql -u babybib_user -p babybib_db -e "SELECT 1"
```

**2. Permission Denied**
```bash
# ตรวจสอบ Owner
ls -la uploads/
# แก้ไข Owner (Apache)
chown -R www-data:www-data uploads/ logs/ backups/ api/cache/
```

**3. 500 Internal Server Error**
```bash
# ดู Error Log
tail -50 /var/log/apache2/error.log
# หรือ
tail -50 /path/to/babybib_db/logs/error.log
```

**4. Session ไม่ทำงาน (HTTPS)**
```bash
# ตรวจสอบ .env
grep SESSION_COOKIE_SECURE .env
# ต้องเป็น 1 เมื่อใช้ HTTPS
```

---

## 📞 Support

หากมีปัญหา ติดต่อ:
- **Email:** support@yourdomain.com
- **GitHub Issues:** https://github.com/yourusername/babybib/issues

---

**Happy Deploying! 🚀**
