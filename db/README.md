# Bản xuất cơ sở dữ liệu

`noxh.sql` — toàn bộ CSDL `sql_noxh`: 95 bảng, cấu trúc lẫn dữ liệu.

Xuất lúc **01/10/2026 13:08**.

## Nạp lên máy chủ

Không cần Docker, đây là SQL thuần:

```bash
mysql -u <user> -p <ten_db> < db/noxh.sql
```

Hoặc import qua phpMyAdmin.

Nạp file này rồi thì **không chạy `php artisan migrate`** và **không chạy
seeder** nữa — trong đây đã có đủ cả cấu trúc bảng lẫn dữ liệu nền.

## Nội dung

| | |
|---|---|
| 34 tỉnh/thành, 3.321 phường/xã | theo cơ cấu hành chính từ 01/07/2025 |
| 451 phường/xã có toạ độ | của 4 tỉnh đang có dự án |
| 6 dự án mẫu | toạ độ riêng từng dự án |
| 12 tài khoản | quản trị + 6 tư vấn viên + nhân viên kinh doanh |
| 594 ô nội dung quản trị | chữ của toàn bộ các trang |
| Bộ kiểm tra điều kiện | 5 bước, 6 tiêu chí chấm |

## Lưu ý

- File **chứa mật khẩu đã băm** của tài khoản quản trị và các tài khoản
  demo. Giữ kho mã nguồn ở chế độ riêng tư, và đổi mật khẩu quản trị ngay
  sau khi lên máy chủ thật.
- Tài khoản tư vấn viên là **tài khoản DEMO dùng chung một mật khẩu mẫu** —
  xoá hoặc đổi mật khẩu trước khi chạy thật.
- Ảnh **không nằm trong file này** (CSDL chỉ lưu đường dẫn). Ảnh cắt từ bản
  thiết kế nằm trong `public/uploads/noxh`, đã có sẵn trong git.

## Xuất lại bản mới

Chạy trên máy phát triển (có Docker):

```bash
docker exec noxh-db sh -c 'exec mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" \
  --single-transaction --routines --triggers --events --no-tablespaces \
  --default-character-set=utf8mb4 --hex-blob sql_noxh' > db/noxh.sql
```

Dùng `utf8mb4` + `utf8mb4_unicode_ci` và không sinh `DEFINER`, nên nạp được
cả MySQL 5.7, MySQL 8 lẫn MariaDB.
