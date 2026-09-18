-- 1. Thêm giá trị 'vnpay' vào enum payment_method của bảng orders:
ALTER TABLE orders MODIFY COLUMN payment_method enum('cod','bank','vnpay') NOT NULL DEFAULT 'cod';

-- 2. Thêm cột vnpay_transaction vào bảng orders (sau cột tracking_code):
ALTER TABLE orders ADD COLUMN vnpay_transaction varchar(100) DEFAULT NULL AFTER tracking_code;

-- 3. Thêm cột socket vào bảng products (sau cột bin_location):
ALTER TABLE products ADD COLUMN socket varchar(50) DEFAULT NULL AFTER bin_location;

-- 4. Xóa cột fullname thừa trong bảng users (đã dùng full_name):
ALTER TABLE users DROP COLUMN fullname;
