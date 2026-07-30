# Minh Triều Party — Laravel 11

Bản chuyển đổi từ source PHP thuần sang Laravel 11.55, giữ các chức năng public/admin và dùng giao diện responsive tone hồng–cam.

## Yêu cầu và cách chạy

Project hiện chạy bằng XAMPP PHP 8.2 tại `D:\xampp2`.

1. Mở `D:\xampp2\xampp-control.exe`.
2. Bật Apache và MySQL.
3. Mở `http://localhost/event-blog-laravel11/public/`.
4. Admin: `http://localhost/event-blog-laravel11/public/admin/login`.

Trước khi seed dữ liệu, đặt `ADMIN_SEED_USERNAME` và `ADMIN_SEED_PASSWORD`
trong file `.env`. Không commit file `.env` lên Git.

Có thể chạy lệnh Artisan trong XAMPP Shell hoặc PowerShell:

```bat
D:\xampp2\php\php.exe artisan migrate
D:\xampp2\php\php.exe artisan db:seed
D:\xampp2\php\php.exe artisan test
```

## Tạo database mới

Database đang sử dụng là `event_blog` trong MySQL của `D:\xampp2`.

Nếu cần tạo lại bằng phpMyAdmin:

1. Mở `http://localhost/phpmyadmin`.
2. Chọn **New / Mới**.
3. Nhập `event_blog`, collation `utf8mb4_unicode_ci`, rồi chọn **Create / Tạo**.
4. Kiểm tra `.env`: `DB_DATABASE=event_blog`, `DB_USERNAME=root`, `DB_PASSWORD=`.
5. Chạy:

```bat
D:\xampp2\php\php.exe artisan migrate:fresh --seed
D:\xampp2\php\php.exe artisan storage:link
```

`migrate:fresh` xóa toàn bộ bảng trong database đích, chỉ dùng khi muốn tạo lại từ đầu. Với database đang có dữ liệu, dùng `artisan11.bat migrate`.

## Chức năng

- Trang chủ, danh sách dịch vụ, danh mục nhiều cấp, danh sách và chi tiết bài viết.
- Tìm kiếm/lọc bài viết, bộ đếm lượt xem, SEO meta, Open Graph, schema và sitemap.
- Sitemap XML tự cập nhật tại `/sitemap.xml`; robots tại `/robots.txt` có thể quản lý trong **Admin → Cấu hình website**.
- Admin riêng: đăng nhập, dashboard, CRUD bài viết/danh mục, trạng thái nháp/đăng/lưu trữ.
- Upload ảnh đại diện, nhiều ảnh phụ và xóa ảnh.
- Biên tập trang dịch vụ: banner, ảnh minh họa, nội dung, ba điểm nổi bật và CTA.
- Cấu hình thương hiệu, số điện thoại, Facebook, địa chỉ.

## Lưu ý framework

Composer đang ghi nhận advisory đối với nhánh Laravel 11. Dự án giữ đúng Laravel 11 theo yêu cầu và Composer được cấu hình cảnh báo thay vì chặn cài đặt. Trước khi triển khai production, chạy `composer audit` và cập nhật lên bản Laravel 11 đã vá ngay khi có.

## Chạy dự án với Docker

### Build và khởi động

Build image:

```bash
docker compose build
```

Khởi động các container ở chế độ nền:

```bash
docker compose up -d
```

Cũng có thể build và khởi động bằng một lệnh:

```bash
docker compose up -d --build
```

Sau khi các container đã chạy, tạo bảng và dữ liệu mẫu cho database:

```bash
docker compose exec app php artisan migrate --seed
```

Website chạy tại `http://localhost:8080`. MySQL trên máy host được mở tại
`127.0.0.1:3307` theo cấu hình mặc định trong `.env`.

### Các lệnh thường dùng

Kiểm tra trạng thái container:

```bash
docker compose ps
```

Theo dõi log của toàn bộ hệ thống hoặc riêng ứng dụng:

```bash
docker compose logs -f
docker compose logs -f app
```

Chạy lệnh Artisan:

```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan test
```

Mở shell trong container PHP:

```bash
docker compose exec app bash
```

Khởi động lại hoặc dừng hệ thống:

```bash
docker compose restart
docker compose down
```

Để xóa cả container và dữ liệu MySQL trong volume, dùng lệnh sau. Lệnh này sẽ
xóa toàn bộ dữ liệu database Docker:

```bash
docker compose down -v
```
