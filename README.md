# Dealka Marketplace - PHP + MySQL

ระบบ Marketplace ที่ปลอดภัยสำหรับซื้อขายสินค้า โดยใช้ระบบ Escrow (เว็บเป็นคนกลางเก็บเงิน)

git remote add origin https://github.com/PeeTer-1/dealka.git
git branch -M main
git push -u origin main

## 🎯 ฟีเจอร์หลัก

### ระบบผู้ใช้
- ✅ สมัครสมาชิก (Register)
- ✅ เข้าสู่ระบบ (Login)
- ✅ 1 บัญชี = ซื้อได้ + ขายได้
- ✅ ดูยอดเงิน (Balance)
- ✅ ถอนเงิน (Withdrawal)

### ระบบสินค้า
- ✅ ลงขายสินค้า (Add Product)
- ✅ แก้ไขสินค้า (Edit Product)
- ✅ ลบสินค้า (Delete Product)
- ✅ ดูสินค้า (View Products)
- ✅ ค้นหาตามหมวดหมู่ (Category Filter)

### ระบบออเดอร์
- ✅ สั่งซื้อสินค้า (Create Order)
- ✅ ดูออเดอร์ (View Orders)
- ✅ ส่งสินค้า (Mark as Shipped)
- ✅ ยืนยันการรับสินค้า (Confirm Receipt)

### ระบบชำระเงิน
- ✅ QR Code (BCEL ONE)
- ✅ อัปโหลดสลิป (Upload Slip)
- ✅ ตรวจสลิป (Verify Slip) - Admin
- ✅ อนุมัติ/ปฏิเสธการชำระเงิน (Approve/Reject Payment)

### ระบบถอนเงิน
- ✅ ยื่นคำขอถอนเงิน (Request Withdrawal)
- ✅ ค่าธรรมเนียม 1% (ขั้นต่ำ 1,000 LAK)
- ✅ อนุมัติ/ปฏิเสธการถอนเงิน (Approve/Reject Withdrawal)

### ระบบแอดมิน
- ✅ อนุมัติสินค้า (Approve Products)
- ✅ ตรวจสลิป (Verify Payments)
- ✅ อนุมัติถอนเงิน (Approve Withdrawals)
- ✅ ดูบันทึก (View Logs)

## 📊 ค่าธรรมเนียม

| การกระทำ | ค่าธรรมเนียม |
|---------|-----------|
| การขาย | 3% ของราคาสินค้า |
| การถอนเงิน | 1% (ขั้นต่ำ 1,000 LAK) |

## 🔄 ขั้นตอนการซื้อขาย

### ผู้ซื้อ
1. สมัครสมาชิก
2. ดูสินค้า
3. สั่งซื้อ
4. กรอกที่อยู่จัดส่ง
5. โอนเงินผ่าน BCEL ONE QR
6. อัปโหลดสลิป
7. รอแอดมินตรวจสลิป
8. รอผู้ขายส่งสินค้า
9. ยืนยันการรับสินค้า

### ผู้ขาย
1. สมัครสมาชิก
2. ลงขายสินค้า
3. รออนุมัติจากแอดมิน
4. รอคนซื้อ
5. เมื่อได้ออเดอร์ → ส่งสินค้า
6. เงินเข้า Balance หลังผู้ซื้อยืนยัน
7. ถอนเงิน

### แอดมิน
1. อนุมัติสินค้า
2. ตรวจสลิป → อนุมัติ/ปฏิเสธ
3. อนุมัติการถอนเงิน

## 📁 โครงสร้างไฟล์

```
dealka_new/
├── config/
│   └── db.php                 # Database configuration & PDO
├── includes/
│   ├── auth.php              # Authentication functions
│   ├── functions.php         # Utility functions
│   ├── header.php            # Header template
│   ├── admin_header.php      # Admin header template
│   └── footer.php            # Footer template
├── pages/
│   ├── auth/
│   │   ├── login.php         # Login page
│   │   ├── register.php      # Register page
│   │   └── logout.php        # Logout
│   ├── user/
│   │   ├── dashboard.php     # User dashboard
│   │   ├── orders.php        # Orders list
│   │   ├── order_detail.php  # Order detail
│   │   ├── checkout.php      # Checkout
│   │   ├── payment.php       # Payment with QR & slip upload
│   │   └── withdraw.php      # Withdrawal
│   ├── seller/
│   │   ├── add_product.php   # Add product
│   │   ├── manage_products.php # Manage products
│   │   ├── edit_product.php  # Edit product
│   │   └── delete_product.php # Delete product
│   ├── admin/
│   │   ├── dashboard.php     # Admin dashboard
│   │   ├── approve_products.php # Approve products
│   │   ├── verify_payments.php # Verify payments
│   │   ├── approve_withdrawals.php # Approve withdrawals
│   │   └── view_logs.php     # View audit logs
│   └── product.php           # Product detail page
├── assets/
│   └── css/
│       └── style.css         # Main stylesheet
├── uploads/
│   ├── products/             # Product images
│   └── slips/                # Payment slips
├── logs/
│   └── php_errors.log        # Error logs
├── schema.sql                # Database schema
├── index.php                 # Homepage
└── README.md                 # This file
```

## 🗄️ ฐานข้อมูล

### ตาราง
- `users` - ข้อมูลผู้ใช้
- `products` - สินค้า
- `orders` - ออเดอร์
- `shipping_addresses` - ที่อยู่จัดส่ง
- `payments` - ข้อมูลการชำระเงิน
- `withdrawals` - ข้อมูลการถอนเงิน
- `central_account` - บัญชีกลาง (Escrow)
- `logs` - บันทึกการกระทำ (Audit Trail)

## 🚀 การติดตั้ง

### ข้อกำหนด
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx

### ขั้นตอน

1. **สร้างฐานข้อมูล**
```bash
mysql -u root -p
CREATE DATABASE dealka_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dealka_db;
SOURCE schema.sql;
```

2. **แก้ไข config/db.php**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'dealka_db');
```

3. **ตั้งค่า permissions**
```bash
chmod 755 uploads/
chmod 755 uploads/products/
chmod 755 uploads/slips/
chmod 755 logs/
```

4. **เข้าถึงเว็บ**
```
http://localhost/dealka_new/
```

## 👤 บัญชี Test

### Admin
- Username: `admin`
- Password: `admin123`

## 🔐 ความปลอดภัย

- ✅ PDO Prepared Statements (ป้องกัน SQL Injection)
- ✅ Password Hashing (bcrypt)
- ✅ CSRF Token Protection
- ✅ Session Management
- ✅ Input Sanitization
- ✅ File Upload Validation
- ✅ Database Transactions (Atomicity)
- ✅ Audit Logging

## 📝 ฟีเจอร์เพิ่มเติม

### ที่สามารถขยายได้
- [ ] ระบบ Rating & Review
- [ ] ระบบ Messaging
- [ ] ระบบ Notification
- [ ] ระบบ Dispute Resolution
- [ ] Integration กับ Payment Gateway
- [ ] Mobile App
- [ ] API REST

## 🐛 Known Issues

ไม่มี

## 📞 Support

สำหรับคำถามหรือปัญหา กรุณาติดต่อ

## 📄 License

MIT License

---

**สร้างด้วย ❤️ สำหรับ Dealka Marketplace**
"# dealka" 
