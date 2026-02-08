# Dealka Marketplace - Installation Guide

git remote add origin https://github.com/PeeTer-1/dealka.git
git branch -M main
git push -u origin main

## 📋 ข้อกำหนดระบบ

- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Apache หรือ Nginx
- 50 MB พื้นที่ว่าง

## 🔧 ขั้นตอนการติดตั้ง

### 1. ดาวน์โหลดและแตกไฟล์

```bash
# ดาวน์โหลดไฟล์ ZIP
# แตกไฟล์ไปยัง /var/www/html/dealka_new หรือ /home/username/public_html/dealka_new
unzip dealka_php.zip -d /var/www/html/
```

### 2. สร้างฐานข้อมูล

```bash
# เข้าสู่ MySQL
mysql -u root -p

# สร้างฐานข้อมูล
CREATE DATABASE dealka_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dealka_db;

# นำเข้า schema
SOURCE /path/to/dealka_new/schema.sql;

# ออกจาก MySQL
EXIT;
```

### 3. แก้ไขไฟล์ config/db.php

```php
// config/db.php
define('DB_HOST', 'localhost');      // ชื่อเซิร์ฟเวอร์ MySQL
define('DB_USER', 'root');           // ชื่อผู้ใช้ MySQL
define('DB_PASS', 'password');       // รหัสผ่าน MySQL
define('DB_NAME', 'dealka_db');      // ชื่อฐานข้อมูล
define('DB_PORT', 3306);             // พอร์ต MySQL
```

### 4. ตั้งค่า Permissions

```bash
# ตั้งค่าสิทธิ์ของโฟลเดอร์
chmod 755 uploads/
chmod 755 uploads/products/
chmod 755 uploads/slips/
chmod 755 logs/

# ตั้งค่า owner (ถ้าจำเป็น)
chown -R www-data:www-data /var/www/html/dealka_new/
```

### 5. ตั้งค่า Apache Virtual Host (ถ้าจำเป็น)

สร้างไฟล์ `/etc/apache2/sites-available/dealka.conf`:

```apache
<VirtualHost *:80>
    ServerName dealka.local
    ServerAlias www.dealka.local
    DocumentRoot /var/www/html/dealka_new

    <Directory /var/www/html/dealka_new>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/dealka_error.log
    CustomLog ${APACHE_LOG_DIR}/dealka_access.log combined
</VirtualHost>
```

เปิดใช้งาน:
```bash
a2ensite dealka.conf
a2enmod rewrite
systemctl restart apache2
```

### 6. เข้าถึงเว็บ

```
http://localhost/dealka_new/
หรือ
http://dealka.local/
```

## 👤 บัญชี Test

### Admin Account
- **Username:** admin
- **Password:** admin123
- **URL:** http://localhost/dealka_new/pages/admin/dashboard.php

### ทดสอบการสร้างบัญชี
1. ไปที่ http://localhost/dealka_new/pages/auth/register.php
2. สมัครสมาชิกใหม่
3. เข้าสู่ระบบ

## 🔍 การตรวจสอบการติดตั้ง

### ตรวจสอบ PHP
```bash
php -v
php -m | grep pdo_mysql
```

### ตรวจสอบ MySQL
```bash
mysql -u root -p -e "SELECT VERSION();"
```

### ตรวจสอบการเชื่อมต่อ
1. เข้าไปที่ http://localhost/dealka_new/
2. ถ้าเห็นหน้าแรก = ติดตั้งสำเร็จ
3. ลองสมัครสมาชิก = ฐานข้อมูลทำงาน

## 🛠️ Troubleshooting

### ปัญหา: "Cannot connect to database"
- ✅ ตรวจสอบ DB_HOST, DB_USER, DB_PASS ใน config/db.php
- ✅ ตรวจสอบว่า MySQL ทำงานอยู่: `systemctl status mysql`
- ✅ ตรวจสอบ PDO extension: `php -m | grep pdo_mysql`

### ปัญหา: "Permission denied" สำหรับ uploads/
- ✅ รัน: `chmod 755 uploads/`
- ✅ ตรวจสอบ owner: `ls -la uploads/`

### ปัญหา: "Class 'PDO' not found"
- ✅ เปิดใช้ PDO extension ใน php.ini
- ✅ ค้นหา: `extension=pdo_mysql`
- ✅ รีสตาร์ท Apache: `systemctl restart apache2`

### ปัญหา: "Cannot upload files"
- ✅ ตรวจสอบ upload_max_filesize ใน php.ini
- ✅ ตรวจสอบ post_max_size ใน php.ini
- ✅ ตรวจสอบ permissions ของ uploads/

### ปัญหา: "Session not working"
- ✅ ตรวจสอบ session.save_path ใน php.ini
- ✅ ตรวจสอบ permissions ของ session directory

## 📊 ตรวจสอบฐานข้อมูล

```bash
mysql -u root -p dealka_db

# ดูตาราง
SHOW TABLES;

# ดูข้อมูลผู้ใช้
SELECT * FROM users;

# ดูสินค้า
SELECT * FROM products;

# ดูบันทึก
SELECT * FROM logs ORDER BY created_at DESC LIMIT 10;
```

## 🔐 ความปลอดภัยเพิ่มเติม

### เปลี่ยนรหัสผ่าน Admin
```bash
mysql -u root -p dealka_db

UPDATE users SET password_hash = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/KFm' 
WHERE username = 'admin';

# รหัสผ่านใหม่: admin123
```

### ตั้งค่า SSL (HTTPS)
```bash
# ติดตั้ง Certbot
sudo apt-get install certbot python3-certbot-apache

# สร้าง SSL certificate
sudo certbot --apache -d dealka.local

# ตรวจสอบ renewal
sudo certbot renew --dry-run
```

### ปิดใช้งาน Directory Listing
เพิ่มใน .htaccess:
```apache
Options -Indexes
```

## 📈 Optimization

### เพิ่มประสิทธิภาพ MySQL
```bash
# ตรวจสอบ slow queries
mysql -u root -p dealka_db
SET GLOBAL slow_query_log = 'ON';
```

### เพิ่มประสิทธิภาพ PHP
```php
// php.ini
memory_limit = 256M
max_execution_time = 30
upload_max_filesize = 10M
post_max_size = 10M
```

## 🚀 Production Deployment

### ขั้นตอน
1. ปิด debug mode
2. ตั้งค่า HTTPS
3. ตั้งค่า Firewall
4. ตั้งค่า Backup
5. ตั้งค่า Monitoring
6. ตั้งค่า CDN (ถ้าจำเป็น)

## 📞 Support

สำหรับปัญหาการติดตั้ง กรุณาตรวจสอบ:
1. error logs: `logs/php_errors.log`
2. Apache logs: `/var/log/apache2/dealka_error.log`
3. MySQL logs: `/var/log/mysql/error.log`

---

**ติดตั้งสำเร็จ! ยินดีต้อนรับสู่ Dealka Marketplace** 🎉
