# 💻 Hướng Dẫn Cài Đặt & Chạy Dự Án Ban Linh Kiện (PC Store)

---

## 📌 1. Giới thiệu dự án

- **Tên dự án:** Website Thương Mại Điện Tử Bán Linh Kiện Máy Tính (PC Store).
- **Kiến trúc:** PHP 8.0+ thuần (Custom MVC, chuẩn PSR-4), MySQL 8.0+, Apache/Nginx.
- **Frontend:** HTML5, CSS3, JavaScript, jQuery, Bootstrap.
- **Thư viện:** Đã tích hợp sẵn đầy đủ trong thư mục `vendor/` (không cần chạy Composer).
- **Cơ sở dữ liệu:** File database.sql đi kèm ngay tại thư mục gốc.

---

## 🔑 2. Tài khoản đăng nhập demo

### 👑 Quản trị viên (Admin)
- **Đường dẫn Admin:** http://localhost/Ban_linh_kien/admin/ (hoặc http://localhost:8082/admin/)
- **Tên đăng nhập (Username):** admin
- **Mật khẩu (Password):** admin123
- **Quyền:** Quản trị toàn bộ hệ thống (Sản phẩm, Kho hàng, Serial Number, Đơn hàng, Doanh thu, Khách hàng, Chatbot, Khuyến mãi...).

### 👤 Khách hàng / Thành viên demo
- **Tên đăng nhập:** test2 (hoặc email: tung5249m@gmail.com)
- **Mật khẩu:** 12345678
*(Bạn cũng có thể bấm Đăng ký tài khoản mới trực tiếp trên giao diện website).*

---

## 🚀 3. Hướng dẫn cài đặt & Khởi chạy

### Cách 1: Sử dụng Laragon (Khuyên dùng - Nhanh nhất)
1. Giải nén thư mục dự án Ban_linh_kien vào thư mục web:
   ```
   C:\laragon\www\Ban_linh_kien
   ```
2. Mở **Laragon** và nhấn nút **Start All**.
3. Nhập cơ sở dữ liệu:
   - Truy cập **phpMyAdmin** tại http://localhost/phpmyadmin/ (hoặc nhấn nút **Database** trên giao diện Laragon để mở HeidiSQL).
   - Chọn tab **Import** (Nhập) -> Chọn tệp database.sql tại thư mục dự án -> Nhấn **Import** (Thực hiện).
   - *(File database.sql đã thiết lập tự động tạo database db_ban_linh_kien).*
4. Cấu hình file .env:
   - Mở file .env tại thư mục gốc dự án.
   - Nếu MySQL Laragon của bạn không đặt mật khẩu (mặc định của Laragon):
     ```env
     DB_HOST=localhost
     DB_NAME=db_ban_linh_kien
     DB_USER=root
     DB_PASS=
     ```
   - Nếu bạn có đặt mật khẩu cho MySQL, hãy điền vào sau DB_PASS=.
5. Truy cập trình duyệt:
   - **Trang chủ:** http://localhost/Ban_linh_kien/ (hoặc http://Ban_linh_kien.test/)
   - **Trang quản trị:** http://localhost/Ban_linh_kien/admin/

---

### Cách 2: Sử dụng XAMPP
1. Giải nén thư mục Ban_linh_kien vào thư mục:
   ```
   C:\xampp\htdocs\Ban_linh_kien
   ```
2. Mở **XAMPP Control Panel** -> Nhấn **Start** cho cả **Apache** và **MySQL**.
3. Truy cập http://localhost/phpmyadmin/ -> Tab **Import** -> Chọn file database.sql -> Bấm **Go**.
4. Kiểm tra file .env: đảm bảo DB_USER=root, DB_PASS= (để trống mật khẩu).
5. Truy cập:
   - **Trang chủ:** http://localhost/Ban_linh_kien/
   - **Trang Admin:** http://localhost/Ban_linh_kien/admin/

---

### Cách 3: Chạy nhanh bằng lệnh PHP Built-in Server (Không cần Apache)
1. Đảm bảo MySQL đang chạy và đã import file database.sql.
2. Mở PowerShell hoặc Terminal tại thư mục Ban_linh_kien.
3. Chạy lệnh:
   ```bash
   php -S localhost:8082 router.php
   ```
4. Truy cập trên trình duyệt:
   - **Trang chủ:** http://localhost:8082/
   - **Trang Admin:** http://localhost:8082/admin/

---

## 🛠️ 4. Xử lý sự cố thường gặp (Troubleshooting)

- **Lỗi kết nối cơ sở dữ liệu (Connection failed / SQLSTATE[HY000] [1045]):**
  - Mở file .env và kiểm tra lại DB_USER và DB_PASS xem đã khớp với cấu hình MySQL trên máy bạn chưa.
- **Lỗi 404 khi truy cập các trang con:**
  - Hãy kiểm tra xem Apache đã bật module mod_rewrite và cho phép .htaccess hoạt động (AllowOverride All) chưa (trên Laragon đã bật sẵn).
- **Yêu cầu phiên bản:**
  - PHP >= 8.0 với các extension: pdo_mysql, mbstring, curl, openssl.

---
*Chúc bạn trải nghiệm dự án thành công!*
