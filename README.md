# NOXH.vn

Cổng thông tin nhà ở xã hội. Laravel 10 + Blade + Vite. Yêu cầu **PHP 8.1+**
và **MySQL 5.7+ / MariaDB 10.3+**.

## Triển khai lên máy chủ

Máy chủ **không cần Docker, không cần Node, không cần Python**: CSS/JS đã
biên dịch (`public/build`) và ảnh cắt từ bản thiết kế
(`public/uploads/noxh`) đều nằm sẵn trong git.

### 1. Mã nguồn

```bash
git clone <repo> noxh && cd noxh
composer install --no-dev --optimize-autoloader
```

Trỏ **document root của tên miền vào thư mục `public`**, không phải thư mục
gốc dự án.

### 2. Cấu hình

```bash
cp .env.example .env
php artisan key:generate
```

Sửa trong `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tenmien.vn      # không có dấu / ở cuối
DB_HOST=... DB_DATABASE=... DB_USERNAME=... DB_PASSWORD=...
```

### 3. Cơ sở dữ liệu

**Cách A – có sẵn file .sql** (bản bàn giao, gửi riêng chứ không nằm trong
kho mã nguồn): nạp thẳng, *không* chạy migrate và *không* chạy seeder, vì
file đã có đủ cấu trúc lẫn dữ liệu.

```bash
mysql -u <user> -p <ten_db> < noxh-db-xxxx.sql
```

**Cách B – cài mới từ số không:**

```bash
php artisan migrate --force
php artisan db:seed --class=NoxhSeeder --force
```

`NoxhSeeder` gọi toàn bộ seeder của NOXH theo đúng thứ tự phụ thuộc và chạy
lại được nhiều lần — mọi seeder chỉ thêm ô còn trống, không đè lên nội dung
quản trị đã sửa. Lần sau có dữ liệu nền mới cũng chỉ chạy đúng lệnh này.

### 4. Quyền ghi

```bash
chmod -R 775 storage bootstrap/cache public/uploads public/userfiles public/image-cache
chown -R www-data:www-data storage bootstrap/cache public/uploads public/userfiles public/image-cache
```

Thiếu bước này thì trang báo lỗi 500 khi ghi log, hoặc quản trị tải ảnh lên
báo "không ghi được".

### 5. Dọn cache (chạy lại sau mỗi lần deploy)

```bash
php artisan config:clear && php artisan config:cache
php artisan route:clear  && php artisan route:cache
php artisan view:clear
```

## Việc chỉ chạy khi cần

### Toạ độ dự án

Bản đồ (`/du-an/ban-do`) chỉ ghim đúng khi dự án có toạ độ riêng. Thêm dự án
mới mà không nhập vĩ độ/kinh độ thì chạy:

```bash
php artisan noxh:toa-do          # toạ độ từng dự án
php artisan noxh:toa-do --xa     # nạp thêm toạ độ phường/xã (~1 giây/xã)
```

Tra cứu qua Nominatim của OpenStreetMap — miễn phí, không cần API key, nhưng
máy chủ phải ra được Internet. Lệnh không đè lên toạ độ người nhập tay.

### Ảnh cắt từ bản thiết kế

Chỉ chạy trên **máy phát triển**, khi sửa file trong `noxh_image/`:

```bash
python tools/tach-anh-ban-ve.py   # ghi vào public/uploads/noxh
```

### Biên dịch lại giao diện

Chỉ chạy trên **máy phát triển**, khi sửa SCSS hoặc JS:

```bash
npm install && npm run build      # ghi vào public/build
```

Nhớ commit cả `public/build` lẫn `public/uploads/noxh`.

### Bản đồ dùng Google Maps (tuỳ chọn)

Mặc định dùng OpenStreetMap, miễn phí, không cần khai báo gì. Muốn đổi:
*Cấu hình hệ thống → Bản đồ dự án* → chọn Google → dán API key. Nhớ giới hạn
key theo tên miền. Thiếu key thì trang tự quay về OpenStreetMap.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
"# dienmayvina_com" 
"# infyd" 
# t-shop
