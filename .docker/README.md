# Chạy dự án bằng Docker

Khởi động các container:

```bash
docker compose up -d --build
```

Tạo cấu trúc và dữ liệu mẫu cho database ở lần chạy đầu tiên:

```bash
docker compose exec app php artisan migrate --seed
```

Website chạy tại `http://localhost:8080`. Dừng các container bằng:

```bash
docker compose down
```

Database được lưu trong volume `mysql-data`. Muốn thay port hoặc thông tin database,
đặt các biến sau trong file `.env`:

```dotenv
APP_PORT=8080
DB_FORWARD_PORT=3307
DOCKER_DB_DATABASE=event_blog
DOCKER_DB_USERNAME=event_blog
DOCKER_DB_PASSWORD=event_blog
DOCKER_DB_ROOT_PASSWORD=root
```

Không dùng `docker compose down -v` nếu muốn giữ dữ liệu MySQL.
