# Ban Linh Kiện — Web Bán Linh Kiện Máy Tính

> Đồ án tốt nghiệp CNTT — Web thương mại điện tử chuyên bán linh kiện máy tính

---

## 📋 Giới thiệu

**PC Store** là ứng dụng web bán linh kiện máy tính được xây dựng bằng PHP thuần (không framework), MySQL và JavaScript vanilla. Hệ thống tích hợp đầy đủ các tính năng thương mại điện tử cơ bản cùng một số tính năng nâng cao như chatbot AI, quản lý serial number, hệ thống loyalty points.

### Tech Stack
| Thành phần | Công nghệ |
|---|---|
| Backend | PHP 8.0+ (thuần, không framework) |
| Database | MySQL 8.0 |
| Frontend | HTML5, CSS3, JavaScript (Vanilla + jQuery) |
| AI Chatbot | Groq API (LLaMA 3.3 70B) |
| Email | PHP mail() / SMTP |
| Web Server | Apache (Laragon) |

---

## 🚀 Cài đặt & Chạy

### Yêu cầu
- [Laragon](https://laragon.org/) (hoặc XAMPP/WAMP với PHP ≥ 8.0)
- MySQL 8.0+
- PHP Extensions: `pdo_mysql`, `mbstring`, `json`

### Bước 1 — Clone & Setup
```bash
# Clone vào thư mục web root của Laragon
cd C:\laragon\www
git clone <repo-url> Ban_linh_kien
cd Ban_linh_kien
```

### Bước 2 — Cấu hình môi trường
```bash
# Sao chép file .env mẫu
copy .env.example .env
```

Chỉnh sửa `.env`:
```env
DB_HOST=localhost
DB_NAME=db_ban_linh_kien
DB_USER=root
DB_PASS=your_password

APP_DEBUG=false

GROQ_API_KEY=your_groq_api_key
```

> ⚠️ **Quan trọng**: Không commit `.env` vào git! File này đã được liệt kê trong `.gitignore`.

### Bước 3 — Tạo Database
```bash
# Chạy migration qua CLI (không chạy qua URL)
php _scripts/run_migration.php
php _scripts/seed_products.php     # (tuỳ chọn) thêm dữ liệu mẫu
php _scripts/setup_admin.php       # tạo tài khoản admin
```

### Bước 4 — Truy cập
```
http://localhost/Ban_linh_kien/
http://localhost/Ban_linh_kien/admin/
```

---

## 📁 Cấu trúc thư mục

```
Ban_linh_kien/
├── admin/                  # Trang quản trị
│   ├── views/              # Views của admin
│   └── session_check.php   # Auth check riêng cho admin
├── app/                    # Application code
│   ├── controllers/        # Controllers xử lý logic
│   ├── helpers/            # Helper classes (CSRF, Logger, Rate Limit...)
│   ├── models/             # Database models
│   └── views/              # Frontend views & layouts
├── core/
│   └── Database.php        # PDO connection wrapper
├── public/                 # Static assets (CSS, JS, images)
│   ├── css/
│   ├── js/
│   └── img/
├── _scripts/               # Scripts chỉ chạy qua CLI (không qua URL)
│   ├── run_migration.php
│   ├── seed_products.php
│   └── *.sql
├── logs/                   # Application logs (ignored by git)
├── .env                    # Environment config (ignored by git)
├── .env.example            # Template cho .env
├── .htaccess               # Apache config: bảo mật + security headers
├── config.php              # Load .env, define constants
├── session_check.php       # Session + timeout management
├── router.php              # PHP built-in server router
└── index.php               # Trang chủ (entry point)
```

---

## ✨ Tính năng

### Khách hàng
- 🛍️ Xem, tìm kiếm, lọc sản phẩm
- 🛒 Giỏ hàng, thanh toán (COD / chuyển khoản)
- 📦 Theo dõi đơn hàng, mã vận đơn
- ⭐ Đánh giá & xếp hạng sản phẩm
- 💬 Chat hỗ trợ khách hàng real-time
- 🤖 Chatbot AI gợi ý cấu hình máy tính
- 🎁 Hệ thống tích điểm (Loyalty Points)
- 🔄 Đổi/trả hàng online
- 📱 So sánh sản phẩm

### Quản trị
- 📊 Dashboard thống kê doanh thu
- 📦 Quản lý sản phẩm, kho hàng
- 🔢 Theo dõi serial number từng sản phẩm
- 📋 Quản lý đơn hàng, vận chuyển
- 👥 Quản lý khách hàng (khoá/mở khoá)
- 💬 Quản lý chat & hỗ trợ
- 📈 Báo cáo SEO, audit log
- 📧 Email queue worker

---

## 🔒 Bảo mật

| Tính năng | Trạng thái |
|---|---|
| CSRF Protection | ✅ `CsrfHelper` — tất cả form POST |
| Rate Limiting | ✅ `RateLimiter` — login, admin, reset password |
| Session HttpOnly + SameSite | ✅ `session_check.php` |
| SQL Injection | ✅ PDO Prepared Statements |
| XSS | ✅ `htmlspecialchars()` toàn bộ output |
| Credential bảo mật | ✅ `.env` + `getenv()`, không hardcode |
| File protection | ✅ `.htaccess` chặn truy cập trực tiếp |
| Error reporting | ✅ Kiểm soát theo `APP_DEBUG` |
| Audit log | ✅ Mọi thao tác admin được ghi lại |

---

## 🌐 Deploy lên Production

### 1. Cấu hình web server (quan trọng)

Để tách biệt source code khỏi web root, cấu hình Apache document root trỏ vào `public/`:

```apache
# Trong Laragon: thêm vào C:\laragon\etc\apache2\sites-enabled\auto.Ban_linh_kien.conf
<VirtualHost *:80>
    ServerName ban-linh-kien.local
    DocumentRoot "C:/laragon/www/Ban_linh_kien/public"
    <Directory "C:/laragon/www/Ban_linh_kien/public">
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

> ⚠️ Hiện tại project chưa được restructure để chạy với document root = `public/`. Đây là bước tiếp theo khi refactor architecture.

### 2. Environment
```env
APP_DEBUG=false
```

### 3. Checklist trước khi deploy
- [ ] `APP_DEBUG=false` trong `.env`
- [ ] Đổi DB password mạnh
- [ ] Tạo Groq API key mới (thu hồi key cũ nếu đã lộ)
- [ ] Enable HTTPS — bỏ comment phần HTTPS trong `.htaccess`
- [ ] Kiểm tra `logs/` có quyền ghi

---

## 🛠️ Development

### Chạy với PHP built-in server
```bash
php -S localhost:8082 router.php
```

### Kiểm tra syntax
```bash
php -l config.php
php -l core/Database.php
```

### Chạy migration thủ công
```bash
php _scripts/run_migration.php
php _scripts/patch_migration.php
```

---

## 📝 Biến môi trường (.env)

| Biến | Mô tả | Bắt buộc |
|---|---|---|
| `DB_HOST` | Database host | ✅ |
| `DB_NAME` | Tên database | ✅ |
| `DB_USER` | Database username | ✅ |
| `DB_PASS` | Database password | ✅ |
| `APP_DEBUG` | Bật/tắt debug mode (`true`/`false`) | ✅ |
| `GROQ_API_KEY` | API key Groq cho chatbot AI | ⚠️ |

---

## 👨‍💻 Đóng góp

Đây là đồ án cá nhân. Mọi issue hoặc góp ý xin gửi qua GitHub Issues.

---

*© 2026 — Đồ án tốt nghiệp CNTT*
