# Trọn bộ 22 Sơ đồ Thiết kế Hệ thống (Định dạng PlantUML)
### Dự án: Website Thương Mại Điện Tử Bán Linh Kiện Máy Tính

Toàn bộ các sơ đồ trong thư mục này được xây dựng chuẩn theo ngôn ngữ **PlantUML** với cấu hình:
- `skinparam linetype ortho`: Tự động uốn các đường liên kết thành các góc vuông $90^\circ$, **tuyệt đối không bị cắt chéo hay rối dây**.
- `left to right direction`: Tối ưu bố cục trải rộng theo chiều ngang, không bị kéo dọc.
- Bảng thực thể phân định rõ ràng Khóa chính (`<<PK>>`), Khóa ngoại (`<<FK>>`), và kiểu dữ liệu.

---

## 🚀 Hướng dẫn mở và xuất ảnh cực đẹp

### Cách 1: Mở trên PlantText (Khuyên dùng - Nhanh & Đẹp nhất)
1. Truy cập trang web: **[https://www.planttext.com](https://www.planttext.com)**
2. Xóa sạch đoạn văn bản mẫu ở khung bên trái.
3. Mở file `.puml` tương ứng bên dưới, nhấn `Ctrl + A` rồi `Ctrl + C` để copy toàn bộ nội dung (từ `@startuml` đến `@enduml`).
4. Dán vào khung soạn thảo PlantText và nhấn **Refresh** (hoặc `Ctrl + Enter`).
5. Bấm nút **Export PNG** hoặc **Export SVG** để tải ảnh sắc nét chất lượng cao chèn vào Word/Báo cáo đồ án.

---

### Cách 2: Mở trong Draw.io (Để chỉnh sửa/kéo thả tùy ý)
1. Mở **Draw.io** ([app.diagrams.net](https://app.diagrams.net)).
2. Vào menu: **Sắp xếp** $\rightarrow$ **Chèn** $\rightarrow$ **Nâng cao** $\rightarrow$ chọn **`PlantUML...`**
3. Dán toàn bộ mã PlantUML vào ô nhập liệu $\rightarrow$ bấm nút **Chèn (Insert)**.
4. Draw.io sẽ tự động chuyển đổi thành các khối vẽ vector để bạn tùy ý đổi màu hoặc kéo thả.

---

## 📑 Danh mục 22 Sơ đồ chi tiết

| STT | Tên Sơ Đồ | Thể loại | File PlantUML (`.puml`) |
| :---: | :--- | :--- | :--- |
| **01** | **Sơ đồ ERD – Nhóm Sản phẩm & Danh mục** | CSDL / ERD | [01_ERD_SanPham_DanhMuc.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/01_ERD_SanPham_DanhMuc.puml) |
| **02** | **Sơ đồ ERD – Nhóm Giỏ hàng, Đơn hàng & Thanh toán** | CSDL / ERD | [02_ERD_GioHang_DonHang_ThanhToan.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/02_ERD_GioHang_DonHang_ThanhToan.puml) |
| **03** | **Sơ đồ ERD – Nhóm Marketing** | CSDL / ERD | [03_ERD_Marketing.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/03_ERD_Marketing.puml) |
| **04** | **Sơ đồ Sequence – Thêm cấu hình Build PC vào giỏ hàng** | Sequence | [04_Sequence_Them_BuildPC_VaoGioHang.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/04_Sequence_Them_BuildPC_VaoGioHang.puml) |
| **05** | **Sơ đồ Sequence – Đặt hàng & Thanh toán VNPay** | Sequence | [05_Sequence_DatHang_ThanhToan_VNPay.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/05_Sequence_DatHang_ThanhToan_VNPay.puml) |
| **06** | **Sơ đồ Sequence – Chatbot AI tư vấn cấu hình (Groq LLaMA 3.3)** | Sequence | [06_Sequence_Chatbot_AI_TuVan.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/06_Sequence_Chatbot_AI_TuVan.puml) |
| **07** | **Sơ đồ Activity – Quy trình xử lý đơn hàng (end-to-end)** | Activity | [07_Activity_QuyTrinh_XuLy_DonHang.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/07_Activity_QuyTrinh_XuLy_DonHang.puml) |
| **08** | **Sơ đồ State – Vòng đời đơn hàng (Order Lifecycle)** | Statechart | [08_State_VongDoi_DonHang.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/08_State_VongDoi_DonHang.puml) |
| **09** | **Sơ đồ ERD – Nhóm Kho hàng & Nhập hàng** | CSDL / ERD | [09_ERD_KhoHang_NhapHang.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/09_ERD_KhoHang_NhapHang.puml) |
| **10** | **Sơ đồ ERD – Nhóm Vận chuyển (Logistics)** | CSDL / ERD | [10_ERD_VanChuyen.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/10_ERD_VanChuyen.puml) |
| **11** | **Sơ đồ ERD – Nhóm Tương tác & Chăm sóc khách hàng** | CSDL / ERD | [11_ERD_TuongTac_CSKH.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/11_ERD_TuongTac_CSKH.puml) |
| **12** | **Sơ đồ ERD – Nhóm Hệ thống & Thông báo** | CSDL / ERD | [12_ERD_HeThong_ThongBao.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/12_ERD_HeThong_ThongBao.puml) |
| **13** | **Sơ đồ Deployment – Triển khai hệ thống** | Deployment | [13_Deployment_TrienKhai_HeThong.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/13_Deployment_TrienKhai_HeThong.puml) |
| **14** | **Sơ đồ Sequence – Đổi/Trả hàng** | Sequence | [14_Sequence_DoiTraHang.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/14_Sequence_DoiTraHang.puml) |
| **15** | **Sơ đồ Sequence – Nhập kho & Tạo Serial Number** | Sequence | [15_Sequence_NhapKho_TaoSerialNumber.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/15_Sequence_NhapKho_TaoSerialNumber.puml) |
| **16** | **Sơ đồ Use Case – Khách hàng** | Use Case | [16_UseCase_KhachHang.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/16_UseCase_KhachHang.puml) |
| **17** | **Sơ đồ Use Case – Quản trị viên** | Use Case | [17_UseCase_QuanTriVien.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/17_UseCase_QuanTriVien.puml) |
| **18** | **Sơ đồ ERD – Nhóm Người dùng & Phân quyền (RBAC)** | CSDL / ERD | [18_ERD_NguoiDung_PhanQuyen.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/18_ERD_NguoiDung_PhanQuyen.puml) |
| **19** | **Sơ đồ ERD tổng quát (Master ERD 8 phân hệ)** | Master ERD | [19_ERD_TongQuat.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/19_ERD_TongQuat.puml) |
| **20** | **Sơ đồ Class Diagram – Tầng Model & Database Core** | Class OOP | [20_ClassDiagram_TangModel.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/20_ClassDiagram_TangModel.puml) |
| **21** | **Sơ đồ Component – Middleware Pipeline (Admin)** | Component | [21_Component_Middleware_Pipeline_Admin.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/21_Component_Middleware_Pipeline_Admin.puml) |
| **22** | **Sơ đồ Sequence – Đăng nhập & Session Timeout (8h)** | Sequence | [22_Sequence_DangNhap_SessionTimeout.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/22_Sequence_DangNhap_SessionTimeout.puml) |

---

### File Tổng Hợp:
- 📑 **[Tong_hop_tat_ca_so_do.puml](file:///C:/laragon/www/Ban_linh_kien/Sơ%20đồ/Tong_hop_tat_ca_so_do.puml)**: Tập hợp đầy đủ mã nguồn PlantUML của 22 sơ đồ trong một file duy nhất.
