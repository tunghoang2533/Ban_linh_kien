<?php
require_once 'session_check.php';
require_once 'config.php';
include 'app/views/header.php';
?>
<div class="container" style="max-width:860px;margin:40px auto 60px;">
    <div style="background:white;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,.06);overflow:hidden;">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,#1e293b,#334155);padding:36px 40px;color:white;">
            <h1 style="margin:0 0 6px;font-size:26px;font-weight:800;">📜 Điều khoản sử dụng & Chính sách bảo mật</h1>
            <p style="margin:0;font-size:14px;opacity:.7;">Cập nhật lần cuối: 06/07/2026</p>
        </div>

        <div style="padding:36px 40px;line-height:1.85;font-size:14.5px;color:#334155;">

            <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">1. Điều khoản sử dụng</h2>
            <p><strong>1.1.</strong> Khi sử dụng website <strong>Ban Linh Kiện</strong> (banlinh.vn), bạn đồng ý tuân thủ tất cả các điều khoản dưới đây. Nếu không đồng ý, vui lòng không sử dụng dịch vụ.</p>
            <p><strong>1.2.</strong> Tài khoản đăng ký là sở hữu cá nhân. Người dùng chịu trách nhiệm bảo mật mật khẩu và mọi hoạt động dưới tài khoản của mình.</p>
            <p><strong>1.3.</strong> Nghiêm cấm sử dụng website cho các mục đích: gian lận, lệnh phần mềm độc hại, spam, hoặc bất kỳ hành vi vi phạm pháp luật Việt Nam.</p>
            <p><strong>1.4.</strong> Ban Linh Kiện có quyền khóa tài khoản hoặc hủy đơn hàng nếu phát hiện hành vi vi phạm mà không cần thông báo trước.</p>

            <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">2. Chính sách bảo mật thông tin</h2>
            <p><strong>2.1.</strong> Thông tin thu thập: Họ tên, email, số điện thoại, địa chỉ giao hàng — <em>chỉ nhằm mục đích xử lý đơn hàng và hỗ trợ khách hàng</em>.</p>
            <p><strong>2.2.</strong> Chúng tôi <strong>không bán, cho thuê, chia sẻ</strong> thông tin cá nhân của bạn cho bên thứ ba, trừ khi có yêu cầu từ cơ quan pháp luật.</p>
            <p><strong>2.3.</strong> Mật khẩu được mã hóa bằng thuật toán bcrypt (one-way hash) — ngay cả quản trị viên cũng không thể xem mật khẩu gốc.</p>
            <p><strong>2.4.</strong> Cookie: Website sử dụng cookie phiên (session) để duy trì trạng thái đăng nhập. Cookie được thiết lập HttpOnly, SameSite=Lax để chống XSS.</p>

            <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">3. Chính sách giao hàng & Đổi trả</h2>
            <p><strong>3.1.</strong> Thời gian xử lý đơn hàng: 1–3 ngày làm việc. Thời gian vận chuyển tùy thuộc vào đơn vị giao hàng và khu vực.</p>
            <p><strong>3.2.</strong> Đổi trả trong vòng <strong>7 ngày</strong> kể từ ngày nhận hàng, áp dụng cho sản phẩm lỗi do nhà sản xuất hoặc sai giao hàng.</p>
            <p><strong>3.3.</strong> Sản phẩm đổi trả phải còn nguyên hộp, tem mác, phụ kiện kèm theo. Không áp dụng cho sản phẩm phần mềm đã kích hoạt.</p>

            <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">4. Chính sách thanh toán</h2>
            <p><strong>4.1.</strong> Phương thức thanh toán: COD (nhận hàng trả tiền), chuyển khoản ngân hàng.</p>
            <p><strong>4.2.</strong> Đơn hàng COD sẽ được xác nhận qua điện thoại trước khi giao. Vui lòng giữ liên lạc trong 24 giờ sau khi đặt hàng.</p>

            <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">5. Liên hệ</h2>
            <div style="background:#f8fafc;border-radius:14px;padding:20px 24px;border:1px solid #e2e8f0;">
                <p style="margin:0 0 8px;">📧 <strong>Email:</strong> support@pcstore.vn</p>
                <p style="margin:0 0 8px;">📞 <strong>Hotline:</strong> 1800 6975</p>
                <p style="margin:0;">📍 <strong>Địa chỉ:</strong> 123 Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh</p>
            </div>
        </div>
    </div>
</div>
<?php include 'app/views/footer.php'; ?>
