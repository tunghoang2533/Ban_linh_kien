cat > GEMINI.md << 'EOF'

\# GEMINI.md — Global Rules cho Antigravity



\## Project: Ban Linh Kiện

\- Stack: PHP 8.0+ thuần, MySQL, jQuery, PDO

\- Architecture: Custom MVC với PSR-4 autoloader

\- Namespace: App\\Controllers, App\\Models, App\\Helpers, App\\Core



\## Cấu trúc thư mục

\- app/controllers/ — Controllers

\- app/models/ — Models

\- app/helpers/ — Helpers

\- core/ — Core classes (Database)

\- admin/ — Admin panel

\- public/ — Static assets

\- Root PHP files — Entry points



\## Quy tắc coding BẮT BUỘC

1\. Luôn dùng PDO prepared statements — KHÔNG nối chuỗi SQL

2\. Luôn dùng BASE\_URL cho redirect — KHÔNG dùng relative path

3\. Luôn thêm exit() sau header("Location:...")

4\. Luôn verify CSRF trước khi xử lý POST

5\. Luôn check isset() trước khi truy cập $\_SESSION, $\_GET, $\_POST

6\. Luôn dùng htmlspecialchars() khi output user data ra HTML

7\. Luôn dùng try-catch khi gọi helper có thể throw Exception



\## Key Config

\- DB: .env qua config.php

\- Session: session\_check.php (timeout 8h)

\- Router: router.php (PHP built-in server)

\- VNPAY: .env (VNPAY\_TMN\_CODE, VNPAY\_HASH\_SECRET, VNPAY\_URL, VNPAY\_RETURN\_URL)

\- VNPAY TxnRef: "{orderId}\_{timestamp}" — parse bằng explode('\_', $txnRef)\[0]



\## Definition of Done

\- Fix xong KHÔNG sinh lỗi mới

\- Giữ backward-compatible

\- Không thay đổi DB schema nếu không cần

\- Guest checkout phải hoạt động

\- Test: php -S localhost:8082 router.php

EOF

