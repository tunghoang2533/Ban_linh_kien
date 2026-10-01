# 🚀 HƯỚNG DẪN KIỂM THỬ DỰ ÁN "BÁN LINH KIỆN" BẰNG KATALON STUDIO

Dự án kiểm thử tự động hóa giao diện (E2E Web UI Automation Testing) được xây dựng hoàn chỉnh và sẵn sàng để nhập (import) trực tiếp vào **Katalon Studio**.

---

## 📁 1. Cấu Trúc Thư Mục Kiểm Thử (katalon_tests/)

```text
katalon_tests/
├── BanLinhKien_KatalonTest.prj       # File cấu hình Project Katalon
├── .project                          # File Eclipse Project Descriptor
├── Profiles/
│   └── default.glbl                  # Cấu hình biến toàn cục (G_SiteURL, G_Timeout, ...)
├── Object Repository/                # Kho lưu trữ các phần tử Web (Web Test Objects)
│   ├── Page_Auth/                    # Các ô nhập, nút bấm trang Đăng nhập / Đăng ký
│   ├── Page_Home/                    # Thanh tìm kiếm trên Header
│   ├── Page_ProductDetail/           # Nút Thêm vào giỏ, tên sản phẩm
│   ├── Page_Cart/                    # Tiêu đề giỏ hàng, bảng giỏ hàng
│   └── Page_Contact/                 # Form liên hệ hỗ trợ trực tuyến
├── Test Cases/                       # Định nghĩa các kịch bản kiểm thử
│   ├── TC01_DangKyTaiKhoan.tc
│   ├── TC02_DangNhapThanhCong.tc
│   ├── TC03_DangNhapSaiMatKhau.tc
│   ├── TC04_TimKiemSanPham.tc
│   ├── TC05_ThemVaoGioHang.tc
│   └── TC06_GuiLienHe.tc
├── Scripts/                          # Mã nguồn kịch bản kiểm thử (Groovy Scripts)
│   ├── TC01_DangKyTaiKhoan/
│   ├── TC02_DangNhapThanhCong/
│   ├── TC03_DangNhapSaiMatKhau/
│   ├── TC04_TimKiemSanPham/
│   ├── TC05_ThemVaoGioHang/
│   └── TC06_GuiLienHe/
└── Test Suites/
    └── TS_Regression_BanLinhKien.ts  # Bộ Test Suite chạy hồi quy toàn diện
```

---

## 🛠️ 2. Chuẩn Bị Môi Trường

1. **Khởi động Web Server & Database**:
   - Nếu bạn dùng **Laragon**: Mở Laragon nhấn **Start All** (Apache & MySQL). Website thường chạy ở:
     `http://localhost/Ban_linh_kien/`
   - Hoặc nếu chạy bằng lệnh PHP built-in server:
     ```bash
     php -S localhost:8082 router.php
     ```
     Khi đó đường dẫn là `http://localhost:8082/` hoặc `http://localhost:8082/Ban_linh_kien/`.

2. **Cài đặt Katalon Studio**:
   - Tải Katalon Studio miễn phí từ trang chủ: [https://katalon.com/download](https://katalon.com/download).
   - Giải nén và mở ứng dụng `katalon.exe`.

---

## ⚡ 3. Cách Mở & Cấu Hình Project trong Katalon Studio

1. Mở **Katalon Studio**.
2. Trên thanh menu, chọn: **File** > **Open Project...**
3. Điều hướng và chọn file:
   `c:\laragon\www\Ban_linh_kien\katalon_tests\BanLinhKien_KatalonTest.prj`
4. **Kiểm tra biến môi trường Global Variable**:
   - Trong cây thư mục bên trái (Tests Explorer), mở: **Profiles** > **default**.
   - Xem giá trị của `G_SiteURL`: Mặc định là `'http://localhost/Ban_linh_kien/'`.
   - Nếu web của bạn đang chạy ở port khác (ví dụ: `http://localhost:8082/`), chỉ cần sửa giá trị này rồi nhấn **Ctrl + S** để lưu. Mọi Test Case sẽ tự động lấy đúng URL mới!

---

## 🧪 4. Danh Sách Các Kịch Bản Kiểm Thử (Test Cases)

| Mã TC | Tên Kịch Bản | Loại Kiểm Thử | Tóm Tắt Luồng Kiểm Thử |
|---|---|---|---|
| **TC01** | `TC01_DangKyTaiKhoan` | Positive Test | Chuyển sang tab Đăng ký, sinh ngẫu nhiên tài khoản hợp lệ, điền form đăng ký, gửi form và kiểm tra thông báo *"Đăng ký thành công! Hãy đăng nhập."* |
| **TC02** | `TC02_DangNhapThanhCong` | Positive Test | Tạo tài khoản độc lập, đăng nhập với thông tin vừa tạo, kiểm tra hệ thống xác thực thành công và hiển thị giao diện người dùng. |
| **TC03** | `TC03_DangNhapSaiMatKhau` | Negative Test | Nhập tài khoản và mật khẩu sai, nhấn Đăng nhập, xác minh hệ thống hiển thị thông báo lỗi *"Sai tài khoản hoặc mật khẩu"*. |
| **TC04** | `TC04_TimKiemSanPham` | Functional Test | Nhập từ khóa (vd: *"Ryzen"*) vào thanh tìm kiếm ở Header, gửi form tìm kiếm, xác nhận URL chuyển tới `search.php?key=Ryzen` và hiển thị kết quả sản phẩm phù hợp. |
| **TC05** | `TC05_ThemVaoGioHang` | E2E Cart Test | Truy cập chi tiết sản phẩm, nhấn nút *"THÊM VÀO GIỎ"*, chuyển hướng tới `giohang.php` và xác minh sản phẩm hiển thị trong bảng giỏ hàng. |
| **TC06** | `TC06_GuiLienHe` | Functional Test | Truy cập `lienhe.php`, điền đầy đủ các trường họ tên, email, SĐT, chủ đề và tin nhắn, nhấn Gửi và xác nhận thông báo phản hồi thành công. |

---

## ▶️ 5. Hướng Dẫn Chạy Kiểm Thử

### Cách 1: Chạy từng Test Case riêng lẻ
1. Trong cửa sổ **Tests Explorer**, nhấp đúp vào một Test Case bất kỳ (ví dụ: `Test Cases/TC01_DangKyTaiKhoan`).
2. Nhấp vào nút **Run** (biểu tượng tam giác xanh ▶️ trên thanh công cụ) và chọn trình duyệt muốn chạy (ví dụ: **Chrome**, **Edge**, hoặc **Firefox**).
3. Katalon sẽ tự động bật trình duyệt, thực thi các bước click/input/verify và tự động đóng trình duyệt khi hoàn thành.
4. Xem kết quả tại tab **Log Viewer** ở góc dưới màn hình.

### Cách 2: Chạy toàn bộ bộ kiểm thử (Test Suite)
1. Trong cửa sổ **Tests Explorer**, mở thư mục **Test Suites**.
2. Nhấp đúp vào `TS_Regression_BanLinhKien`.
3. Nhấp nút **Run** ▶️ ở góc trên bên phải.
4. Katalon Studio sẽ tuần tự thực thi cả 6 Test Case từ đầu đến cuối và tự động tổng hợp kết quả (Passed/Failed).

---

## 📊 6. Xem Báo Cáo Kết Quả Kiểm Thử (Reports)

1. Sau khi chạy Test Suite xong, trong mục **Reports** ở menu bên trái sẽ tự động tạo thư mục log theo ngày giờ chạy.
2. Nhấp đúp vào bản ghi report để xem:
   - Tổng số test case Pass/Fail.
   - Thời gian thực thi từng bước (Execution Time).
   - Log chi tiết từng câu lệnh WebUI.
3. Xuất báo cáo: Nhấn vào các nút xuất báo cáo ở góc trên bên phải của trang Report:
   - **Export as HTML**: Báo cáo giao diện web trực quan, đẹp mắt để nộp đồ án hoặc báo cáo kiểm thử.
   - **Export as PDF**: Báo cáo dạng tài liệu PDF.
   - **Export as CSV / Excel**: Báo cáo số liệu dạng bảng tính.

---

## 💡 7. Mẹo Tạo Thêm Kịch Bản Mới Bằng Katalon (Record & Spy)

- **Record Web**: Nhấn vào biểu tượng quả cầu Record Web trên thanh công cụ > Nhập URL website > Click **Start** > Thao tác trên web như người dùng thật > Katalon sẽ tự động sinh Test Objects và mã lệnh!
- **Spy Web**: Dùng để chụp và lấy XPath / CSS selector của bất kỳ phần tử nào trên giao diện nếu giao diện có thay đổi trong tương lai.
