# Chạy NOXH bằng Laragon (không di chuyển code)

Máy này **không có Docker** (không có `docker` trong PATH, không có Docker Desktop,
WSL bị chặn `Wsl/E_ACCESSDENIED`), nên dùng **Laragon ở `C:\laragon`** — Apache 2.4.54,
PHP 8.4.2, MySQL 8.0.30.

Code vẫn nằm nguyên ở `D:\sandbox\noxh`, chỉ có **1 file cấu hình mới** được thêm vào Laragon.

## Đang chạy

| Thành phần | Đường dẫn / địa chỉ |
|---|---|
| Mã nguồn | `D:\sandbox\noxh` |
| Web | <http://noxh.test> (Apache của Laragon, cổng 80) |
| DocumentRoot | `D:\sandbox\noxh\public` |
| CSDL | `noxh` trên MySQL của Laragon, datadir `C:\laragon\data\mysql-8` |
| File dump gốc | `D:\sandbox\db\sql_noxh_webchua.sql` |
| File vhost | `C:\laragon\etc\apache2\sites-enabled\auto.noxh.test.conf` |

## Cách dựng lại từ đầu

```powershell
# 1. MySQL của Laragon
C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqld.exe `
    --defaults-file=C:\laragon\bin\mysql\mysql-8.0.30-winx64\my.ini --console

# 2. Tạo database và nạp dump
C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -u root -e `
  "DROP DATABASE IF EXISTS noxh; CREATE DATABASE noxh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cmd /c "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -u root --default-character-set=utf8mb4 noxh < D:\sandbox\db\sql_noxh_webchua.sql"

# 3. Vhost (nội dung nằm ở docker\laragon-noxh.test.conf)
Copy-Item D:\sandbox\noxh\docker\laragon-noxh.test.conf `
          C:\laragon\etc\apache2\sites-enabled\auto.noxh.test.conf -Force

# 4. Apache
C:\laragon\bin\apache\httpd-2.4.54-win64-VS16\bin\httpd.exe

# 5. PHP + Composer cho dự án
cd D:\sandbox\noxh
composer install
copy .env.example .env      # rồi sửa DB_DATABASE=noxh, APP_URL=http://noxh.test
php artisan key:generate
```

Hoặc chỉ cần mở **Laragon → Start All**; `noxh.test` đã có sẵn trong `hosts`.

## Lưu ý quan trọng về tên miền `noxh.test`

`noxh.test` trước đây trỏ vào project cũ `C:\laragon\www\noxh`. File vhost đó đã được
thay bằng bản trỏ vào `D:\sandbox\noxh`. Bản gốc lưu ở
`scratch\laragon-noxh.test.conf.original`.

Đây cũng là file `auto.*.conf` do Laragon tự sinh: **khi Laragon Start All, nó có thể
sinh lại file này trỏ về `C:\laragon\www\noxh`**. Nếu web đột nhiên chạy project cũ,
chép lại file trong `docker\` như bước 3. Muốn tránh hẳn thì tắt "Auto Virtual Hosts"
trong Laragon, hoặc thêm `noxh.test` thành vhost thủ công (file không có tiền tố `auto.`).

## Ghi chú kỹ thuật
- Trong sandbox của DSH, `is_writable()` của PHP trả `false` cho **mọi** thư mục (token
  bị hạn chế), làm Laravel không boot được. Chạy qua Laragon/Apache bằng token thật nên
  không còn vấn đề này; `vendor/` giữ nguyên bản gốc từ `composer.lock`.
- `node_modules` không cần: `public/build` (CSS/JS đã biên dịch) đã có sẵn trong git.
  Chỉ chạy `npm install && npm run build` khi sửa SCSS/JS.
- `APP_DEBUG=true` và `DEBUGBAR_ENABLED=false` trong `.env` — bật debugbar khi cần soi query.

## Script kiểm tra trong `scratch\`

| File | Việc |
|---|---|
| `smoke-noxh.php` | Gọi toàn bộ route frontend + trang CMS + dự án + tin tức qua HTTP, in bảng OK/404 |
| `test-chuc-nang.php` | Wizard kiểm tra điều kiện, tra cứu mã kết quả, form lead, tìm kiếm, trang đăng nhập |
| `test-lead-3.php` | Gửi lead qua `/de-lai-thong-tin` và `/lien-he-tu-van`, kiểm tra bản ghi trong `contacts`, rồi xoá dữ liệu test |
| `kiem-tra-admin.php` | Đăng nhập admin thật rồi kiểm tra `/product/index` có hiện đủ dự án và bộ lọc danh mục chạy không |

Chạy bằng PHP của Laragon:

```powershell
C:\laragon\bin\php\php-8.4.2-nts-Win32-vs17-x64\php.exe scratch\smoke-noxh.php
```

## Đưa lên host

Trên host **không cần chèn SQL tay**. Sau khi nạp file `.sql` bản bàn giao, chạy:

```bash
php artisan db:seed --class=NoxhSeeder --force
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear
```

`NoxhSeeder` chạy lại được nhiều lần, mọi seeder bên trong chỉ thêm ô còn trống nên
không đè nội dung quản trị đã sửa.

### ⚠️ Không chạy `php artisan migrate` trên CSDL rỗng

Đã thử và **lỗi ngay từ bước đầu**:

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table '...languages' doesn't exist
```

`AppServiceProvider::boot()` truy vấn bảng `languages` ngay khi ứng dụng khởi động
(`Language::where('canonical', $locale)`), nên khi CSDL chưa có bảng đó thì cả
`artisan migrate` lẫn `artisan db:seed` đều không chạy được. Vì vậy đường "Cách B —
cài mới từ số không" trong `README.md` (`migrate --force` rồi `db:seed`) hiện
**không hoạt động**; phải nạp file `.sql` trước (đúng như "Cách A" mà README khuyến
nghị), rồi mới seed.

## Danh mục dự án bị trống ở `/product/index`

**Triệu chứng:** trang quản trị danh sách dự án hiện trống, dù web ngoài `/du-an`
vẫn hiện đủ 6 dự án.

**Nguyên nhân:** `products` có 6 dự án, nhưng bảng liên kết `product_catalogue_product`
rỗng. `RemoveLegacyCatalogDataSeeder` xoá sạch bảng này, còn `NoxhFrontendContentSeeder`
lại bỏ qua dự án đã tồn tại (`product_language where canonical exists -> continue`),
nên dự án cũ không bao giờ được nối lại danh mục. Trang quản trị lấy danh sách bằng
query có `INNER JOIN product_catalogue_product` nên ra 0 dòng. Web ngoài không bị vì
`ProjectQuery` chỉ join `product_language`.

Kèm theo đó, `products.product_catalogue_id` trỏ vào id danh mục đã bị xoá (mồ côi) —
sau khi chạy lại seeder danh mục, id danh mục đổi (15–19 thành 20–24).

**Cách xử lý:** seeder `NoxhProjectCatalogueSeeder` (đã thêm vào `NoxhSeeder`), chạy:

```powershell
php artisan db:seed --class=NoxhSeeder --force
```

Seeder này chỉ nối lại dòng còn thiếu nên **không tạo bản trùng**, và **không đè** dự án
mà quản trị đã chủ động đổi sang danh mục khác. Nó tra id danh mục theo
`orderBy('id')->value('id')` chứ không hard-code, nên đúng cả khi id danh mục thay đổi.

## Địa giới hành chính trong form tạo/sửa dự án

Form `/product/create` và `/product/{id}/edit` trước đây lấy dữ liệu hành chính từ **bộ
bảng 3 cấp cũ**, nên sai cả ba chỗ. Đã sửa:

| Lỗi | Nguyên nhân | Đã sửa thành |
|---|---|---|
| Ô chọn tỉnh còn Thái Bình, Bắc Giang, Quảng Nam… | `ProductController::duLieuDuAn()` dùng `App\Models\Province` → bảng `provinces` (63 tỉnh cũ) | `DB::table('vn_provinces')` — 34 tỉnh, 2 cấp |
| Có ô "Quận/Huyện" thừa | Cấp huyện bỏ từ 01/07/2025 | Bỏ hẳn ô đó và cả hook `location.js` |
| Chọn tỉnh mà ô Phường/Xã không nhảy | `location.js` gọi `/ajax/location/getLocation`, controller đó đọc `districts`/`wards` cũ; mã phường/xã 3 cấp không khớp mã 5 chữ số của `vn_wards` | JS riêng trong `noxh.blade.php` gọi thẳng `/dia-gioi/phuong-xa/{maTinh}` — **cùng nguồn với thanh tìm ở trang chủ** |

Sửa kèm hai lỗi phát hiện thêm khi kiểm tra:

- **`ProductRepository::getProductById()`** chọn danh sách cột cố định từ thời shop cũ,
  **thiếu cả 28 cột của dự án NOXH** (`province_code`, `ward_code`, `latitude`, `price_from`,
  `status`…). Hệ quả: mở form sửa thì mọi ô của khối dự án hiện **trống**, không báo lỗi —
  vì mỗi ô đọc qua `($product->ten_cot) ?? ''`. Đã bổ sung đủ cột.
- **`noxh.blade.php` đọc `$duAn`** — biến này không tồn tại (controller truyền `$product`),
  nên ô chọn tỉnh và mã phường/xã không bao giờ hiện lại giá trị đã lưu. Đã đổi sang `$product`.

Cũng đã sửa cùng lỗi bảng cũ ở hai chỗ nữa: bộ lọc tỉnh trang `/du-an`
(`ProjectController::index`) và form dự án của nhân viên kinh doanh
(`Sale\ProjectController::duLieuForm`).

**Bẫy cần nhớ:** đổi tỉnh thì JS **phải xoá** giá trị phường/xã đang chọn trước khi nạp
danh sách mới. Trình duyệt không tự bỏ giá trị cũ khi `<option>` biến mất, nên để nguyên
thì ô vẫn giữ mã phường của tỉnh trước và bấm Lưu sẽ lưu một cặp tỉnh/xã không tồn tại.

### Ô Phường/Xã không bấm được — Select2

Sau khi nối cascade, ô Phường/Xã vẫn **không bấm được**: `library.js:14` chạy
`$('.setupSelect2').select2()` khi trang sẵn sàng. Select2 4.0.0 **ẩn thẻ `<select>` gốc**
(`display:none`) rồi tự vẽ một khung riêng, nên mọi thay đổi qua `innerHTML` chỉ tới thẻ gốc
— khung người dùng nhìn thấy không đổi. Ô đó còn bị vẽ ở trạng thái *disabled* vì thẻ gốc
có thuộc tính `disabled` từ đầu.

Đã kiểm chứng bằng Chrome headless trên một trang thí nghiệm cô lập:

| Ô | Thẻ `<select>` gốc | Khung Select2 hiển thị |
|---|---|---|
| có `setupSelect2` | 3 option, `value=07210` | vẫn hiện "[Chọn tỉnh trước]", không bấm được |
| **không** `setupSelect2` | 3 option, `value=07210` | không có Select2 — dùng thẻ thật, bấm được |

**Cách sửa:** bỏ class `setupSelect2` khỏi **duy nhất ô Phường/Xã**; ô Tỉnh/Thành giữ
nguyên Select2 vì nó render sẵn từ PHP. Ô Phường/Xã trở thành `<select>` thật (giống các ô
lọc ngoài website), kèm một ô "gõ để lọc nhanh phường/xã" hiện ra khi danh sách trên 20 dòng
— thay cho ô tìm kiếm mà Select2 vẫn cho.

Đừng gắn lại `setupSelect2` cho ô này trừ khi chuyển nó sang Select2 dạng AJAX
(`ajax.url` + `processResults`); đổ `innerHTML` vào thẻ gốc là không tới được giao diện.

### Select2 phát sự kiện bằng jQuery — không nghe được bằng addEventListener

Sau khi bỏ Select2 khỏi ô Phường/Xã, ô đó vẫn **không nhảy** khi chọn tỉnh. Nguyên nhân nằm
ở ô Tỉnh/Thành: nó vẫn bị Select2 bọc, và Select2 phát sự kiện bằng
`jQuery.trigger('change')`. Mà `jQuery.trigger` **chỉ gọi handler gắn qua jQuery** — handler
gắn bằng `oTinh.addEventListener('change', …)` không bao giờ chạy.

Đã kiểm chứng bằng Chrome thật trên một trang thí nghiệm: chọn "Thành phố Hà Nội" làm
`o tinh value = "01"` và giao diện Select2 đổi đúng, nhưng dòng log trong handler
`addEventListener` **không hề xuất hiện**.

**Cách sửa:** nghe bằng jQuery khi có jQuery (`jQuery(oTinh).on('change', …)`), chỉ rơi về
`addEventListener` khi không có jQuery.

Ghi chú: ô Phường/Xã vẫn là `<select>` thật nên `oXa.addEventListener('input', …)` cho ô lọc
nhanh vẫn đúng — chỉ ô nào bị Select2 bọc mới cần nghe bằng jQuery.

### Ô lọc phường/xã phải bỏ dấu

Gõ "ba dinh" không ra kết quả nào vì tên là "Ba Đình" có dấu. Đã thêm hàm bỏ dấu
(NFD + thay `đ`/`Đ` riêng, vì `đ` không tách được bằng NFD) cho cả từ khoá lẫn tên phường/xã.

### Các ô trong form dự án không cao đều nhau

Ô bị Select2 bọc cao **32px**, ô `<select>` thường và ô nhập chữ cao **40px** — lệch 8px nên
khối "Thông tin dự án" nhìn so le. Nguyên nhân: CSS của theme đặt
`.select2-container .select2-selection--single { height:32px !important }`.

Đo bằng Chrome trước khi sửa:

```
Chủ đầu tư (Select2)   32px      Trạng thái            40px
Tỉnh/Thành (Select2)   32px      Hình thức sở hữu      40px
                                 Phường/Xã, Địa chỉ    40px
```

**Cách sửa:** hai form (`backend/product/product/store.blade.php` và
`sale/project/form.blade.php`) nay có thêm class **`nx-form-du-an`**; `noxh.blade.php` in một
khối `<style>` bọc trong `@once` để đưa Select2 về 40px. Không sửa CSS của theme vì nó dùng
chung cho cả trang quản trị.

Đổi 40px thì phải đổi **cả bốn** trị số trong khối đó (`height`, `line-height` của
`__rendered`, `padding`, `height` của `__arrow`), không thì lệch lại. Công thức:
`line-height = 40 − padding-trên − padding-dưới`, khớp padding 6px của `.form-control`.

Sau khi sửa, đo lại trên **cả hai** form: mọi ô đều 40px, cùng `top`.

### Banner trang chủ: 2 ảnh (máy tính + điện thoại)

**Sửa ở đâu về sau:**

| Muốn đổi | Vào |
|---|---|
| Ảnh banner | **Cài đặt → Giới thiệu → "Khối 1: Banner trang chủ"** — ô *Ảnh nền banner (máy tính)* và *Ảnh nền banner (điện thoại)* |
| Slide điện thoại | Bảng `slides`, từ khoá `mobile-slide` (bản ghi #35) — chỉ là bản dự phòng |

Trang chủ đọc ảnh điện thoại theo thứ tự ưu tiên: ô `hero_image_mobile` trong Giới thiệu →
**slide `mobile-slide`** → ảnh máy tính.

> **Đã đảo thứ tự này một lần.** Bản trước để slide đứng trước, nghĩa là sửa ô trong
> *Giới thiệu* xong **không thấy gì đổi** — rất dễ tưởng là hỏng. Ô trong Giới thiệu là chỗ
> quản trị nhìn thấy được nên phải đứng trước; slide chỉ dùng khi ô đó để trống.

**Ô ảnh trong quản trị: bấm vào mở CKFinder 2 để chọn/tải ảnh, chọn xong tự ghi
đường dẫn vào ô. Đã kiểm chứng chạy được** trên PHP 8.4 + Chrome:

```
backend/plugins/ckfinder_2/ckfinder.html                                 -> 200
backend/plugins/ckfinder_2/core/connector/php/connector.php?command=Init -> 200,
      <Error number="0"/> enabled="true" csrfProtection="true"
```

Mở ra rồi đọc ruột `<iframe>` bên trong cửa sổ chọn ảnh thì thấy đúng giao diện
CKFinder: `Folders · Images · Basket · Upload · Refresh · Settings · Maximize ·
Help · Search · <Empty Folder>`. CKFinder báo "This is the DEMO version" nhưng
chính `ckfinder_2/config.php:62` ghi *"fully functional, in demo mode"* — không
có khoá bản quyền thì vẫn dùng đủ chức năng.

> **Ba lần tôi kết luận sai về chỗ này, ghi lại để không lặp:**
> 1. Đọc `/json/list` rồi so `targetId` — sai tên khoá, endpoint trả về `id`, nên
>    mọi phần tử đều `undefined` và phép so "target mới" luôn sai.
> 2. Thấy cửa sổ đứng ở `about:blank` rồi kết luận "hỏng". **`about:blank` là chủ ý:**
>    `p = a.env.webkit ? 'about:blank' : ''` rồi `window.open(p,'CKFinderpopup')` và
>    ghi HTML vào cửa sổ đó; giao diện thật nằm trong `<iframe>`, nên URL và
>    `body.innerText` của cửa sổ luôn rỗng — không suy ra được gì.
> 3. Đoán `CKFinder.DEFAULT_basePath` là thủ phạm. Nó đúng là không được gán ở đâu
>    trong bản `ckfinder.js` này, **nhưng không phải nguyên nhân**: `finder.js` không
>    đặt `basePath` mà CKFinder vẫn tự dò ra từ thẻ `<script>`.
>
> Cách đo ĐÚNG: giữ lấy đối tượng mà `window.open` trả về, rồi đọc
> `cuaSo.document.querySelector('iframe').contentDocument.body.innerText`
> (cùng tên miền nên đọc được) — xem `scratch/kiem-tra-ckfinder-that.cjs`.

Ảnh tải lên qua CKFinder nằm ở `public/userfiles/image/` (`$baseUrl = '/userfiles/'`
trong `ckfinder_2/config.php`), **khác** thư mục ảnh của giao diện NOXH là
`public/uploads/noxh/`. Cả hai đều dùng được vì ô chỉ lưu đường dẫn.

**Ô ảnh nay có ẢNH XEM TRƯỚC bên phải** (`renderSystemImages()` trong
`app/Helpers/MyHelper.php` + `HT.xemTruocAnh()` trong
`public/vendor/backend/library/finder.js`). Trước đây chỉ có một ô nhập: gõ
đường dẫn xong không biết mình vừa chọn ảnh nào. Ảnh xem trước:
- lấy giá trị đang lưu lúc tải trang;
- cập nhật khi gõ tay (`input`/`change`);
- cập nhật sau khi chọn trong CKFinder — phải gọi tay vì CKFinder đặt giá trị
  bằng jQuery `.val()`, mà `.val()` **không** phát sinh sự kiện `change`;
- tự ẩn nếu đường dẫn sai (bắt `onerror`), không hiện biểu tượng ảnh hỏng.

Đã đo trên trang thật: `anh dang dung: ["/uploads/noxh/banner-pc.jpg",
"/uploads/noxh/banner-mobile.jpg"]`, gõ giá trị mới thì `src` đổi theo, xoá trắng
thì khung ẩn.

Vì đã đổi markup của các ô mà form gửi đi, phải kiểm tra lại việc LƯU:
`node scratch/kiem-tra-luu-gioi-thieu.cjs <cookie>` bấm "Lưu lại" mà không đổi gì,
rồi tải lại và so từng ô. Kết quả: **438 ô trước và sau, 0 ô bị đổi hay bị mất.**
Chạy lại script này mỗi khi sửa `renderSystemImages()`.

**Ảnh hiện tại** — sinh bằng `python tools/lam-anh-banner.py`:

| | Tệp | Kích thước | Dung lượng | Nguồn |
|---|---|---|---|---|
| Máy tính | `public/uploads/noxh/banner-pc.jpg` | 3840×1200 | 455 KB | cắt + thu nhỏ 1.56× |
| Điện thoại | `public/uploads/noxh/banner-mobile.jpg` | 1200×1938 | 415 KB | cắt + thu nhỏ 1.45× |

Ảnh gốc: `tools/anh-goc/linh-dam-01.jpg` (6000×3375, Chung cư HH Linh Đàm, Hà Nội,
CC BY-SA 4.0, tác giả Phan Minh Tuấn — xem `tools/anh-goc/NGUON-ANH.txt`).
Tải lại bằng `powershell -File tools/tai-anh-goc.ps1`.

Hai ảnh cũ (`du-an-mac-dinh.jpg`, `du-an-mac-dinh-doc.jpg`) vẫn còn trong
`public/uploads/noxh` làm bản lùi.

### Vì sao banner bị mờ (đã tìm ra và sửa)

Ảnh gốc `du-an-mac-dinh.jpg` chỉ có **1200×324**, bị phóng lên 1920×600 (×1.6) rồi trình duyệt
còn vẽ ở bề rộng 1905 CSS px — trên màn hình 2x thì phải vẽ 3810 điểm ảnh từ một ảnh 1920, tức
là nhoè gấp đôi, cộng thêm một lần phóng to trước đó.

Cách sửa: dùng ảnh gốc **lớn hơn** khung hiển thị rồi **thu nhỏ**. Script báo lỗi nếu phải phóng
to. Bản PC hiện tại 3840×1200 (= 2× bề rộng hiển thị lớn nhất), nguồn 6000×1875 nên vẫn là thu
nhỏ 1.56 lần → nét thật.

### Ba điều rút ra khi làm ảnh, đừng sửa mà quên

1. **Ảnh gốc phải lớn hơn khung hiển thị.** Nguồn nhỏ hơn thì mọi mẹo cắt ghép đều vô nghĩa —
   xem mục "Vì sao banner bị mờ" ở trên.
2. **Tỉ lệ ảnh phải khớp tỉ lệ khung.** Khung thật đo bằng `scratch/chup-banner.cjs`:
   máy tính 1265×334 (tỉ lệ 3.79) lúc 1280px và 1905×334 (5.70) lúc 1920px; điện thoại 390×630
   (1.615). Ảnh dùng `object-fit: cover` + `object-position: center top` nên **phần bị cắt luôn
   nằm ở ĐÁY ảnh** — toà nhà và bầu trời phải nằm ở nửa trên.
3. **Không để trời trống ở đầu ảnh.** Bản điện thoại đầu tiên cắt từ `y=0` nên 22% trên cùng
   màn hình chỉ là trời trơn, trông như thiếu ảnh. Cắt từ `y=560`, chỉ còn 178 điểm ảnh trời
   trước khi chạm nóc toà nhà (ở `y=738`).

### Script tự kiểm tra kết quả

`tools/lam-anh-banner.py` in ra hệ số `nguon/day` và **thoát bằng mã 1** nếu:

- `nguon/day < 1` → đang phóng to ảnh, chắc chắn mờ;
- tỉ lệ ảnh ra lệch quá 1% so với tỉ lệ khung;
- vùng cắt lệch tỉ lệ so với ảnh ra (toà nhà sẽ bị kéo giãn).

Kiểm tra trên web thật:

```powershell
& 'C:\Program Files\nodejs\node.exe' scratch\chup-banner.cjs 390 1280 1920  # do khung + chup anh doi chieu
& 'C:\laragon\bin\php\php-8.4.2-nts-Win32-vs17-x64\php.exe' scratch\kiem-tra-cuoi-banner.php
```

Ảnh chụp đối chiếu để ở `scratch/anh-chup/`.

## Hai nút banner nằm chung một hàng, chữ một dòng, trên điện thoại

`.nx-hero__nut` mặc định `flex-wrap: wrap` nên khi không đủ chỗ thì nút "Kiểm tra
điều kiện" rơi xuống hàng dưới, làm banner cao thêm và đẩy chữ lên. Đã đổi sang
`nowrap` + chia đều bề ngang.

Nhưng như vậy ở màn 320px thì **chữ trong nút** lại xuống dòng (nút chỉ còn 140px,
chữ "KIỂM TRA ĐIỀU KIỆN" cần 121px ở cỡ 12px mà chỉ có 103px chỗ). Vì hai nút chia
đều bề ngang khung, **bề rộng nút tỉ lệ với bề rộng màn hình**, nên cỡ chữ cũng
phải theo tỉ lệ đó:

```scss
font-size: clamp(10px, 3.1vw, 12px);   // 10px ở 320px, 12px từ 375px lên
```

| Bề rộng | Trước | Sau |
|---|---|---|
| 320px | 2 hàng, chữ 2 dòng | 1 hàng, nút 140px, chữ 10px **1 dòng** |
| 360px | 2 hàng | 1 hàng, nút 160px, chữ 11,2px, 1 dòng |
| 375–480px | 2 hàng | 1 hàng, chữ 11,6–12px, 1 dòng |
| ≥481px | đã 1 hàng | không đổi (13px ở ≤768, 15px ở rộng hơn) |

Sửa ở `resources/css/components/_noxh-home.scss` (khối `$nx-md` và `$nx-sm`) **và**
`public/build/assets/app-fb4cf9c4.css` — chạy `node scratch/them-css-nut-hero.cjs`
để vá bản đã biên dịch (script tự gỡ khối cũ nên chạy lại nhiều lần vẫn đúng).

Mũi tên trang trí ở nút đầu bị ẩn ở màn ≤480px cho đủ chỗ:
`.nx-btn:first-child svg:last-child`. Dùng `:last-child` để không đụng vào hình cái
file của nút thứ hai (hình đó là con **đầu** của nút đó). Hình trong nút co còn 14px.

**Không đặt `white-space: nowrap`.** Cỡ chữ theo `vw` chỉ vừa với chữ HIỆN TẠI; nếu
quản trị đổi chữ trên nút dài hơn thì chữ sẽ xuống dòng — chấp nhận được. Nếu ép
`nowrap` thì chữ dài sẽ tràn ra ngoài nút, trông hỏng hơn.

Đo bằng `node scratch/do-nut-hero.cjs [bề rộng...]`. Script đếm **số dòng chữ thật**
bằng cách bọc đoạn chữ vào `Range` rồi đếm số hình chữ nhật nó chiếm (1 = một dòng),
kiểm tra hai nút có cùng `top` không, và có tràn khung không. Kết quả hiện tại:
320→1280px đều **1 hàng, chữ 1/1 dòng, không tràn**.

## Ảnh cờ ngôn ngữ và ảnh đại diện tài khoản bị 404

Bản clone không kèm theo thư mục `public/userfiles/`, nhưng CSDL lại trỏ vào đó:

| Chỗ | Giá trị trong dump | Hậu quả |
|---|---|---|
| `languages.image` (id 1–3) | `/public/userfiles/image/language/Flag_of_Vietnam_svg.png`, `en.png`, `cn.png` | mọi trang quản trị 404 một ảnh cờ |
| `users.image` (id 4504 — tài khoản admin) | `/userfiles/image/1750518865_6856cc51d7a38.png` | **mọi** trang quản trị 404 thêm một ảnh, do `sidebar.blade.php:19` hiện ảnh của tài khoản đang đăng nhập |

Đã sửa:

1. `python tools/lam-anh-co.py` vẽ lại 3 ảnh cờ (60×40, vẽ bằng hình học, không
   cần mạng) vào `public/userfiles/image/language/`.
2. `php scratch/don-anh-thieu.php --sua` đặt `users.image = NULL` cho tài khoản có
   ảnh trỏ tới tệp không tồn tại. Sidebar khi đó tự dùng ảnh mặc định
   `uploads/noxh/avatar-mac-dinh.png` — đúng ý đồ của dòng
   `src="{{ $toi?->image ?: asset('uploads/noxh/avatar-mac-dinh.png') }}"`.
   Script này quét **mọi** tài khoản chứ không hard-code id, và không có `--sua`
   thì chỉ báo cáo.

Kiểm chứng: chạy `node scratch/kiem-tra-o-anh.cjs <cookie> /introduce/index` →
`khong co loi js nao` (trước đó là 2 dòng 404).

## Bộ lọc tư vấn viên theo khu vực không lọc được — vì CSS, không phải JS

Chọn một khu vực trong "Chọn khu vực" ở trang chủ thì danh sách tư vấn viên
không đổi. Nhìn thì tưởng logic lọc sai, nhưng **JS hoàn toàn đúng**.

Nguyên nhân: `[hidden] { display: none }` là **kiểu của trình duyệt**, độ ưu tiên
thấp nhất, nên **mọi khai báo `display` của tác giả đều đè được nó**. JS đặt
`the.hidden = true` chuẩn, mà thẻ vẫn hiện vì khối tư vấn ở trang chủ nằm trong
`.nx-doi-tu-van`, và có luật `.nx-doi-tu-van .nx-advisor { display: grid }` —
cùng độ ưu tiên 0,2,0 với `.nx-advisor[hidden]`, nên **thứ tự tệp quyết định**,
rất mong manh.

Đo được trước khi sửa: thẻ Bắc Ninh có `hidden=true` **mà `display: grid`**,
chiều cao 115px → vẫn hiện. Sau khi sửa: `display: none`, chiều cao 0.

**Quy ước cũ của dự án là thêm `&[hidden] { display: none }` cho TỪNG phần tử bị
ẩn** — đã có 6 luật như vậy (`.nx-modal`, `.nx-modal__bao`, `.nx-chon__bang`,
`.nx-chon__tim`, `.nx-pd-video`, `.nx-hop`) và cái thứ 7 bị quên đúng ở
`.nx-advisor`. Kiểu quy ước này hỏng lại được bất cứ lúc nào có người quên.

Đã thay bằng **một luật chung** trong `resources/css/components/_noxh.scss`:

```scss
[hidden] {
    display: none !important;
}
```

Đúng như cách Bootstrap làm (Bootstrap cũng đặt
`[hidden]{display:none!important}`). `!important` làm thứ tự tệp không còn
quan trọng.

Phải vá **cả** `public/build/assets/app-fb4cf9c4.css` (không có `node_modules`
để build lại) — chạy `node scratch/them-css-hidden.cjs`.

> **Cẩn thận khi chạy hai script vá CSS:** `them-css-nut-hero.cjs` gỡ khối cũ
> bằng cách **cắt tệp tại dấu của chính nó**, nên mọi thứ nằm SAU dấu đó sẽ bị
> xoá khi chạy lại. Vì vậy `them-css-hidden.cjs` phải **chèn TRƯỚC** dấu của
> nut-hero, không phải nối xuống cuối. Đã chạy lại nut-hero sau đó và xác nhận
> khối `[hidden]` còn nguyên.

Kiểm chứng: `node scratch/kiem-tra-loc-khu-vuc.cjs "Thái Nguyên"` — chọn Thái
Nguyên còn đúng **4/6** thẻ; `"Bắc Ninh"` còn đúng **1/6**, và thẻ còn lại giữ
**đúng bề ngang một cột** (204px trên màn rộng, 161px ở 390px) chứ không bị kéo
dài hết hàng. Script đo cả hai đường: bấm thật vào dropdown tự vẽ, và đặt
`select.value` rồi phát `change`; thêm `--rong 390` để đo ở bề rộng điện thoại.

### Bản CSS đã biên dịch chứa luật cũ đã bị xoá khỏi nguồn

`public/build/assets/app-fb4cf9c4.css` là tệp **được commit sẵn** và còn được vá
tay, nên nó **lệch khỏi SCSS**. Cụ thể: luật
`.nx-advisors--mot { grid-template-columns: minmax(0,1fr) }` **không còn trong**
`resources/css/components/_noxh-advisor.scss` nữa, nhưng vẫn nằm trong bản đã
biên dịch (**2 chỗ**) và vì thế **vẫn có tác dụng khi chạy** — làm thẻ tư vấn
viên cuối cùng bị kéo rộng hết hàng sau khi lọc.

Đã gỡ bằng `node scratch/xoa-css-cu.cjs`.

> **Bài học:** khi bản CSS đã biên dịch được commit sẵn, xoá luật trong SCSS là
> **chưa đủ** — phải xoá cả trong bản đã biên dịch, nếu không luật cũ vẫn sống
> và rất khó truy: đọc SCSS thì không thấy gì, mà chạy thì thấy sai.

## Khối "Bảng số liệu cạnh banner" đang ẩn

Bốn ô số liệu (120+ dự án / 38 tỉnh / 15250+ tư vấn / 100%) trong banner trang chủ **đang được
ẩn tạm, cả máy tính lẫn điện thoại**.

- Ẩn ở `resources/views/frontend/noxh/home/index.blade.php`: điều kiện đổi thành
  `@if(false && count($soLieu))`. **Muốn hiện lại thì bỏ đoạn `false &&`.**
- Dữ liệu vẫn còn nguyên trong **Cài đặt → Giới thiệu → "Khối 1b"**, không phải nhập lại.
- CSS (`.nx-hero__so`, `.nx-so-the`) giữ nguyên, không xoá.

## Danh sách tư vấn viên trên mobile: 2 người một hàng

Bản thiết kế để 1 cột ở màn hình ≤480px, nhưng thẻ nhân viên chỉ gồm ảnh tròn + tên + nút
nên 2 cột vẫn đọc được, và danh sách 6 người gọn hơn hẳn. Đã đổi:

| Bề rộng | Trước | Sau |
|---|---|---|
| ≤480px | 1 cột | **2 cột** (ảnh 46px, chữ 12.5/11px, nút nhỏ lại) |
| 481–768px | 2 cột | 2 cột, thêm gap 9px |
| ≥769px | 3/6 cột | không đổi |

Đo bằng Chrome giả lập điện thoại (`scratch/kiem-tra-mobile-tuvan.cjs`):

```
320px : 2 x 125px  | moi hang 2 - 2 - 2 | khong tran ngang
360px : 2 x 146px  | moi hang 2 - 2 - 2 | khong tran ngang
390px : 2 x 161px  | moi hang 2 - 2 - 2 | khong tran ngang
768px : 2 x 341px  | moi hang 2 - 2 - 2 | khong tran ngang
1280px: 6 cot      | moi hang 6         | khong doi
```

Lọc theo khu vực mà chỉ còn **1 người** thì thẻ chiếm cả bề ngang (`--mot`, do JS ở
`home/index.blade.php` bật/tắt). Không có class đó thì thẻ nằm nửa hàng, chừa nửa trống.

### ⚠️ CSS phải sửa ở HAI chỗ

Dự án commit `public/build` (máy chủ không cần Node), nên **sửa SCSS thôi là chưa đủ** — phải
sửa cả file CSS đã biên dịch, nếu không thì máy đang chạy vẫn ra giao diện cũ:

1. `resources/css/components/_noxh-advisor.scss` — nguồn
2. `public/build/assets/app-fb4cf9c4.css` — bản đã biên dịch (sửa bằng
   `scratch/sua-css-build.cjs`, script in ra khối cũ và khối mới rồi mới ghi)

Trên máy có Node thì chạy `npm install && npm run build` để sinh lại `public/build` cho khớp;
khi đó script vá tay không cần nữa. File CSS gốc lưu ở `%TEMP%\app-css-backup.css`.

Chú ý khi viết SCSS: **không lồng media query vào trong `.nx-advisor`** — lồng vào thì selector
thành `.nx-advisor .nx-advisor__ten` (sai). Phải viết `.nx-advisors .nx-advisor__ten` ở cấp
ngoài, vì `_noxh-home.scss` cũng đặt `.nx-advisor__than { width: auto }` nên cần độ cụ thể cao
hơn mới thắng.

## Ô Phường/Xã dùng Select2

Ô Phường/Xã là `<select>` do **Select2** quản lý (có class `setupSelect2`), nên có sẵn ô tìm
kiếm — không cần ô input lọc riêng.

**Select2 4.0.0 trong bản này TỰ BỎ DẤU khi tìm**, đã kiểm chứng trên form thật bằng Chrome
(`scratch/kiem-tra-tim-nhieu-tinh.cjs`):

| Tỉnh | Gõ (không dấu) | Kết quả |
|---|---|---|
| Bắc Ninh (99 xã) | `da mai` | Phường Đa Mai |
| Thái Nguyên (92) | `duc xuan` | Phường Đức Xuân |
| Hà Nội (126) | `ba dinh` | Phường Ba Đình |
| TP HCM (168) | `ben thanh` | Phường Bến Thành |
| Đà Nẵng (94) | `hai chau` | Phường Hải Châu |

Gõ từ khoá không tồn tại thì ra "Không tìm thấy phường/xã" — lọc đúng, không phải hiện cả danh
sách. **Không cần hàm bỏ dấu tự viết**, và `select2/compat/matcher` không có trong bản
Select2 này nên đừng dùng.

Ba cái bẫy của Select2 đã phải xử lý (đều đã kiểm chứng):

1. **Select2 không tự gọi AJAX khi khởi tạo** — phải mở dropdown nó mới gọi. Nên nạp danh sách
   bằng `fetch` rồi đưa vào `data`, không dùng `ajax`.
2. **`.val(ma)` trả `null` nếu thẻ `<select>` không có option mang mã đó** → khi mở form sửa
   phải render sẵn option của phường/xã đang lưu (làm ở PHP, biến `$phuongXa` trong controller),
   rồi chỉ cần `trigger('change')`.
3. **Thứ tự nạp script**: script trong `noxh.blade.php` nằm ở giữa trang, Select2 chỉ có ở cuối
   trang (trong `ckeditor.js`; thẻ CDN đặt trước đó nhưng máy không ra Internet thì không nạp
   được). Nếu đợi có Select2 mới gắn sự kiện `change` cho ô Tỉnh/Thành thì **không bao giờ gắn
   được** — phải gắn sự kiện ngay, chỉ đợi Select2 khi khởi tạo (`khiCoSelect2`).

**Về cảnh báo lúc kiểm thử:** nhiều lần tôi tưởng lỗi mà thật ra là lỗi phép thử —
`document.querySelector('.select2-search__field')` lấy ô tìm kiếm **đầu tiên trên trang**, mà
trang có nhiều ô Select2 (Tỉnh/Thành, Chủ đầu tư…). Muốn thử đúng thì lấy ô tìm kiếm của riêng
ô Phường/Xã qua `jQuery('#nx-xa').data('select2').dropdown.$search[0]`.

### Cách kiểm tra lại

```powershell
$php = 'C:\laragon\bin\php\php-8.4.2-nts-Win32-vs17-x64\php.exe'
& $php scratch\kiem-tra-form-dia-gioi.php   # 34 tinh, khong con Quan/Huyen, form sua doc dung
& $php scratch\kiem-tra-luu-du-an.php       # tao du an tam -> luu -> doc lai -> hien ngoai web -> xoa
& $php scratch\kiem-tra-js-cascade.php      # chay JS that tren DOM gia: doi tinh co xoa ma cu khong
& $php scratch\kiem-tra-form-sale.php       # form du an cua nhan vien kinh doanh
```

Kiem tra cascade bang **Chrome that** (chon tinh nhu nguoi dung roi doc lai DOM):

```powershell
& $php scratch\lay-cookie-phien.php                       # lay cookie phien quan tri
$cookie = (Get-Content "$env:TEMP\noxh-cookie.txt")[0]
& 'C:\Program Files\nodejs\node.exe' scratch\kiem-tra-cascade-browser.cjs "$cookie"
```

Do chieu cao cac o (form quan tri, va form nhan vien qua `do-chieu-cao-sale.php`):

```powershell
& 'C:\Program Files\nodejs\node.exe' scratch\do-chieu-cao-o.cjs "$cookie" '/product/76/edit'
& $php scratch\do-chieu-cao-sale.php
```

Chup anh mot khoi cua form de xem bang mat:

```powershell
& 'C:\Program Files\nodejs\node.exe' scratch\chup-anh-form.cjs "$cookie" '/product/76/edit' 'khoi-du-an'
# -> D:\sandbox\khoi-du-an.png
```

Canh bao: script nay **tao du an that** roi xoa, nen chay xong nho kiem tra lai danh sach du an
con dung 6. Va `public/` khong duoc de lai file tam nao.

`kiem-tra-form-sale.php` đặt mật khẩu tạm cho tài khoản nhân viên demo rồi **khôi phục lại
nguyên hash cũ** trong khối `finally`, nên không để lại thay đổi trên tài khoản đó.

## Ô "Giá dự kiến" theo loại căn hộ không tự khớp

Trang chi tiết đọc **ba nguồn giá độc lập**, không có gì đồng bộ chúng:

| Hiển thị ở đâu | Lấy từ |
|---|---|
| Thẻ giá + dòng "Giá" trong bảng tổng quan | `products.price_from` / `price_to` (triệu/m²) |
| Bảng "Giá bán & các loại căn hộ" (từng dòng) | `project_units.price_from` / `price_to` + `price_unit` |

6 dự án hiện có bộ `project_units` **giống hệt nhau** (do `NoxhProjectDetailDemoSeeder`
sinh hàng loạt: 1PN–1WC 1,075–1,180 tỷ; 2PN–1WC 1,180–1,260 tỷ; 2PN–2WC 1,280–1,420 tỷ),
nên giá quy đổi ra ~55 triệu/m² trong khi dự án ghi 16,8–21,3 triệu/m². Muốn đúng thì
phải nhập lại ở module **Loại căn hộ** (`/project/unit/index?product_id=…`).

## Sao lưu

`D:\sandbox\db\backup-truoc-seeder.sql` — bản dump trước khi chạy thử toàn bộ
`NoxhSeeder` (1,74 MB). Xoá được nếu không cần nữa.
