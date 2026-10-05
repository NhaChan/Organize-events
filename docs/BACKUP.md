# Sao lưu tự động

Hệ thống tạo một bản sao lưu mỗi ngày lúc **03:00 theo giờ Việt Nam**. Mỗi bản gồm:

- `database.sql.gz`: toàn bộ cơ sở dữ liệu MySQL.
- `uploads.tar.gz`: ảnh và tệp trong `storage/app/public`.
- `SHA256SUMS`: mã kiểm tra tính toàn vẹn.
- `manifest.txt`: thời gian và thông tin bản backup.

Các bản sao lưu nằm trong thư mục `backups/YYYY-MM-DD_HH-MM-SS`. Mặc định, bản cũ hơn 14 ngày sẽ tự động bị xóa.

## Khởi động job

```bash
docker compose up -d backup
docker compose logs --tail=50 backup
```

Log phải có dòng `Next backup` với thời gian 03:00 `+07`.

## Chạy thử ngay

```bash
docker compose run --rm backup once
```

Kiểm tra kết quả:

```bash
ls -lah backups
cd backups/YYYY-MM-DD_HH-MM-SS
sha256sum -c SHA256SUMS
```

## Thay đổi giờ hoặc thời gian lưu

Thêm vào `.env`, sau đó chạy lại `docker compose up -d backup`:

```dotenv
BACKUP_TIMEZONE=Asia/Ho_Chi_Minh
BACKUP_TIME=03:00
BACKUP_RETENTION_DAYS=14
BACKUP_UID=1000
BACKUP_GID=1000
```

`BACKUP_TIME` dùng định dạng 24 giờ `HH:MM`.

## Phục hồi cơ sở dữ liệu

Phục hồi sẽ ghi dữ liệu vào database hiện tại. Hãy tạo thêm một bản backup mới trước khi thực hiện.

```bash
docker compose run --rm backup once
docker compose run --rm --entrypoint /bin/bash backup -lc 'gunzip -c /backups/YYYY-MM-DD_HH-MM-SS/database.sql.gz | MYSQL_PWD="$DB_PASSWORD" mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" "$DB_DATABASE"'
```

Thay `YYYY-MM-DD_HH-MM-SS` bằng tên thư mục cần phục hồi. Mật khẩu được lấy từ môi trường container và không xuất hiện trong lịch sử lệnh.

## Phục hồi ảnh tải lên

Lệnh này ghi đè tệp trùng tên nhưng không xóa các tệp mới hơn đang có:

```bash
tar -xzf backups/YYYY-MM-DD_HH-MM-SS/uploads.tar.gz -C storage/app/public
docker compose exec app chown -R www-data:www-data storage/app/public
```

## Khuyến nghị an toàn

Thư mục `backups` đã được bỏ khỏi Git. Nên đồng bộ backup sang máy hoặc kho lưu trữ riêng; backup chỉ nằm trên cùng máy chủ sẽ không cứu được dữ liệu nếu ổ đĩa hoặc máy chủ bị hỏng.
