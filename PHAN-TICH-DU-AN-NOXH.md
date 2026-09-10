# NOXH.vn — Phân tích cơ sở dữ liệu và module cần phát triển

> Căn cứ: 9 ảnh mô phỏng trong `sanbox/work/noxh/` và mã nguồn hiện tại (clone từ `truc`,
> Laravel + module tự viết, CSDL `sql_noxh`).
> Ngày phân tích: 10/09/2026.

---

## 1. Dự án này là gì

NOXH.vn là **cổng thông tin nhà ở xã hội**, không phải website bán hàng. Ba việc chính:

1. **Tra cứu dự án NOXH** trên toàn quốc — lọc theo tỉnh, trạng thái, giá/m², diện tích
2. **Kiểm tra điều kiện mua** — bộ 8 câu hỏi, chấm điểm, ra kết quả có mã tra cứu
3. **Thu lead và tư vấn** — mọi màn hình đều có form/CTA đẩy về chuyên viên

Nguồn thu không đến từ giao dịch trên site mà từ **lead**. Điều này quyết định trọng tâm
kỹ thuật: dữ liệu dự án phải lọc được, và luồng lead phải chặt.

## 2. Chín màn hình → chức năng

| # | Màn hình | Chức năng cốt lõi |
|---|---|---|
| 1 | Trang chủ | Ô tìm dự án (tỉnh / trạng thái / khoảng giá), số liệu tổng, 4 dự án nổi bật, 5 nhóm thông tin hữu ích, câu hỏi nổi bật, tin mới, form nhận tin |
| 2 | Pháp lý NOXH | 5 chủ đề pháp lý, bài viết pháp lý, **thư viện văn bản pháp luật tải về** (số hiệu, ngày hiệu lực, PDF/DOC), form gửi câu hỏi |
| 3 | Danh sách dự án | Bộ lọc 4 nhóm **có đếm số lượng**, sắp xếp, phân trang, **bản đồ có ghim + chip tỉnh kèm số dự án** |
| 4 | Chi tiết dự án | 10 tab, bảng thông số, **công cụ tính giá dự kiến**, loại căn hộ, timeline tiến độ, hồ sơ pháp lý, FAQ riêng, form lead |
| 5 | Kiểm tra điều kiện (mở đầu) | Màn hình đồng ý chính sách bảo mật trước khi hỏi |
| 6 | Kiểm tra điều kiện (form) | 8 câu chia 4 tab, kết quả sơ bộ cập nhật trực tiếp bên cạnh |
| 7 | Kết quả kiểm tra | Vòng tròn %, **mã kết quả + hạn 30 ngày**, bảng 8 tiêu chí Đạt/Cần kiểm tra, dự án gợi ý, 4 bước tiếp theo |
| 8 | Chi tiết tin tức | 7 chuyên mục, tag, chia sẻ, bài liên quan, hộp chuyên gia |
| 9 | Trang chủ (bản 2) | Trùng với #1 |

## 3. Hiện trạng CSDL — cái gì dùng lại được

Đếm thực tế trong `sql_noxh` (68 bảng):

### Dùng lại được ngay

| Bảng | Số dòng | Dùng cho |
|---|---|---|
| `provinces` / `districts` / `wards` | 63 / 705 / **10.598** | Địa chỉ dự án, bộ lọc tỉnh, chip tỉnh trên bản đồ |
| `posts` + `post_catalogues` + `post_language` | 17 / 4 | Tin tức, bài pháp lý (7 chuyên mục ở màn hình #8) |
| `products` + `product_catalogues` + `product_language` | 69 / 7 | **Dự án NOXH** — module sẽ tái sử dụng |
| `attributes` + `attribute_catalogues` | 24 / 7 | Bộ lọc động (đúng như định hướng) |
| `menus` + `menu_catalogues` | 35 / 5 | Menu điều hướng |
| `systems` | 158 | Cấu hình chung, hotline, script |
| `widgets` / `slides` | 17 / 7 | Khối trang chủ |
| `contacts` | 1 | Lead — nhưng cần mở rộng, xem mục 5 |
| `users` / `user_catalogues` / `permissions` | 5 / 4 / 153 | Phân quyền admin |
| `routers` | 49 | Định tuyến canonical |

**Kết luận:** khung xương dùng lại được khoảng 70%. Hạ tầng đa ngôn ngữ, phân quyền,
nested set danh mục, router canonical đều đã có và chạy tốt.

### Bỏ đi — hành lý thương mại điện tử

`orders`, `order_product`, `order_paymentable`, `vouchers` (+5 bảng con), `promotions`
(+4 bảng con), `combo_products`, `customer_point_history`, `reviews`, `sources`.
NOXH không bán hàng trên site.

### Bỏ đi — module của dự án khác

`admission`, `scholar`, `school`, `major`, `lecturer`, `workshops`, `departments`,
`policies`, `distributions`, `distribution_areas`. Đây là vết tích của các bản trước.

---

## 4. Khoảng trống CSDL — đây là phần quan trọng nhất

### 4.1. `products` không có một trường nào để lọc dự án

Cột hiện có của `products`:

```
id, product_catalogue_id, image, icon, album, publish, follow, order, user_id,
deleted_at, created_at, updated_at, code, made_in, price, combo_price, stock,
attributeCatalogue, attribute, variant, qrcode, warranty, check, iframe,
seller_id, link, total_lesson, duration, lession_content, chapter, percent,
ml, promotion_content, no_offer
```

Đối chiếu với màn hình #3 và #4, **thiếu toàn bộ** các trường sau:

| Cần có | Kiểu | Dùng ở |
|---|---|---|
| `province_id`, `district_id`, `ward_id` | FK | Lọc tỉnh, hiển thị địa chỉ, ghim bản đồ |
| `latitude`, `longitude` | decimal | Bản đồ dự án (#3) |
| `status` | enum | Badge: đang nhận hồ sơ / sắp mở bán / đang triển khai / đã bàn giao |
| `price_min`, `price_max` | decimal | Giá **19,55 – 23,99 triệu/m²** — hiện `price` chỉ 1 giá trị |
| `area_min`, `area_max` | decimal | Diện tích căn **32 – 70 m²** |
| `total_units` | int | "1.042 căn" |
| `total_land_area` | decimal | "8,12 ha" |
| `investor_id` | FK | Chủ đầu tư (bảng mới, mục 5.1) |
| `ownership_type` | string | "Sở hữu 50 năm" |
| `start_date`, `handover_date` | date | Khởi công / bàn giao dự kiến |
| `is_featured` | bool | "Dự án nổi bật" trang chủ |

> **Lưu ý về giá:** mockup dùng **khoảng giá**, không phải giá đơn. Bộ lọc "Mức giá:
> dưới 18 / 18–20 / 20–22 / trên 22" phải so với khoảng, nên bắt buộc có 2 cột. Cố nhét
> vào cột `price` hiện tại sẽ phải sửa lại toàn bộ về sau.

### 4.2. Thuộc tính không gắn được vào sản phẩm

Đây là vấn đề chặn bộ lọc động mà anh nói tới.

Hiện tại thuộc tính chỉ gắn vào **biến thể**:

```
attributes ── attribute_catalogue_attribute ── attribute_catalogues
    │
    └── product_variant_attribute ── product_variants ── products
```

**Không tồn tại bảng `product_attribute`.** Muốn lọc dự án theo thuộc tính thì hoặc phải
tạo biến thể giả cho từng dự án, hoặc đọc cột `products.attribute` — cột này là chuỗi
JSON, không JOIN được, và hiện **rỗng ở cả 69 dòng**.

**Cần tạo bảng pivot `product_attribute` (`product_id`, `attribute_id`)** rồi đánh index.
Đây là điều kiện tiên quyết cho bộ lọc có đếm số lượng như màn hình #3.

Ngoài ra `attribute_catalogues` cần thêm cột điều khiển hiển thị bộ lọc:
`filterable` (có hiện ở sidebar không), `filter_type` (checkbox / range / select),
`display_order`.

Dữ liệu thuộc tính hiện tại là của **shop rượu vang** (Màu sắc, Vùng, Giống nho, Loại rượu,
Nhà sản xuất, Dung tích, Nồng độ cồn) — xoá sạch, thay bằng nhóm thuộc tính NOXH.

### 4.3. `product_variants` bỏ trống — nên tái sử dụng cho loại căn hộ

Màn hình #4 có "Giá bán & các loại căn hộ": CĂN 1PN-1WC, 2PN-1WC, 2PN-2WC, mỗi loại có
mặt bằng riêng, khoảng diện tích, khoảng giá, danh sách đặc điểm.

Đây **chính là** biến thể. Bảng `product_variants` (0 dòng) và `product_variant_attribute`
(0 dòng) đã có sẵn cấu trúc. Cần bổ sung cột: `area_min`, `area_max`, `price_min`,
`price_max`, `bedrooms`, `bathrooms`, `floorplan_image`, `features` (JSON).

Tận dụng được cả công cụ "Tìm giá bán dự kiến" ở màn hình #4 — nó chính là chọn biến thể
rồi nhân diện tích với đơn giá.

### 4.4. `contacts` quá mỏng cho luồng lead

Cột hiện có: `id, name, phone, address, gender, product_id, post_id, publish, created_at,
updated_at, deleted_at, type, message, email`.

Mockup có **4 điểm thu lead khác nhau**, mỗi điểm cần ngữ cảnh riêng:

- Form sidebar chi tiết dự án (có "Nhu cầu quan tâm" — loại căn hộ)
- "Đăng ký tư vấn" trên thẻ dự án ở màn hình #3
- Kết quả kiểm tra điều kiện (#7) — phải gắn với bản ghi kết quả
- Form nhận tin trang chủ (chỉ tên + SĐT)

Cần thêm: `source` (màn hình nào), `variant_id` (quan tâm loại căn nào),
`eligibility_check_id`, `assigned_user_id` (chuyên viên phụ trách), `status`
(mới / đã gọi / đang tư vấn / thành công / huỷ), `note`, `contacted_at`.

Không có `status` và `assigned_user_id` thì sale không dùng được — sẽ quay lại xuất Excel
làm tay, và toàn bộ giá trị của cổng thông tin nằm ở chỗ này.

---

## 5. Module cần phát triển thêm

Xếp theo mức độ chặn.

### 5.1. Module Dự án (nâng cấp từ Products) — **bắt buộc**

Sửa `products` theo mục 4.1 + 4.3. Trang admin cần thêm tab:

- **Vị trí**: tỉnh / huyện / xã (đã có sẵn 10.598 phường xã), toạ độ, nhúng bản đồ
- **Thông số**: quy mô, số căn, tổng diện tích, hình thức sở hữu, mốc thời gian
- **Loại căn hộ**: quản lý biến thể + mặt bằng từng loại
- **Tiến độ**: timeline các mốc (`project_milestones`: `title`, `date`, `status`, `image`)
- **Pháp lý dự án**: `project_documents` (`title`, `doc_number`, `issued_date`, `file`, `type`)
- **Tiện ích**: dùng `product_attribute` mới
- **FAQ dự án**: `project_faqs` (`question`, `answer`, `order`)

Bảng mới cần tạo: `investors` (chủ đầu tư: tên, logo, hotline, email, website, địa chỉ) —
màn hình #4 có hẳn khối "Thông tin liên hệ dự án".

### 5.2. Module Kiểm tra điều kiện — **bắt buộc, làm mới hoàn toàn**

Không có gì tái sử dụng được. Cần 4 bảng:

| Bảng | Nội dung |
|---|---|
| `eligibility_questions` | Nội dung câu hỏi, nhóm/tab (nhà ở / thu nhập / đối tượng / điều kiện khác), kiểu (boolean/select), thứ tự, tooltip, bắt buộc hay không |
| `eligibility_options` | Đáp án cho câu select, kèm điểm và kết luận (đạt / cần kiểm tra / không đạt) |
| `eligibility_checks` | Mỗi lượt kiểm tra: mã kết quả (`NOXH-240527-1530`), điểm %, số tiêu chí đạt, hạn 30 ngày, IP, đồng ý chính sách |
| `eligibility_answers` | Câu trả lời từng câu của một lượt |

Điểm cần bàn kỹ: **quy tắc chấm điểm phải sửa được trong admin**, vì điều kiện mua NOXH
thay đổi theo nghị định (mockup #8 nhắc Nghị định 136/2026 nâng mức thu nhập). Nếu
hard-code trong PHP thì mỗi lần đổi chính sách lại phải sửa code và deploy.

### 5.3. Module Văn bản pháp luật — **bắt buộc**

Màn hình #2 có thư viện văn bản: số hiệu, loại (Luật/Nghị định/Thông tư/Công văn), ngày
hiệu lực, mô tả, file tải về, đếm lượt tải.

Bảng `legal_documents`. Không dùng `posts` được vì cần trường số hiệu và ngày hiệu lực để
sắp xếp và lọc.

### 5.4. Module Hỏi đáp — **bắt buộc**

Màn hình #1 và #4 đều có. Người dùng đặt câu hỏi, chuyên gia trả lời, đếm lượt xem, có
trạng thái duyệt.

Bảng `questions` + `answers`. Có thể gắn vào dự án (`product_id`) để làm mục "Hỏi đáp" ở
tab thứ 9 của màn hình #4.

### 5.5. Module Chuyên gia — **nên có**

"Công Hòa", "Nguyễn Hòa – Cố vấn Pháp lý" xuất hiện ở 6/9 màn hình với ảnh, chức danh,
hotline, danh sách cam kết. Hiện đang phải hard-code.

Bảng `experts`. Nhẹ nhưng xuất hiện dày, để trong `systems` sẽ rối.

### 5.6. Nâng cấp Quản lý lead — **bắt buộc**

Theo mục 4.4. Cần thêm màn hình danh sách lead có lọc theo trạng thái, gán chuyên viên,
ghi chú, và xuất Excel.

### 5.7. Bộ lọc động — **bắt buộc**

Sau khi có `product_attribute`, cần:

- Admin: đánh dấu nhóm thuộc tính nào hiện ở bộ lọc, kiểu hiển thị, thứ tự
- Frontend: đếm số lượng theo từng lựa chọn (như "Đang nhận hồ sơ **19**")

> Cảnh báo kỹ thuật: đếm số lượng cho mỗi lựa chọn dễ sinh N+1 nếu làm ngây thơ. Nên gom
> về một truy vấn `GROUP BY` duy nhất, hoặc cache theo bộ tham số lọc.

### 5.8. Bản đồ dự án — **nên có**

Màn hình #3 có bản đồ Việt Nam với ghim và chip tỉnh kèm số lượng. Cần `latitude`,
`longitude` trên `products` và một endpoint trả JSON toàn bộ ghim.

---

## 6. Dữ liệu và ảnh cần dọn

Toàn bộ nội dung hiện tại là **của shop rượu vang / phụ kiện ô tô**, không liên quan NOXH:

- 69 sản phẩm, 7 danh mục sản phẩm
- 17 bài viết, 4 chuyên mục
- 24 thuộc tính (Giống nho, Nồng độ cồn, Dung tích...)
- 35 mục menu, 7 slide, 17 widget

Về ảnh trong `public/userfiles` (147 file, 21,4 MB), kết quả quét đối chiếu với CSDL và
toàn bộ mã nguồn:

| | File | Dung lượng |
|---|---|---|
| Không nơi nào tham chiếu | 53 | 6,6 MB |
| Đang được tham chiếu | 94 | 14,8 MB |

**Nhưng 94 file "đang dùng" kia là ảnh rượu vang, whisky, pha chế** — chúng chỉ "đang dùng"
vì còn 69 sản phẩm rượu trong CSDL. Với dự án NOXH thì cả 147 file đều là rác.

Vì vậy câu hỏi thật không phải "xoá ảnh thừa" mà là **có xoá sạch dữ liệu nội dung để bắt
đầu từ trắng không**. Tôi khuyên có, giữ lại:

- Cấu trúc bảng và migration
- `users`, `user_catalogues`, `permissions` (tài khoản admin)
- `provinces`, `districts`, `wards` (10.598 dòng địa giới — làm lại rất mất công)
- `languages`, `systems` (dọn lại giá trị)

Tôi **chưa xoá gì cả** — cần anh xác nhận trước, vì thao tác này không hoàn tác được bằng
Ctrl+Z. Sao lưu trước thì có `sanbox/db/sql_noxh.sql`.

---

## 7. Thứ tự đề xuất

| Giai đoạn | Nội dung | Vì sao trước |
|---|---|---|
| 1 | Dọn dữ liệu + gỡ module thừa | Làm sau sẽ vướng dữ liệu rác khi test |
| 2 | Mở rộng `products` (địa lý, giá/diện tích khoảng, trạng thái) + `investors` | Mọi màn hình đều phụ thuộc |
| 3 | `product_attribute` + bộ lọc động | Chặn màn hình #3 |
| 4 | Biến thể loại căn hộ + tiến độ + pháp lý + FAQ dự án | Hoàn thiện màn hình #4 |
| 5 | Module Kiểm tra điều kiện | Khối lượng lớn nhất, độc lập, làm song song được |
| 6 | Văn bản pháp luật + Hỏi đáp + Chuyên gia | Nội dung phụ trợ |
| 7 | Nâng cấp lead + bản đồ | Sau khi đã có dữ liệu thật để thử |

Giai đoạn 2 và 3 nên làm cùng một đợt migration để chỉ phải đụng vào bảng `products`
một lần.

---

## 8. Ba điểm cần quyết trước khi viết code

1. **Xoá sạch dữ liệu cũ hay giữ lại làm mẫu?** Ảnh hưởng tới toàn bộ giai đoạn 1.

2. **Quy tắc chấm điểm điều kiện đặt ở đâu?** Trong CSDL (sửa được trong admin, phức tạp
   hơn) hay trong code (nhanh hơn, mỗi lần đổi nghị định phải deploy). Mockup #8 cho thấy
   chính sách thay đổi theo năm, nên tôi nghiêng về đặt trong CSDL.

3. **Một dự án thuộc bao nhiêu danh mục?** Bảng `product_catalogue_product` hiện là
   nhiều-nhiều (81 liên kết / 69 sản phẩm). Với dự án NOXH thì "danh mục" nên hiểu là gì —
   theo tỉnh, theo trạng thái, hay theo phân khúc? Nếu chỉ theo tỉnh/trạng thái thì đã có
   cột riêng rồi, không cần danh mục nữa.
