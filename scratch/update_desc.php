<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$desc = '<h2>Màn hình android oto OLED Pro X8S</h2>
<p><strong>Giải pháp cho cuộc sống hiện đại</strong></p>
<p>“Dẫn lối tiên phong” chính là slogan khẳng định sự cao cấp tuyệt đối của màn hình DVD Android Oled Pro X8S. Một siêu phẩm trong làng màn hình DVD ô tô mà số đông khách hàng khi nhìn thấy và được trải nghiệm sẽ phải trầm trồ về kiểu dáng, phong cách, cấu hình đỉnh cao và hệ tính năng siêu khủng mà nó đem lại cho giới tài xế Việt.</p>
<p>“Sinh sau đẻ muộn” nhưng Oled Pro X8S đã có sự kế thừa và phát triển những tính năng xịn sò của thế hệ “đàn anh” đi trước để giờ đây có được cho mình những tính năng vượt trội hơn hẳn. Xứng đáng là chiếc màn hình DVD dẫn đầu thi trường và đáng lắp đặt nhất hiện nay.</p>
<h3>Hệ thống camera 360 ghi hình toàn cảnh</h3>
<p>Màn hình DVD Android cao cấp Oled Pro X8S được trang bị 4 mắt camera Sony 225. Chúng được lắp tại 4 vị trí trên xe: Đầu xe, đuôi xe và 2 bên gương chiếu hậu. Giúp thu lại hình ảnh hành trình xe một cách toàn cảnh, rõ nét và chân thực nhất.</p>
<p>Bên cạnh đó, hệ thống camera 360 ở Oled Pro X8S sẽ được gắn trực tiếp vào màn hình chứ không cần thông qua hộp tổng CMU. Nhờ vậy mà hình ảnh được truyền tới thiết bị một cách siêu nhanh chóng, bởi nó không cần mất quá nhiều thời gian xử lý. Từ đó, giúp bạn có thể nhanh chóng nắm bắt tình hình và có được những hành trình lái xe an toàn hơn.</p>
<h3>Cấu hình siêu khủng – Tốc độ xử lý siêu mượt</h3>
<p>Không hổ danh là chiếc màn hình DVD Android đẳng cấp nhất tại Việt Nam hiện nay. Oled Pro X8S sở hữu một cấu hình cực khủng, với: RAM 6GB, ROM 128GB, HĐH Android 10.0, Chipset (CPU) ARM Cortex 8 nhân 65 bit… cho khả năng hoạt động và tốc độ xử lý siêu nhanh, siêu mượt…</p>
<p>Nếu bạn là người sành nghe nhạc và thường xuyên thích thưởng thức âm nhạc khi lái xe. Thì bạn sẽ ngay lập tức nhận ra sự khác biệt về chất lượng âm thanh của màn hình DVD Android Oled Pro X8S cao cấp so với những chiếc màn hình DVD khác. Bởi ở X8S có hỗ trợ chức năng Audio Delay và lọc âm. Để có thể dễ dàng thu và tái tạo lại những âm thanh chất lượng, trung thực nhất, loại bỏ tạp âm siêu đỉnh.</p>
<p>Cùng hệ thống âm thanh 6 loa, tích hợp DSP, Oled Pro X8S sẽ đem tới bạn những âm thanh cực đã tai, loại bỏ hạn chế không gian nhỏ hẹp.</p>';

$affected = DB::table('product_language')
    ->where('canonical', 'man-hinh-android-zestech-z800-pro')
    ->update(['description' => $desc]);

echo "Updated lines: " . $affected . "\n";
