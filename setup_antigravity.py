#!/usr/bin/env python3
"""Script tạo 2 file .antigravityignore và GEMINI.md cho project Ban Linh Kien"""
import os

# ── .antigravityignore ──
antigravity_content = """\
vendor/
cache/
public/img/
*.png
*.jpg
*.jpeg
*.gif
*.webp
*.ico
*.svg
*.min.css
*.min.js
logs/
*.log
php_server.log
_scripts/
.git/
*.cache
*.cache.expires
composer.lock
package-lock.json
"""

# ── GEMINI.md ──
gemini_content = """\
# GEMINI.md — Global Rules cho Antigravity

## Project: Ban Linh Kiện
- Stack: PHP 8.0+ thuần, MySQL, jQuery, PDO
- Architecture: Custom MVC với PSR-4 autoloader
- Namespace: App\\Controllers, App\\Models, App\\Helpers, App\\Core

## Cấu trúc thư mục
- app/controllers/ — Controllers
- app/models/ — Models
- app/helpers/ — Helpers
- core/ — Core classes (Database)
- admin/ — Admin panel
- public/ — Static assets
- Root PHP files — Entry points

## Quy tắc coding BẮT BUỘC
1. Luôn dùng PDO prepared statements — KHÔNG nối chuỗi SQL
2. Luôn dùng BASE_URL cho redirect — KHÔNG dùng relative path
3. Luôn thêm exit() sau header("Location:...")
4. Luôn verify CSRF trước khi xử lý POST
5. Luôn check isset() trước khi truy cập $_SESSION, $_GET, $_POST
6. Luôn dùng htmlspecialchars() khi output user data ra HTML
7. Luôn dùng try-catch khi gọi helper có thể throw Exception

## Key Config
- DB: .env qua config.php
- Session: session_check.php (timeout 8h)
- Router: router.php (PHP built-in server)
- VNPAY: .env (VNPAY_TMN_CODE, VNPAY_HASH_SECRET, VNPAY_URL, VNPAY_RETURN_URL)
- VNPAY TxnRef: "{orderId}_{timestamp}" — parse bằng explode('_', $txnRef)[0]

## Definition of Done
- Fix xong KHÔNG sinh lỗi mới
- Giữ backward-compatible
- Không thay đổi DB schema nếu không cần
- Guest checkout phải hoạt động
- Test: php -S localhost:8082 router.php
"""

# Ghi file
with open('.antigravityignore', 'w', encoding='utf-8', newline='\n') as f:
    f.write(antigravity_content)

with open('GEMINI.md', 'w', encoding='utf-8', newline='\n') as f:
    f.write(gemini_content)

print("✅ Đã tạo thành công:")
print("   - .antigravityignore")
print("   - GEMINI.md")
print()
print("Chạy tiếp các lệnh sau để push lên GitHub:")
print("   git add .antigravityignore GEMINI.md")
print("   git commit -m \"fix: correct antigravity config files\"")
print("   git push")
