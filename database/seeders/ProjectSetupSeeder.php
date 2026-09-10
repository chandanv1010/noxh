<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ProjectSetupSeeder extends Seeder
{
    public function run()
    {
        // Mock authentication for Nestedsetbie so user_id is set to 1
        Auth::loginUsingId(1);

        // 1. Clean existing tables
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        DB::table('products')->truncate();
        DB::table('product_language')->truncate();
        DB::table('product_catalogue_product')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('product_variant_language')->truncate();
        DB::table('product_variant_attribute')->truncate();
        DB::table('product_catalogues')->truncate();
        DB::table('product_catalogue_language')->truncate();
        
        DB::table('posts')->truncate();
        DB::table('post_language')->truncate();
        DB::table('post_catalogue_post')->truncate();
        DB::table('post_catalogues')->truncate();
        DB::table('post_catalogue_language')->truncate();
        
        DB::table('menus')->truncate();
        DB::table('menu_language')->truncate();
        DB::table('combo_products')->truncate();
        DB::table('promotion_product_variant')->truncate();
        DB::table('order_product')->truncate();
        DB::table('reviews')->truncate();
        DB::table('routers')->truncate();
        
        // Remove slides commit, commit-2 & banner-home
        DB::table('slides')->whereIn('keyword', ['commit', 'commit-2', 'banner-home'])->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $this->command->info('Database cleaned successfully.');

        // 2. Setup Product Categories
        $productCategories = [
            ['name' => 'Camera nghị định AI', 'slug' => 'camera-nghi-dinh-ai', 'icon' => '/userfiles/image/icons/camera-ai.png'],
            ['name' => 'Camera hành trình', 'slug' => 'camera-hanh-trinh', 'icon' => '/userfiles/image/icons/camera-hanh-trinh.png'],
            ['name' => 'Camera 360', 'slug' => 'camera-360', 'icon' => '/userfiles/image/icons/camera-360.png'],
            ['name' => 'Màn hình Android', 'slug' => 'man-hinh-android', 'icon' => '/userfiles/image/icons/man-hinh-android.png'],
            ['name' => 'Định vị Gps', 'slug' => 'dinh-vi-gps', 'icon' => '/userfiles/image/icons/dinh-vi-gps.png'],
            ['name' => 'Cảm biến', 'slug' => 'cam-bien', 'icon' => '/userfiles/image/icons/cam-bien.png'],
            ['name' => 'Thiết bị công nghệ', 'slug' => 'thiet-bi-cong-nghe', 'icon' => '/userfiles/image/icons/thiet-bi-cong-nghe.png'],
        ];

        $orderIndex = 1;
        foreach ($productCategories as $cat) {
            $catId = DB::table('product_catalogues')->insertGetId([
                'parent_id' => 0,
                'lft' => 0,
                'rgt' => 0,
                'level' => 1,
                'image' => null,
                'icon' => $cat['icon'],
                'album' => null,
                'publish' => 2,
                'follow' => 1,
                'order' => $orderIndex++,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_catalogue_language')->insert([
                'product_catalogue_id' => $catId,
                'language_id' => 1,
                'name' => $cat['name'],
                'canonical' => $cat['slug'],
                'url' => $cat['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('routers')->insert([
                'canonical' => $cat['slug'],
                'module_id' => $catId,
                'language_id' => 1,
                'controllers' => 'App\Http\Controllers\Frontend\ProductCatalogueController',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Run nestedset for product_catalogues
        $nestedsetProduct = new \App\Classes\Nestedsetbie([
            'table' => 'product_catalogues',
            'foreignkey' => 'product_catalogue_id',
            'language_id' => 1,
        ]);
        $nestedsetProduct->Get();
        $nestedsetProduct->Recursive(0, $nestedsetProduct->Set());
        $nestedsetProduct->Action();

        $this->command->info('Product categories seeded and indexed successfully.');

        // 2.5. Seed realistic products for categories (Android Screen, Dashcam, 360 Camera, GPS)
        $demoProducts = [
            4 => [ // Màn hình Android
                ['name' => 'MÀN HÌNH ANDROID OTO OLED PRO X8S', 'price' => 19890000, 'old_price' => 21590000, 'warranty' => 12],
                ['name' => 'Màn hình Android OLED PRO X5', 'price' => 13500000, 'old_price' => 15000000, 'warranty' => 12],
                ['name' => 'Màn hình Android OLED PRO X4S', 'price' => 11200000, 'old_price' => 13000000, 'warranty' => 12],
                ['name' => 'Màn hình ô tô thông minh OLED PRO Ultra', 'price' => 24500000, 'old_price' => 27000000, 'warranty' => 24],
                ['name' => 'Màn hình liền camera 360 OLED PRO X8S Plus', 'price' => 22890000, 'old_price' => 25000000, 'warranty' => 18],
                ['name' => 'Màn hình Android Gotech GT8 Max', 'price' => 12800000, 'old_price' => 14000000, 'warranty' => 12],
                ['name' => 'Màn hình Android Bravigo Air 2', 'price' => 9500000, 'old_price' => 11000000, 'warranty' => 12],
                ['name' => 'Màn hình Android Zestech Z800 Pro', 'price' => 15000000, 'old_price' => 16500000, 'warranty' => 24],
            ],
            2 => [ // Camera hành trình
                ['name' => 'Camera hành trình JC400 ghi hình trước sau', 'price' => 4500000, 'old_price' => 5200000, 'warranty' => 12],
                ['name' => 'Camera hành trình JC400P thế hệ mới', 'price' => 4890000, 'old_price' => 5500000, 'warranty' => 12],
                ['name' => 'Camera hành trình JC261 AI thông minh', 'price' => 3800000, 'old_price' => 4200000, 'warranty' => 12],
                ['name' => 'Camera hành trình Vietmap KC01 cảnh báo giao thông', 'price' => 3290000, 'old_price' => 3800000, 'warranty' => 12],
                ['name' => 'Camera hành trình Xiaomi 70mai A800S 4K', 'price' => 2990000, 'old_price' => 3500000, 'warranty' => 12],
                ['name' => 'Camera hành trình Vietmap TS-2K siêu nét', 'price' => 2690000, 'old_price' => 3100000, 'warranty' => 12],
                ['name' => 'Camera hành trình HP F960x WiFi GPS', 'price' => 1850000, 'old_price' => 2200000, 'warranty' => 12],
                ['name' => 'Camera hành trình JC100 đa năng', 'price' => 2500000, 'old_price' => 2900000, 'warranty' => 12],
            ],
            3 => [ // Camera 360
                ['name' => 'Camera 360 Ô tô Safeview 3D Lux', 'price' => 16500000, 'old_price' => 18500000, 'warranty' => 24],
                ['name' => 'Camera 360 Elliview V5-S thông minh', 'price' => 13800000, 'old_price' => 15500000, 'warranty' => 24],
                ['name' => 'Camera 360 Panorama 3D Pro siêu nét', 'price' => 9890000, 'old_price' => 11500000, 'warranty' => 12],
                ['name' => 'Camera 360 DCT bản T1 chuyên nghiệp', 'price' => 12000000, 'old_price' => 13500000, 'warranty' => 24],
                ['name' => 'Camera 360 DCT bản T3 cao cấp', 'price' => 18800000, 'old_price' => 20500000, 'warranty' => 24],
                ['name' => 'Camera 360 Owin 3D Sony 2K', 'price' => 14500000, 'old_price' => 16000000, 'warranty' => 12],
                ['name' => 'Camera 360 Zestech Z311 hiển thị 3D', 'price' => 17000000, 'old_price' => 19000000, 'warranty' => 24],
                ['name' => 'Camera 360 Gotech GP6 quan sát toàn cảnh', 'price' => 12500000, 'old_price' => 14000000, 'warranty' => 12],
            ],
            5 => [ // Định vị GPS
                ['name' => 'Thiết bị Định vị Gps Wetrack Lite siêu nhỏ', 'price' => 1200000, 'old_price' => 1500000, 'warranty' => 12],
                ['name' => 'Định vị Gps không dây AT4 pin khủng 30 ngày', 'price' => 2500000, 'old_price' => 2900000, 'warranty' => 12],
                ['name' => 'Thiết bị định vị GPS ô tô xe máy GT06N', 'price' => 1600000, 'old_price' => 1900000, 'warranty' => 12],
                ['name' => 'Thiết bị định vị OBD giám sát hành trình', 'price' => 1450000, 'old_price' => 1700000, 'warranty' => 12],
                ['name' => 'Định vị GPS siêu nhỏ Wetrack 2', 'price' => 1350000, 'old_price' => 1600000, 'warranty' => 12],
                ['name' => 'Thiết bị định vị nghe lén không dây X8', 'price' => 950000, 'old_price' => 1200000, 'warranty' => 12],
                ['name' => 'Định vị GPS quản lý xe doanh nghiệp H02', 'price' => 1800000, 'old_price' => 2100000, 'warranty' => 24],
                ['name' => 'Thiết bị giám sát hành trình hộp đen hợp chuẩn', 'price' => 2800000, 'old_price' => 3200000, 'warranty' => 12],
            ],
        ];

        $pOrder = 1;
        foreach ($demoProducts as $catId => $products) {
            foreach ($products as $p) {
                $slug = Str::slug($p['name']);
                $productId = DB::table('products')->insertGetId([
                    'product_catalogue_id' => $catId,
                    'image' => 'userfiles/image/product/oled_pro_x8s.png',
                    'publish' => 2,
                    'follow' => 1,
                    'order' => $pOrder++,
                    'user_id' => 1,
                    'price' => $p['price'],
                    'combo_price' => $p['old_price'],
                    'warranty' => $p['warranty'],
                    'stock' => 99,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('product_language')->insert([
                    'product_id' => $productId,
                    'language_id' => 1,
                    'name' => $p['name'],
                    'canonical' => $slug,
                    'description' => "Thiết bị ô tô chất lượng cao, " . $p['name'] . " mang lại trải nghiệm tối ưu cho người lái xe.",
                    'content' => "<p>Sản phẩm chính hãng chất lượng cao bảo hành chính hãng.</p>",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Pivot connection
                DB::table('product_catalogue_product')->insert([
                    'product_catalogue_id' => $catId,
                    'product_id' => $productId,
                ]);

                // Router entry
                DB::table('routers')->insert([
                    'canonical' => $slug,
                    'module_id' => $productId,
                    'language_id' => 1,
                    'controllers' => 'App\Http\Controllers\Frontend\ProductController',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->command->info('Demo products seeded successfully.');

        // 2.6 Setup homepage-categories widget
        DB::table('widgets')->where('keyword', 'homepage-categories')->delete();
        DB::table('widgets')->insert([
            'name' => 'Sản phẩm danh mục trang chủ',
            'keyword' => 'homepage-categories',
            'model' => 'ProductCatalogue',
            'model_id' => json_encode(['4', '2', '3', '5']),
            'publish' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->command->info('Homepage categories widget seeded.');

        // 3. Setup Post Categories
        $postCategories = [
            ['name' => 'Tin tức', 'slug' => 'tin-tuc'],
            ['name' => 'Giới thiệu', 'slug' => 'gioi-thieu'],
        ];

        $tinTucCatalogueId = null;
        foreach ($postCategories as $cat) {
            $catId = DB::table('post_catalogues')->insertGetId([
                'parent_id' => 0,
                'lft' => 0,
                'rgt' => 0,
                'level' => 1,
                'publish' => 2,
                'user_id' => 1,
                'follow' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('post_catalogue_language')->insert([
                'post_catalogue_id' => $catId,
                'language_id' => 1,
                'name' => $cat['name'],
                'canonical' => $cat['slug'],
                'url' => $cat['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('routers')->insert([
                'canonical' => $cat['slug'],
                'module_id' => $catId,
                'language_id' => 1,
                'controllers' => 'App\Http\Controllers\Frontend\PostCatalogueController',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($cat['slug'] === 'tin-tuc') {
                $tinTucCatalogueId = $catId;
            }
        }

        // Run nestedset for post_catalogues
        $nestedsetPost = new \App\Classes\Nestedsetbie([
            'table' => 'post_catalogues',
            'foreignkey' => 'post_catalogue_id',
            'language_id' => 1,
        ]);
        $nestedsetPost->Get();
        $nestedsetPost->Recursive(0, $nestedsetPost->Set());
        $nestedsetPost->Action();

        $this->command->info('Post categories seeded and indexed successfully.');

        // 4. Seed ~10 demo posts in Tin tức
        if ($tinTucCatalogueId) {
            for ($i = 1; $i <= 10; $i++) {
                $postName = "Bài viết tin tức demo số $i";
                $slug = Str::slug($postName);
                
                $postId = DB::table('posts')->insertGetId([
                    'image' => null,
                    'publish' => 2,
                    'follow' => 1,
                    'order' => $i,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('post_language')->insert([
                    'post_id' => $postId,
                    'language_id' => 1,
                    'name' => $postName,
                    'canonical' => $slug,
                    'description' => "Mô tả ngắn cho bài viết demo số $i về các sản phẩm công nghệ camera định vị.",
                    'content' => "<p>Đây là nội dung chi tiết bài viết demo số $i giới thiệu về các giải pháp giám sát hành trình hiện đại, camera nghị định hợp chuẩn, và các công nghệ định vị GPS mới nhất trên thị trường.</p>",
                    'meta_title' => $postName,
                    'meta_description' => "Mô tả ngắn cho bài viết demo số $i",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('post_catalogue_post')->insert([
                    'post_id' => $postId,
                    'post_catalogue_id' => $tinTucCatalogueId,
                ]);

                DB::table('routers')->insert([
                    'canonical' => $slug,
                    'module_id' => $postId,
                    'language_id' => 1,
                    'controllers' => 'App\Http\Controllers\Frontend\PostController',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info('Seeded 10 demo posts in Tin tức successfully!');
        }

        // 4.5 Setup homepage-news widget in database to point to $tinTucCatalogueId
        if (isset($tinTucCatalogueId)) {
            DB::table('widgets')->where('keyword', 'homepage-news')->delete();
            DB::table('widgets')->insert([
                'name' => 'Tin tức nổi bật',
                'keyword' => 'homepage-news',
                'model' => 'PostCatalogue',
                'model_id' => json_encode([(string)$tinTucCatalogueId]),
                'publish' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Homepage news widget seeded pointing to catalogue ID ' . $tinTucCatalogueId);
        }

        // 5. Setup Slide commit
        $commitItems = [
            [
                'image' => '/userfiles/image/icons/free-installation.png',
                'name' => 'MIỄN PHÍ LẮP ĐẶT',
                'description' => null,
                'canonical' => null,
                'alt' => 'TẬN NƠI TOÀN QUỐC',
                'window' => ''
            ],
            [
                'image' => '/userfiles/image/icons/return-7days.png',
                'name' => 'ĐỔI TRẢ TRONG VÒNG 7 NGÀY',
                'description' => null,
                'canonical' => null,
                'alt' => 'NẾU LỖI DO NHÀ SẢN XUẤT',
                'window' => ''
            ],
            [
                'image' => '/userfiles/image/icons/support-247.png',
                'name' => 'HỖ TRỢ 24/7',
                'description' => null,
                'canonical' => null,
                'alt' => 'KỸ THUẬT CHUYÊN NGHIỆP',
                'window' => ''
            ]
        ];

        DB::table('slides')->insert([
            'name' => 'Commitment Slide',
            'keyword' => 'commit',
            'description' => 'Slide cam kết chính sách chất lượng dịch vụ',
            'item' => json_encode(['1' => $commitItems], JSON_UNESCAPED_UNICODE),
            'setting' => json_encode([
                'width' => null,
                'height' => null,
                'animation' => 'fade',
                'arrow' => 'accept',
                'navigate' => 'dots',
                'autoplay' => 'accept',
                'pauseHover' => 'accept',
                'animationDelay' => null,
                'animationSpeed' => null
            ]),
            'publish' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->command->info('Slide keyword commit created.');

        $commit2Items = [
            [
                'image' => 'vendor/frontend/img/project/icons/commit-1.png',
                'name' => '15+ NĂM KINH NGHIỆM',
                'description' => null,
                'canonical' => null,
                'alt' => 'Trong lĩnh vực thiết bị giám sát hành trình',
                'window' => ''
            ],
            [
                'image' => 'vendor/frontend/img/project/icons/commit-2.png',
                'name' => '20.000+ KHÁCH HÀNG',
                'description' => null,
                'canonical' => null,
                'alt' => 'Tin dùng trên toàn quốc',
                'window' => ''
            ],
            [
                'image' => 'vendor/frontend/img/project/icons/commit-3.png',
                'name' => '63 TÌNH THÀNH',
                'description' => null,
                'canonical' => null,
                'alt' => 'Lắp đặt & hỗ trợ toàn quốc',
                'window' => ''
            ],
            [
                'image' => 'vendor/frontend/img/project/icons/commit-4.png',
                'name' => '24/7 HỖ TRỢ KỸ THUẬT',
                'description' => null,
                'canonical' => null,
                'alt' => 'Tư vấn và xử lý mọi vấn đề',
                'window' => ''
            ],
            [
                'image' => 'vendor/frontend/img/project/icons/commit-5.png',
                'name' => 'BẢO HÀNH ĐẾN 24 THÁNG',
                'description' => null,
                'canonical' => null,
                'alt' => 'Cam kết chất lượng chính hãng',
                'window' => ''
            ]
        ];

        DB::table('slides')->insert([
            'name' => 'Commitment 2 Slide',
            'keyword' => 'commit-2',
            'description' => 'Slide thống kê & cam kết dịch vụ',
            'item' => json_encode(['1' => $commit2Items], JSON_UNESCAPED_UNICODE),
            'setting' => json_encode([
                'width' => null,
                'height' => null,
                'animation' => 'fade',
                'arrow' => 'accept',
                'navigate' => 'dots',
                'autoplay' => 'accept',
                'pauseHover' => 'accept',
                'animationDelay' => null,
                'animationSpeed' => null
            ]),
            'publish' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->command->info('Slide keyword commit-2 created.');

        $bannerHomeItems = [
            [
                'image' => '/userfiles/image/product/oled_pro_x8s.png',
                'name' => 'ĐĂNG KÝ XE BIỂN VÀNG, BIỂN TRẮNG',
                'description' => null,
                'canonical' => null,
                'alt' => 'NHANH CHÓNG | ĐÚNG QUY ĐỊNH',
                'window' => ''
            ],
            [
                'image' => '/userfiles/image/product/oled_pro_x8s.png',
                'name' => 'GIẤY PHÉP KINH DOANH PHÙ HIỆU XE VẬN TẢI',
                'description' => null,
                'canonical' => null,
                'alt' => 'HỢP PHÁP | UY TÍN | HỖ TRỢ TRỌN GÓI',
                'window' => ''
            ],
            [
                'image' => '/userfiles/image/product/oled_pro_x8s.png',
                'name' => 'PHÙ HIỆU XE Ô TÔ',
                'description' => null,
                'canonical' => null,
                'alt' => 'ĐÚNG QUY ĐỊNH | HỖ TRỢ NHANH | GIÁ TRỊ TOÀN QUỐC',
                'window' => ''
            ],
            [
                'image' => '/userfiles/image/product/oled_pro_x8s.png',
                'name' => 'TRANSIT LIÊN VẬN QUỐC TẾ VIỆT - LÀO',
                'description' => null,
                'canonical' => null,
                'alt' => 'NHANH CHÓNG | TIẾT KIỆM | AN TOÀN | HỖ TRỢ TRỌN GÓI',
                'window' => ''
            ]
        ];

        DB::table('slides')->insert([
            'name' => 'Banner Home Slide',
            'keyword' => 'banner-home',
            'description' => '4 banner dịch vụ ở trang chủ',
            'item' => json_encode(['1' => $bannerHomeItems], JSON_UNESCAPED_UNICODE),
            'setting' => json_encode([
                'width' => null,
                'height' => null,
                'animation' => 'fade',
                'arrow' => 'accept',
                'navigate' => 'dots',
                'autoplay' => 'accept',
                'pauseHover' => 'accept',
                'animationDelay' => null,
                'animationSpeed' => null
            ]),
            'publish' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->command->info('Slide keyword banner-home created.');

        // 7. Setup Footer Menu
        $columns = [
            [
                'title' => 'VỀ CHÚNG TÔI',
                'items' => [
                    ['name' => 'Đại lý', 'canonical' => 'dai-ly'],
                    ['name' => 'Tuyển dụng', 'canonical' => 'tuyen-dung'],
                    ['name' => 'Chính sách Bảo mật', 'canonical' => 'chinh-sach-bao-mat'],
                    ['name' => 'Chính sách Giao dịch', 'canonical' => 'chinh-sach-giao-dich'],
                ]
            ],
            [
                'title' => 'SẢN PHẨM',
                'items' => [
                    ['name' => 'Giải pháp giám sát', 'canonical' => 'giai-phap-giam-sat'],
                    ['name' => 'Định vị giám sát', 'canonical' => 'dinh-vi-giam-sat'],
                    ['name' => 'Camera giám sát hành trình', 'canonical' => 'camera-giam-sat-hanh-trinh'],
                    ['name' => 'Màn hình - Loa', 'canonical' => 'man-hinh-loa'],
                    ['name' => 'Phụ kiện', 'canonical' => 'phu-kien'],
                ]
            ],
            [
                'title' => 'DỊCH VỤ',
                'items' => [
                    ['name' => 'Đăng ký kinh doanh', 'canonical' => 'dang-ky-kinh-doanh'],
                    ['name' => 'Giấy phép vận tải', 'canonical' => 'giay-phep-van-tai'],
                    ['name' => 'Phù hiệu xe', 'canonical' => 'phu-hieu-xe'],
                    ['name' => 'Sang tên xe', 'canonical' => 'sang-ten-xe'],
                ]
            ]
        ];

        $colOrder = 1;
        foreach ($columns as $column) {
            $parentId = DB::table('menus')->insertGetId([
                'parent_id' => 0,
                'menu_catalogue_id' => 2, // footer-menu
                'publish' => 2,
                'order' => $colOrder++,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('menu_language')->insert([
                'menu_id' => $parentId,
                'language_id' => 1,
                'name' => $column['title'],
                'canonical' => '#',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $itemOrder = 1;
            foreach ($column['items'] as $item) {
                $childId = DB::table('menus')->insertGetId([
                    'parent_id' => $parentId,
                    'menu_catalogue_id' => 2,
                    'publish' => 2,
                    'order' => $itemOrder++,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('menu_language')->insert([
                    'menu_id' => $childId,
                    'language_id' => 1,
                    'name' => $item['name'],
                    'canonical' => $item['canonical'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 7.5. Setup Main Menu (Keyword: main-menu, Catalogue ID: 1)
        $mainMenuCatalogues = DB::table('menu_catalogues')->where('keyword', 'main-menu')->first();
        if ($mainMenuCatalogues) {
            $mainMenuItems = [
                [
                    'name' => 'Trang chủ',
                    'canonical' => '/',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Vector.png'
                ],
                [
                    'name' => 'Giới thiệu',
                    'canonical' => 'gioi-thieu',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Vector-2.png'
                ],
                [
                    'name' => 'Camera nghị định AI',
                    'canonical' => 'camera-nghi-dinh-ai',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Vector-1.png'
                ],
                [
                    'name' => 'Camera hành trình',
                    'canonical' => 'camera-hanh-trinh',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Group.png'
                ],
                [
                    'name' => 'Camera 360',
                    'canonical' => 'camera-360',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Group 9893.png'
                ],
                [
                    'name' => 'Màn hình Android',
                    'canonical' => 'man-hinh-android',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Group 9871.png'
                ],
                [
                    'name' => 'Định vị Gps',
                    'canonical' => 'dinh-vi-gps',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Group 9872.png'
                ],
                [
                    'name' => 'Cảm biến',
                    'canonical' => 'cam-bien',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Group 9875.png'
                ],
                [
                    'name' => 'Thiết bị công nghệ',
                    'canonical' => 'thiet-bi-cong-nghe',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Group 9874.png'
                ],
                [
                    'name' => 'Tin tức',
                    'canonical' => 'tin-tuc',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Group 9873.png'
                ],
                [
                    'name' => 'Liên hệ',
                    'canonical' => 'lien-he',
                    'icon' => 'vendor/frontend/img/project/icons/menu bar/Group 9876.png'
                ]
            ];

            $mainMenuOrder = count($mainMenuItems);
            foreach ($mainMenuItems as $item) {
                $menuId = DB::table('menus')->insertGetId([
                    'parent_id' => 0,
                    'menu_catalogue_id' => $mainMenuCatalogues->id,
                    'publish' => 2,
                    'order' => $mainMenuOrder--,
                    'icon' => $item['icon'],
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('menu_language')->insert([
                    'menu_id' => $menuId,
                    'language_id' => 1,
                    'name' => $item['name'],
                    'canonical' => $item['canonical'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Seed submenus (Level 2 & 3) for Camera nghị định AI
                if ($item['canonical'] == 'camera-nghi-dinh-ai') {
                    $level2Items = [
                        [
                            'name' => 'Camera Nghị Định 10',
                            'canonical' => 'camera-nghi-dinh-10',
                            'items' => ['Thiết bị JC400', 'Thiết bị JC400P', 'Thiết bị JC261']
                        ],
                        [
                            'name' => 'Đầu Ghi Video AI',
                            'canonical' => 'dau-ghi-video-ai',
                            'items' => ['Đầu ghi 4 kênh', 'Đầu ghi 8 kênh']
                        ],
                        [
                            'name' => 'Phụ Kiện Camera AI',
                            'canonical' => 'phu-kien-camera-ai',
                            'items' => ['Mắt camera', 'Cáp tín hiệu', 'Thẻ nhớ']
                        ]
                    ];
                    
                    $l2Order = count($level2Items);
                    foreach ($level2Items as $l2) {
                        $l2Id = DB::table('menus')->insertGetId([
                            'parent_id' => $menuId,
                            'menu_catalogue_id' => $mainMenuCatalogues->id,
                            'publish' => 2,
                            'order' => $l2Order--,
                            'user_id' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        DB::table('menu_language')->insert([
                            'menu_id' => $l2Id,
                            'language_id' => 1,
                            'name' => $l2['name'],
                            'canonical' => $l2['canonical'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        $l3Order = count($l2['items']);
                        foreach ($l2['items'] as $l3Name) {
                            $l3Canonical = Str::slug($l3Name);
                            $l3Id = DB::table('menus')->insertGetId([
                                'parent_id' => $l2Id,
                                'menu_catalogue_id' => $mainMenuCatalogues->id,
                                'publish' => 2,
                                'order' => $l3Order--,
                                'user_id' => 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            
                            DB::table('menu_language')->insert([
                                'menu_id' => $l3Id,
                                'language_id' => 1,
                                'name' => $l3Name,
                                'canonical' => $l3Canonical,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
            $this->command->info('Main menu seeded successfully.');
        }

        // Run nestedset for menus
        $nestedsetMenu = new \App\Classes\Nestedsetbie([
            'table' => 'menus',
            'foreignkey' => 'menu_id',
            'language_id' => 1,
            'isMenu' => TRUE,
        ]);
        $nestedsetMenu->Get();
        $nestedsetMenu->Recursive(0, $nestedsetMenu->Set());
        $nestedsetMenu->Action();
        $this->command->info('Footer menu seeded and indexed successfully.');

        // 8. Setup System Contact Info
        $contactData = [
            'contact_office' => 'No11D LK 35 Phố Nông Quốc Chấn - Vạn Phúc - Hà Đông - Hà Nội | Hệ thống đại lý phục vụ toàn quốc.',
            'contact_address' => 'No11D LK 35 Phố Nông Quốc Chấn - Vạn Phúc - Hà Đông - Hà Nội | Hệ thống đại lý phục vụ toàn quốc.',
            'contact_hotline' => '0987622266 | 0845622266 | 0399622266',
            'contact_phone' => '0987622266 | 0845622266 | 0399622266',
            'contact_email' => 'truccomvn66@gmail.com',
            'contact_complaint' => '0765622266 | 0773622266',
            'contact_technical' => '0343622266 | 0877622266',
            'contact_working_hours' => 'Từ 08h - 21h',
            'contact_copyright' => 'Bản quyền thuộc về TRUC GPS - Website thương mại điện tử đã được Bộ Công Thương cấp phép',
        ];

        foreach ($contactData as $key => $value) {
            for ($langId = 1; $langId <= 3; $langId++) {
                DB::table('systems')->updateOrInsert(
                    ['keyword' => $key, 'language_id' => $langId],
                    [
                        'content' => $value,
                        'user_id' => 1,
                        'updated_at' => now()
                    ]
                );
            }
        }
        $this->command->info('System contact information updated.');
    }
}
