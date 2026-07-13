<?php
require_once 'session_check.php';
require_once 'config.php';
include 'app/views/header.php';
?>
<div class="container" style="max-width:860px;margin:40px auto 60px;">
    <div style="background:white;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,.06);overflow:hidden;">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,#1e293b,#334155);padding:36px 40px;color:white;">
            <h1 style="margin:0 0 6px;font-size:26px;font-weight:800;">📋 Điều khoản sử dụng dịch vụ</h1>
            <p style="margin:0;font-size:14px;opacity:.7;">Cập nhật lần cuối: 06/07/2026 — Áp dụng cho toàn bộ dịch vụ của Ban Linh Kiện</p>
        </div>

        <!-- Mục lục nhanh -->
        <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:20px 40px;">
            <p style="margin:0 0 10px;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Mục lục</p>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach([
                    ['#dieu1','1. Chấp nhận điều khoản'],
                    ['#dieu2','2. Tài khoản người dùng'],
                    ['#dieu3','3. Đặt hàng & Thanh toán'],
                    ['#dieu4','4. Giao hàng & Vận chuyển'],
                    ['#dieu5','5. Đổi trả & Hoàn tiền'],
                    ['#dieu6','6. Quyền sở hữu trí tuệ'],
                    ['#dieu7','7. Giới hạn trách nhiệm'],
                    ['#dieu8','8. Giải quyết tranh chấp'],
                    ['#dieu9','9. Thay đổi điều khoản'],
                    ['#dieu10','10. Liên hệ'],
                ] as [$href,$label]): ?>
                <a href="<?php echo $href; ?>" style="font-size:12px;color:#2563eb;text-decoration:none;background:#eff6ff;padding:5px 12px;border-radius:20px;white-space:nowrap;"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="padding:36px 40px;line-height:1.9;font-size:14.5px;color:#334155;">

            <!-- Lời mở đầu -->
            <div style="background:#fefce8;border:1px solid #fde68a;border-radius:12px;padding:16px 20px;margin-bottom:28px;">
                <p style="margin:0;font-size:13.5px;color:#92400e;">
                    ⚠️ Vui lòng đọc kỹ các điều khoản này trước khi sử dụng dịch vụ của chúng tôi.
                    Bằng việc truy cập và sử dụng website <strong>Ban Linh Kiện</strong>, bạn xác nhận đã đọc, hiểu và đồng ý bị ràng buộc bởi các điều khoản dưới đây.
                </p>
            </div>

            <!-- Điều 1 -->
            <h2 id="dieu1" style="font-size:18px;font-weight:700;color:#1e293b;margin:0 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                📌 1. Chấp nhận điều khoản
            </h2>
            <p><strong>1.1.</strong> Khi truy cập và sử dụng website <strong>Ban Linh Kiện</strong> (sau đây gọi là "Dịch vụ"), bạn đồng ý tuân thủ và bị ràng buộc bởi các Điều khoản sử dụng này và tất cả luật pháp, quy định hiện hành của Việt Nam.</p>
            <p><strong>1.2.</strong> Nếu bạn không đồng ý với bất kỳ điều khoản nào, vui lòng ngừng sử dụng Dịch vụ ngay lập tức.</p>
            <p><strong>1.3.</strong> Người dùng phải đủ 18 tuổi hoặc có sự đồng ý của cha mẹ/người giám hộ hợp pháp để sử dụng dịch vụ có trả phí.</p>

            <!-- Điều 2 -->
            <h2 id="dieu2" style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                👤 2. Tài khoản người dùng
            </h2>
            <p><strong>2.1.</strong> Khi đăng ký tài khoản, bạn cam kết cung cấp thông tin chính xác, đầy đủ và cập nhật kịp thời khi có thay đổi.</p>
            <p><strong>2.2.</strong> Tài khoản là sở hữu cá nhân — <strong>không được chuyển nhượng, chia sẻ hay cho người khác sử dụng</strong>.</p>
            <p><strong>2.3.</strong> Bạn chịu hoàn toàn trách nhiệm về bảo mật mật khẩu và mọi hoạt động xảy ra dưới tài khoản của mình.</p>
            <p><strong>2.4.</strong> Ban Linh Kiện có quyền khóa tài khoản không cần báo trước nếu phát hiện vi phạm điều khoản, gian lận hoặc hành vi gây hại.</p>
            <p><strong>2.5.</strong> Để bảo mật tài khoản, sử dụng mật khẩu mạnh (ít nhất 8 ký tự, kết hợp chữ và số) và đăng xuất sau khi sử dụng trên thiết bị chung.</p>

            <!-- Điều 3 -->
            <h2 id="dieu3" style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                🛒 3. Đặt hàng & Thanh toán
            </h2>
            <p><strong>3.1.</strong> Đơn hàng được xác nhận sau khi chúng tôi gửi email/thông báo xác nhận. Giá hiển thị trên website là giá cuối cùng (đã bao gồm VAT nếu có áp dụng).</p>
            <p><strong>3.2.</strong> Ban Linh Kiện có quyền từ chối hoặc hủy đơn hàng trong các trường hợp: sản phẩm hết hàng, giá hiển thị sai do lỗi kỹ thuật, hoặc phát hiện hành vi gian lận.</p>
            <p><strong>3.3.</strong> Phương thức thanh toán hiện tại: COD (thanh toán khi nhận hàng) và chuyển khoản ngân hàng. Đơn COD sẽ được xác nhận qua điện thoại trong 24 giờ.</p>
            <p><strong>3.4.</strong> Mã giảm giá (voucher) chỉ áp dụng một lần mỗi tài khoản, không có giá trị quy đổi thành tiền mặt và không thể kết hợp với các khuyến mãi khác (trừ khi có ghi chú riêng).</p>

            <!-- Điều 4 -->
            <h2 id="dieu4" style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                🚚 4. Giao hàng & Vận chuyển
            </h2>
            <p><strong>4.1.</strong> Thời gian xử lý đơn hàng: 1–3 ngày làm việc kể từ khi xác nhận. Thời gian vận chuyển phụ thuộc vào đơn vị giao hàng và khu vực (thường 2–5 ngày làm việc).</p>
            <p><strong>4.2.</strong> Phí vận chuyển được tính dựa trên địa chỉ giao hàng và trọng lượng sản phẩm, hiển thị rõ ràng trước khi bạn xác nhận đặt hàng.</p>
            <p><strong>4.3.</strong> Rủi ro về hư hỏng, mất mát trong quá trình vận chuyển thuộc trách nhiệm của đơn vị vận chuyển. Chúng tôi hỗ trợ khiếu nại với đơn vị vận chuyển trong trường hợp này.</p>
            <p><strong>4.4.</strong> Vui lòng kiểm tra kỹ hàng hóa trước khi ký nhận. Sau khi ký nhận, không áp dụng khiếu nại về hư hỏng vận chuyển.</p>

            <!-- Điều 5 -->
            <h2 id="dieu5" style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                🔄 5. Đổi trả & Hoàn tiền
            </h2>
            <p><strong>5.1.</strong> Hỗ trợ đổi trả trong vòng <strong>7 ngày</strong> kể từ ngày nhận hàng với điều kiện: sản phẩm lỗi do nhà sản xuất, giao nhầm sản phẩm, hoặc còn nguyên vẹn, đầy đủ phụ kiện.</p>
            <p><strong>5.2.</strong> Không áp dụng đổi trả cho: sản phẩm phần mềm/key bản quyền đã kích hoạt, sản phẩm có dấu hiệu đã qua sử dụng hoặc cố ý làm hỏng.</p>
            <p><strong>5.3.</strong> Thời gian hoàn tiền (nếu áp dụng): 5–7 ngày làm việc kể từ khi xác nhận đổi trả thành công, tùy phương thức thanh toán ban đầu.</p>
            <p><strong>5.4.</strong> Chi phí vận chuyển đổi trả: khách hàng chịu phí ship chiều về nếu lỗi do người dùng; Ban Linh Kiện chịu chi phí nếu lỗi do chúng tôi.</p>

            <!-- Điều 6 -->
            <h2 id="dieu6" style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                ©️ 6. Quyền sở hữu trí tuệ
            </h2>
            <p><strong>6.1.</strong> Toàn bộ nội dung trên website (logo, hình ảnh, mô tả sản phẩm, giao diện) là tài sản của Ban Linh Kiện hoặc được cấp phép hợp pháp, được bảo hộ bởi luật sở hữu trí tuệ Việt Nam.</p>
            <p><strong>6.2.</strong> Nghiêm cấm sao chép, phân phối, chỉnh sửa hoặc sử dụng nội dung vì mục đích thương mại mà không có sự cho phép bằng văn bản.</p>
            <p><strong>6.3.</strong> Nội dung do người dùng đăng (đánh giá, bình luận): bạn giữ quyền sở hữu nhưng cấp cho chúng tôi giấy phép sử dụng miễn phí, không độc quyền để hiển thị và quảng bá.</p>

            <!-- Điều 7 -->
            <h2 id="dieu7" style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                ⚖️ 7. Giới hạn trách nhiệm
            </h2>
            <p><strong>7.1.</strong> Ban Linh Kiện không chịu trách nhiệm về các thiệt hại gián tiếp, ngẫu nhiên hoặc hậu quả phát sinh từ việc sử dụng hoặc không thể sử dụng Dịch vụ.</p>
            <p><strong>7.2.</strong> Chúng tôi không đảm bảo rằng Dịch vụ sẽ hoạt động liên tục, không có lỗi. Website có thể bị gián đoạn để bảo trì hoặc do sự cố kỹ thuật.</p>
            <p><strong>7.3.</strong> Trách nhiệm tối đa của chúng tôi không vượt quá số tiền bạn đã thanh toán cho đơn hàng liên quan đến tranh chấp.</p>

            <!-- Điều 8 -->
            <h2 id="dieu8" style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                🏛️ 8. Giải quyết tranh chấp
            </h2>
            <p><strong>8.1.</strong> Các tranh chấp phát sinh sẽ được ưu tiên giải quyết thông qua thương lượng trực tiếp. Liên hệ bộ phận CSKH trước khi khởi kiện.</p>
            <p><strong>8.2.</strong> Nếu thương lượng không thành, tranh chấp sẽ được giải quyết tại Tòa án nhân dân có thẩm quyền tại Việt Nam theo quy định pháp luật hiện hành.</p>
            <p><strong>8.3.</strong> Các điều khoản này được điều chỉnh và giải thích theo pháp luật nước Cộng hòa Xã hội Chủ nghĩa Việt Nam.</p>

            <!-- Điều 9 -->
            <h2 id="dieu9" style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                🔔 9. Thay đổi điều khoản
            </h2>
            <p><strong>9.1.</strong> Ban Linh Kiện có quyền cập nhật Điều khoản sử dụng bất cứ lúc nào. Thay đổi sẽ có hiệu lực ngay khi được đăng tải trên website.</p>
            <p><strong>9.2.</strong> Chúng tôi sẽ thông báo qua email đối với các thay đổi quan trọng ảnh hưởng đến quyền lợi của bạn.</p>
            <p><strong>9.3.</strong> Việc tiếp tục sử dụng Dịch vụ sau khi điều khoản được cập nhật đồng nghĩa với việc bạn chấp nhận điều khoản mới.</p>

            <!-- Điều 10 -->
            <h2 id="dieu10" style="font-size:18px;font-weight:700;color:#1e293b;margin:28px 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
                📞 10. Liên hệ
            </h2>
            <p>Mọi thắc mắc về Điều khoản sử dụng, vui lòng liên hệ:</p>
            <div style="background:#f8fafc;border-radius:14px;padding:20px 24px;border:1px solid #e2e8f0;">
                <p style="margin:0 0 8px;">📧 <strong>Email:</strong> support@pcstore.vn</p>
                <p style="margin:0 0 8px;">📞 <strong>Hotline:</strong> 1900 100x (8:00–21:00 mỗi ngày)</p>
                <p style="margin:0 0 8px;">🏠 <strong>Địa chỉ:</strong> 123 Đường ABC, Hà Nội</p>
                <p style="margin:0;">🕐 <strong>Giờ làm việc:</strong> Thứ 2 – Thứ 7, 8:00 – 18:00</p>
            </div>

            <!-- Nút -->
            <div style="display:flex;gap:12px;margin-top:32px;flex-wrap:wrap;">
                <a href="<?php echo BASE_URL; ?>chinh_sach.php" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#f1f5f9;color:#334155;border-radius:12px;text-decoration:none;font-weight:600;font-size:14px;">
                    🔒 Chính sách bảo mật
                </a>
                <a href="<?php echo BASE_URL; ?>index.php" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;border-radius:12px;text-decoration:none;font-weight:600;font-size:14px;">
                    🏠 Về trang chủ
                </a>
            </div>
        </div>
    </div>
</div>
<?php include 'app/views/footer.php'; ?>
