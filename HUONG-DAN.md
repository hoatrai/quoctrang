# quoctrang.com: tiệm điện thoại tiếng Việt

File zip này là bản anh gửi, em đã thêm sẵn mọi thứ. Giải nén rồi upload đè 2 thư mục `woodmart` và `woodmart-child` vào `wp-content/themes/`.

## Em đã thêm gì
- `woodmart/languages/vi.mo`: dịch giao diện theme (Thêm vào giỏ, Còn hàng, Xem nhanh...).
- `woodmart-child/languages/woocommerce-vi.mo`, `elementor-vi.mo`: bản dịch WooCommerce/Elementor (chỉ dùng khi site chưa có gói ngôn ngữ chính thức).
- `woodmart-child/inc/quoctrang-setup.php`: script chạy 1 lần (đổi tên site, danh mục, trang chủ, footer, menu, trang chính sách).
- `woodmart-child/functions.php`: gắn script, nạp bản dịch, và hiện giá thật khi sản phẩm có giá (chưa có giá thì hiện "Liên hệ: 0939 102 209" như cũ).
- `san-pham-mau.csv` (ở thư mục gốc, không upload lên theme): 12 sản phẩm mẫu dạng nháp để import vào WooCommerce.

## Các bước
1. Sao lưu DB và `wp-content`.
2. Mở `woodmart-child/inc/quoctrang-setup.php`, sửa phần THÔNG TIN CỬA HÀNG ở đầu file (tên, hotline, địa chỉ, giờ mở cửa).
3. Upload đè `woodmart` và `woodmart-child` vào `wp-content/themes/`.
4. Đăng nhập admin, mở `/wp-admin/?qt_setup=preview` để xem trước (chưa đổi gì). Ổn thì mở `/wp-admin/?qt_setup=apply`.
5. Cài đặt > Đường dẫn tĩnh > Lưu thay đổi. Xóa cache.
6. Xóa dòng `require_once ... quoctrang-setup.php` trong `woodmart-child/functions.php` (hoặc xóa file script).
7. Sản phẩm > Nhập vào: chọn `san-pham-mau.csv`, điền giá, ảnh, mô tả thật rồi đăng.
8. Thay logo trong Header builder.

## Hoàn tác
`/wp-admin/?qt_setup=undo` bật lại trang/bài/sản phẩm đã ẩn. Nội dung Elementor cũ của trang chủ/footer lưu ở meta `_qt_backup_elementor_data`, header cũ ở option `qt_backup_whb_header_331220`.

## Em tự giả định, anh kiểm tra
- Tên "Quốc Trang Mobile", hotline 0906 717 457, địa chỉ 128 Bạch Đằng, P.2, Tân Bình (lấy từ nút liên hệ trên site). Sản phẩm chưa có giá vẫn hiện 0939 102 209 như cũ; hai số khác nhau, anh chọn số đúng.
- Giờ mở cửa 8:00-21:00, bảo hành 12 tháng, đổi trả 7 ngày, trả góp, giao toàn quốc, thu cũ đổi mới, sửa chữa là nội dung mẫu, sửa theo thực tế.
- Site đang `noindex`: khi ra mắt bỏ tick ẩn khỏi công cụ tìm kiếm ở Cài đặt > Đọc.
- Script ẩn (chuyển nháp, không xóa) trang demo, trang du lịch/thần số học, 23 bài blog cũ, 18 sản phẩm laptop demo. Tắt bằng 3 hằng số QT_HIDE_* trong script.
- Plugin Tutor LMS (khóa học) vẫn bật, tắt nếu không dùng.
