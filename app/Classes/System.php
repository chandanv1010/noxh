<?php
namespace App\Classes;

class System{

    public function config(){
        $data['homepage'] = [
            'label' => 'Thông tin chung',
            'description' => 'Cài đặt đầy đủ thông tin chung của website. Tên thương hiệu hiệu website, Logo, Favicon, vv...',
            'value' => [
                'company' => ['type' => 'text', 'label' => 'Tên công ty'],
                'brand' => ['type' => 'text', 'label' => 'Tên thương hiệu'],
                'slogan' => ['type' => 'text', 'label' => 'Slogan'],
                'logo' => ['type' => 'images', 'label' => 'Logo Website', 'title' => 'Click vào ô phía dưới để tải logo'],
                'logo_mobile' => ['type' => 'images', 'label' => 'Logo Mobile', 'title' => 'Click vào ô phía dưới để tải logo'],
                'favicon' => ['type' => 'images', 'label' => 'Favicon', 'title' => 'Click vào ô phía dưới để tải logo'],
                'copyright' => ['type' => 'text', 'label' => 'Copyright'],
                'flashSale' => ['type' => 'text', 'label' => 'Khuyến mãi'],
                'website' => [
                    'type' => 'select', 
                    'label' => 'Tình trạng website',
                    'option' => [
                        'open' => 'Mở cửa website',
                        'close' => 'Website đang bảo trì'
                    ]
                ],
                'video_youtube_pc' => [
                    'type' => 'textarea', 
                    'label' => 'Video youtube(pc)', 
                ],
                'about_video_title' => ['type' => 'text', 'label' => 'Tiêu đề Video Trang Giới Thiệu'],
                'about_video_desc' => ['type' => 'textarea', 'label' => 'Mô tả Video Trang Giới Thiệu'],
                'about_video_url' => ['type' => 'textarea', 'label' => 'Mã nhúng Youtube Video Trang Giới Thiệu'],
                'viettelpost_email' => ['type' => 'text', 'label' => 'Email Viettel Post'],
                'viettelpost_password' => ['type' => 'text', 'label' => 'Password Viettel Post'],
                'download_text' => ['type' => 'text', 'label' => 'Chữ nút Tải tài liệu'],
                'download_link' => ['type' => 'text', 'label' => 'Link nút Tải tài liệu'],
                'shared_offer_title' => ['type' => 'text', 'label' => 'Tiêu đề khối ưu đãi (chi tiết sản phẩm)', 'title' => 'Ví dụ: ƯU ĐÃI TỪ TRUC GPS'],
                'shared_offer' => ['type' => 'editor', 'label' => 'Ưu đãi chung sản phẩm', 'title' => 'Nội dung ưu đãi mặc định hiển thị dưới giá ở trang chi tiết sản phẩm. Sản phẩm nào có "Nội dung khuyến mãi" riêng sẽ ưu tiên dùng nội dung riêng.'],
                'company_info' => ['type' => 'editor', 'label' => 'Thông tin đơn vị dưới chi tiết sản phẩm'],
            ]
        ];

        $data['telegram'] = [
            'label' => 'Thông báo Telegram',
            'description' => 'Mỗi khi có khách để lại thông tin, hệ thống gửi ngay một tin vào Telegram. Để trống hai ô này thì tính năng tắt, thông tin vẫn lưu đầy đủ trong mục Quản lý liên hệ.',
            'value' => [
                'bot_token' => [
                    'type' => 'text',
                    'label' => 'Bot token',
                    'title' => 'Nhắn @BotFather trên Telegram, gõ /newbot, làm theo hướng dẫn rồi dán chuỗi token nhận được vào đây.',
                ],
                'chat_id' => [
                    'type' => 'text',
                    'label' => 'Chat ID nhận thông báo',
                    'title' => 'ID cá nhân hoặc ID nhóm. Lấy bằng cách nhắn @userinfobot, hoặc thêm bot vào nhóm rồi nhắn @RawDataBot. ID nhóm thường bắt đầu bằng dấu trừ.',
                ],
            ]
        ];

        $data['map'] = [
            'label' => 'Bản đồ dự án',
            'description' => 'Trang /du-an/ban-do vẽ dự án lên bản đồ thật. Mặc định dùng nền bản đồ OpenStreetMap - miễn phí, không cần khai báo gì. Muốn đổi sang Google Maps thì chọn Google ở ô đầu rồi dán API key vào ô thứ hai; thiếu key thì trang tự quay về OpenStreetMap chứ không hỏng.',
            'value' => [
                'provider' => [
                    'type' => 'select',
                    'label' => 'Nền bản đồ',
                    'title' => 'OpenStreetMap miễn phí hoàn toàn. Google Maps quen mắt người Việt hơn và có ảnh vệ tinh, nhưng tính tiền theo lượt mở bản đồ sau khi hết mức miễn phí hàng tháng.',
                    'option' => [
                        'osm' => 'OpenStreetMap (miễn phí, không cần key)',
                        'google' => 'Google Maps (cần API key)',
                    ],
                ],
                'google_key' => [
                    'type' => 'text',
                    'label' => 'Google Maps API key',
                    'title' => 'Lấy tại console.cloud.google.com: tạo project, bật "Maps JavaScript API", vào Credentials tạo API key. Nhớ vào phần Restrict key, chọn "Websites" và chỉ cho phép tên miền của trang - nếu không ai cũng dùng được key của bạn và hóa đơn sẽ do bạn trả.',
                ],
                'tile_url' => [
                    'type' => 'text',
                    'label' => 'Đường dẫn ảnh nền (chỉ dùng cho OpenStreetMap)',
                    'title' => 'Để trống thì dùng máy chủ công cộng của OpenStreetMap. Khi trang đông khách nên thuê một dịch vụ ảnh nền riêng rồi dán đường dẫn dạng https://.../{z}/{x}/{y}.png vào đây.',
                ],
                'tile_credit' => [
                    'type' => 'text',
                    'label' => 'Dòng ghi nguồn ảnh nền',
                    'title' => 'In ở góc dưới bản đồ. OpenStreetMap bắt buộc phải ghi nguồn, xóa dòng này là dùng sai giấy phép.',
                ],
                'zoom' => [
                    'type' => 'text',
                    'label' => 'Mức phóng khi xem cả nước',
                    'title' => 'Số từ 1 (cả quả đất) tới 18 (một con phố). Để trống thì dùng 5.',
                ],
                'zoom_tinh' => [
                    'type' => 'text',
                    'label' => 'Mức phóng khi lọc theo một tỉnh/thành',
                    'title' => 'Để trống thì dùng 11.',
                ],
                'zoom_xa' => [
                    'type' => 'text',
                    'label' => 'Mức phóng khi lọc theo một phường/xã',
                    'title' => 'Để trống thì dùng 14 - đủ gần để nhìn ra từng con phố.',
                ],
            ]
        ];

        $data['sale'] = [
            'label' => 'Nhân viên kinh doanh',
            'description' => 'Quy định nội dung do nhân viên kinh doanh tạo ra có phải chờ quản trị duyệt hay không. Đổi lúc nào cũng được, không ảnh hưởng tới bản ghi đã lưu trước đó.',
            'value' => [
                'post_approval' => [
                    'type' => 'select',
                    'label' => 'Bài viết của nhân viên kinh doanh',
                    'title' => 'Chọn "Phải chờ duyệt" thì mỗi lần nhân viên lưu bài, bài sẽ bị ẩn đi cho tới khi quản trị bấm Duyệt trong danh sách bài viết.',
                    'option' => [
                        'on' => 'Phải chờ quản trị duyệt',
                        'off' => 'Hiển thị ngay, không cần duyệt',
                    ],
                ],
                'project_approval' => [
                    'type' => 'select',
                    'label' => 'Dự án nhân viên tự thêm',
                    'title' => 'Chỉ áp dụng cho dự án do nhân viên TỰ THÊM. Dự án quản trị giao cho họ thì không bị ẩn đi.',
                    'option' => [
                        'off' => 'Hiển thị ngay, không cần duyệt',
                        'on' => 'Phải chờ quản trị duyệt',
                    ],
                ],
            ]
        ];

        $data['contact'] = [
            'label' => 'Thông tin liên hệ',
            'description' => 'Cài đặt thông tin liên hệ của website ví dụ: Địa chỉ công ty, Văn phòng giao dịch, Hotline, Bản đồ, vv...',
            'value' => [
                'office' => ['type' => 'text', 'label' => 'Địa chỉ công ty'],
                'office_map' => [
                    'type' => 'textarea', 
                    'label' => 'Bản đồ công ty',
                    'link' => [
                        'text' => 'Hướng dẫn thiết lập bản đồ',
                        'href' => 'https://manhan.vn/hoc-website-nang-cao/huong-dan-nhung-ban-do-vao-website/',
                        'target' => '_blank'
                    ]
                ],
                'address' => ['type' => 'text', 'label' => 'Văn phòng giao dịch'],
                'hotline' => ['type' => 'text', 'label' => 'Hotline'],
                'address_mt' => ['type' => 'text', 'label' => 'Địa chỉ Miền trung'],
                'hotline_mt' => ['type' => 'text', 'label' => 'Hotline Miền Trung'],
                'address_mn' => ['type' => 'text', 'label' => 'Địa chỉ Miền Nam'],
                'hotline_mn' => ['type' => 'text', 'label' => 'Hotline Miền Nam'],
                'technical_phone' => ['type' => 'text', 'label' => 'Hotline kỹ thuật'],
                'sell_phone' => ['type' => 'text', 'label' => 'Hotline kinh doanh'],
                'phone' => ['type' => 'text', 'label' => 'Số cố định'],
                'fax' => ['type' => 'text', 'label' => 'Fax'],
                'email' => ['type' => 'text', 'label' => 'Email'],
                'website' => ['type' => 'text', 'label' => 'Website'],
                'map' => [
                    'type' => 'textarea', 
                    'label' => 'Bản đồ', 
                    'link' => [
                        'text' => 'Hướng dẫn thiết lập bản đồ',
                        'href' => 'https://manhan.vn/hoc-website-nang-cao/huong-dan-nhung-ban-do-vao-website/',
                        'target' => '_blank'
                    ]
                ],
                'complaint' => ['type' => 'text', 'label' => 'Phản ánh khiếu nại'],
                'technical' => ['type' => 'text', 'label' => 'Hỗ trợ kỹ thuật'],
                'working_hours' => ['type' => 'text', 'label' => 'Thời gian làm việc'],
                'signature' => ['type' => 'editor', 'label' => 'Chữ ký công ty (Chữ ký tin nhắn / hỗ trợ)'],
                'intro' => ['type' => 'textarea', 'label' => 'Giới thiệu'],
            ]
        ];
       

        $data['seo'] = [
            'label' => 'Cấu hình SEO dành cho trang chủ',
            'description' => 'Cài đặt đầy đủ thông tin về SEO của trang chủ website. Bao gồm tiêu đề SEO, Từ Khóa SEO, Mô Tả SEO, Meta images',
            'value' => [
                'meta_title' => ['type' => 'text', 'label' => 'Tiêu đề SEO'],
                'meta_keyword' => ['type' => 'text', 'label' => 'Từ khóa SEO'],
                'meta_description' => ['type' => 'textarea', 'label' => 'Mô tả SEO'],
                'meta_images' => ['type' => 'images', 'label' => 'Ảnh SEO'],
            ]
        ];

        $data['social'] = [
            'label' => 'Cấu hình Mạng xã hội dành cho trang chủ',
            'description' => 'Cài đặt đầy đủ thông tin về Mạng xã hội của trang chủ website. Bao gồm tiêu đề Mạng xã hội, Từ Khóa SEO, Mô Tả SEO, Meta images',
            'value' => [
                'facebook' => ['type' => 'text', 'label' => 'Facebook'],
                'facebook_image' => ['type' => 'images', 'label' => 'Ảnh Fanpage'],
                'google' => ['type' => 'text', 'label' => 'Google'],
                'tiktok' => ['type' => 'text', 'label' => 'Tiktok'],
                'twitter' => ['type' => 'text', 'label' => 'Twitter'],
                'messenger' => ['type' => 'text', 'label' => 'Messenger'],
                'zalo' => ['type' => 'text', 'label' => 'Zalo'],
                'youtube' => ['type' => 'text', 'label' => 'Youtube'],
                'instagram' => ['type' => 'text', 'label' => 'Instagram'],
                'lazada' => ['type' => 'text', 'label' => 'Lazada'],
                'shopee' => ['type' => 'text', 'label' => 'Shopee'],
            ]
        ];

        
        
        $data['script'] = [
            'label' => 'Cấu hình script',
            'description' => '',
            'value' => [
                '1' => ['type' => 'textarea', 'label' => 'Script Head'],
                '2' => ['type' => 'textarea', 'label' => 'Script Body'],
            ]
        ];

       
        return $data;
    }
	
}
