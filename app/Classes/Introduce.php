<?php

namespace App\Classes;

/**
 * Khai bao cac o chu quan tri sua duoc cua NOXH.vn (module "Giới thiệu").
 *
 * KHOA LUU TRONG CSDL = ten nhom + '_' + ten o.
 *
 * Trang backend.introduce.index ghep hai phan do lai
 * ($name = $key.'_'.$keyVal), giong het module Cau hinh he thong. Vi vay ten
 * o KHONG duoc lap lai ten nhom: nhom 'hero' voi o 'title' cho ra khoa
 * hero_title - dung cai ma frontend doc. Neu viet o thanh 'hero_title' thi
 * khoa luu se la hero_hero_title va trang ngoai khong thay gi ca.
 */
class Introduce
{
    public function config()
    {
        $icon = NoxhIcon::chon();

        // --- Thuong hieu ------------------------------------------------------
        $data['brand'] = [
            'label' => 'Thương hiệu',
            'description' => 'Khẩu hiệu hiện dưới logo ở đầu trang và chân trang',
            'value' => [
                'tagline' => [
                    'type' => 'text',
                    'label' => 'Khẩu hiệu dưới logo',
                    'title' => 'Ví dụ: Rõ pháp lý · Đúng thông tin · Vì an cư',
                ],
            ],
        ];

        // --- Dau trang --------------------------------------------------------
        $data['header'] = [
            'label' => 'Đầu trang',
            'description' => 'Ô tìm kiếm và khối số điện thoại trên thanh đầu trang',
            'value' => [
                'search_placeholder' => [
                    'type' => 'text',
                    'label' => 'Chữ mờ trong ô tìm kiếm',
                    'title' => 'Ví dụ: Tìm dự án, tin tức...',
                ],
                'phone_note' => [
                    'type' => 'text',
                    'label' => 'Dòng chữ nhỏ dưới số điện thoại',
                    'title' => 'Ví dụ: Tư vấn miễn phí 24/7. Số điện thoại lấy từ ô Hotline trong Cấu hình hệ thống.',
                ],
            ],
        ];

        // --- Dai anh dau cac trang trong ---------------------------------------
        $data['pagehead'] = [
            'label' => 'Dải ảnh đầu các trang trong',
            'description' => 'Ảnh nền của dải xanh ở đầu trang Tin tức và trang chi tiết dự án',
            'value' => [
                'image' => [
                    'type' => 'images',
                    'label' => 'Ảnh nền dải đầu trang',
                    'title' => 'Ảnh NGANG rất dẹt (khoảng 1920x140). Chữ tiêu đề nằm đè lên nửa trái nên nửa đó của ảnh nên là nền trời, đừng để chi tiết rối.',
                ],
            ],
        ];

        // --- Khoi 1: banner ---------------------------------------------------
        $data['hero'] = [
            'label' => 'Khối 1: Banner trang chủ',
            'description' => 'Chữ, ảnh nền và hai nút của khối lớn nhất ở đầu trang chủ',
            'value' => [
                'image' => [
                    'type' => 'images',
                    'label' => 'Ảnh nền banner (máy tính)',
                    'title' => 'Ảnh NGANG. Dùng cho màn hình rộng, chữ nằm bên trái ảnh.',
                ],
                'image_mobile' => [
                    'type' => 'images',
                    'label' => 'Ảnh nền banner (điện thoại)',
                    'title' => 'Ảnh DỌC, nên khoảng 900x1900. Trên điện thoại chữ nằm đè lên ảnh nên ảnh ngang sẽ không đủ chỗ. Để trống thì dùng luôn ảnh máy tính.',
                ],
                'label' => ['type' => 'text', 'label' => 'Dòng chữ nhỏ phía trên (VD: Cổng thông tin)'],
                'title' => ['type' => 'text', 'label' => 'Tiêu đề lớn (VD: Nhà ở xã hội)'],
                'slogan' => ['type' => 'text', 'label' => 'Khẩu hiệu dưới tiêu đề'],
                'description' => ['type' => 'textarea', 'label' => 'Mô tả ngắn'],

                'usp_1' => ['type' => 'text', 'label' => 'Điểm mạnh 1'],
                'usp_2' => ['type' => 'text', 'label' => 'Điểm mạnh 2'],
                'usp_3' => ['type' => 'text', 'label' => 'Điểm mạnh 3'],

                'btn_1_text' => ['type' => 'text', 'label' => 'Nút chính - chữ'],
                'btn_1_url' => ['type' => 'text', 'label' => 'Nút chính - đường dẫn'],
                'btn_2_text' => ['type' => 'text', 'label' => 'Nút phụ - chữ'],
                'btn_2_url' => ['type' => 'text', 'label' => 'Nút phụ - đường dẫn'],
            ],
        ];

        // --- Bang so lieu ben phai banner -------------------------------------
        $data['stat'] = [
            'label' => 'Khối 1b: Bảng số liệu cạnh banner',
            'description' => 'Bốn dòng số liệu trong bảng trắng nằm bên phải banner. Để trống giá trị thì dòng đó không hiện.',
            'value' => $this->oSoLieu($icon),
        ];

        // --- Thanh tim du an --------------------------------------------------
        $data['search'] = [
            'label' => 'Khối 2: Thanh tìm dự án',
            'description' => 'Thanh trắng nằm ngay dưới banner, cho người dùng chọn khu vực và mức giá',
            'value' => [
                'title' => ['type' => 'text', 'label' => 'Tiêu đề (VD: Tìm dự án nhà ở xã hội)'],
                'description' => ['type' => 'text', 'label' => 'Dòng chữ nhỏ bên dưới'],
                'button' => ['type' => 'text', 'label' => 'Chữ trên nút tìm'],
                'price_ranges' => [
                    'type' => 'textarea',
                    'label' => 'Các khoảng giá cho ô "Khoảng giá"',
                    'title' => 'Mỗi dòng một khoảng, viết theo dạng  Nhãn | từ-đến  (đơn vị triệu/m², để trống một đầu là không giới hạn). Ví dụ: Dưới 18 triệu/m² | -18',
                ],
            ],
        ];

        // --- Dai moi kiem tra dieu kien ---------------------------------------
        $data['check'] = [
            'label' => 'Khối 3: Dải mời kiểm tra điều kiện',
            'description' => 'Dải xanh mời người dùng làm bài kiểm tra điều kiện mua NOXH',
            'value' => [
                'title' => ['type' => 'text', 'label' => 'Tiêu đề'],
                'description' => ['type' => 'textarea', 'label' => 'Mô tả'],
                'button_text' => ['type' => 'text', 'label' => 'Chữ trên nút cam'],
                'button_url' => ['type' => 'text', 'label' => 'Đường dẫn của nút cam'],

                'point_1' => ['type' => 'text', 'label' => 'Điểm nhấn 1 (VD: Nhanh chóng)'],
                'point_1_icon' => ['type' => 'select', 'label' => 'Điểm nhấn 1 - hình', 'option' => $icon],
                'point_2' => ['type' => 'text', 'label' => 'Điểm nhấn 2'],
                'point_2_icon' => ['type' => 'select', 'label' => 'Điểm nhấn 2 - hình', 'option' => $icon],
                'point_3' => ['type' => 'text', 'label' => 'Điểm nhấn 3'],
                'point_3_icon' => ['type' => 'select', 'label' => 'Điểm nhấn 3 - hình', 'option' => $icon],

                'step_1' => ['type' => 'text', 'label' => 'Bước 1 - tên'],
                'step_1_desc' => ['type' => 'text', 'label' => 'Bước 1 - mô tả'],
                'step_2' => ['type' => 'text', 'label' => 'Bước 2 - tên'],
                'step_2_desc' => ['type' => 'text', 'label' => 'Bước 2 - mô tả'],
                'step_3' => ['type' => 'text', 'label' => 'Bước 3 - tên'],
                'step_3_desc' => ['type' => 'text', 'label' => 'Bước 3 - mô tả'],
                'step_4' => ['type' => 'text', 'label' => 'Bước 4 - tên'],
                'step_4_desc' => ['type' => 'text', 'label' => 'Bước 4 - mô tả'],
                'step_5' => ['type' => 'text', 'label' => 'Bước 5 - tên (Kết quả)'],
                'step_5_desc' => ['type' => 'text', 'label' => 'Bước 5 - mô tả'],
            ],
        ];

        // --- Khoi du an noi bat -----------------------------------------------
        $data['project'] = [
            'label' => 'Khối 4: Dự án nổi bật + trang Dự án',
            'description' => 'Tiêu đề khối dự án ở trang chủ, và phần đầu + bộ lọc của trang danh sách dự án',
            'value' => [
                'block_heading' => ['type' => 'text', 'label' => 'Trang chủ - tiêu đề khối dự án nổi bật'],
                'more_text' => ['type' => 'text', 'label' => 'Trang chủ - chữ liên kết "xem tất cả"'],
                'heading' => ['type' => 'text', 'label' => 'Trang Dự án - tiêu đề'],
                'description' => ['type' => 'textarea', 'label' => 'Trang Dự án - mô tả'],
            ] + $this->oSoLieuDuAn($icon) + [
                'search_placeholder' => ['type' => 'text', 'label' => 'Ô tìm kiếm - chữ mờ'],
                'search_button' => ['type' => 'text', 'label' => 'Ô tìm kiếm - chữ trên nút'],
                'sort_label' => ['type' => 'text', 'label' => 'Thanh trên danh sách - nhãn ô sắp xếp'],
                'filter_heading' => ['type' => 'text', 'label' => 'Bộ lọc - tiêu đề'],
                'filter_clear' => ['type' => 'text', 'label' => 'Bộ lọc - chữ xoá lọc'],
                'filter_button' => ['type' => 'text', 'label' => 'Bộ lọc - chữ trên nút áp dụng'],
            ],
        ];

        // --- Trang Du an: cot phai va banner tu van ----------------------------
        $data['projectaside'] = [
            'label' => 'Khối 4b: Trang Dự án - cột phải & banner tư vấn',
            'description' => 'Khối bản đồ, khối tin tức nổi bật, khối "Có dự án phù hợp" và banner tư vấn ở cột trái',
            'value' => [
                'map_heading' => ['type' => 'text', 'label' => 'Bản đồ - tiêu đề'],
                'map_all_text' => ['type' => 'text', 'label' => 'Bản đồ - chữ liên kết góc phải'],
                'map_more_text' => ['type' => 'text', 'label' => 'Bản đồ - chữ nút dưới danh sách tỉnh'],
                'map_note' => [
                    'type' => 'text',
                    'label' => 'Bản đồ - mô tả trang bản đồ',
                    'title' => 'Hiện ở đầu trang /du-an/ban-do',
                ],

                'news_heading' => ['type' => 'text', 'label' => 'Tin tức nổi bật - tiêu đề'],
                'news_more_text' => ['type' => 'text', 'label' => 'Tin tức nổi bật - chữ liên kết góc phải'],

                'fit_heading' => ['type' => 'text', 'label' => 'Khối "Có dự án phù hợp" - tiêu đề'],
                'fit_icon' => ['type' => 'select', 'label' => 'Khối "Có dự án phù hợp" - hình', 'option' => $icon],
                'fit_point_1' => ['type' => 'text', 'label' => 'Khối "Có dự án phù hợp" - gạch đầu dòng 1'],
                'fit_point_2' => ['type' => 'text', 'label' => 'Khối "Có dự án phù hợp" - gạch đầu dòng 2'],
                'fit_point_3' => ['type' => 'text', 'label' => 'Khối "Có dự án phù hợp" - gạch đầu dòng 3'],
                'fit_button' => ['type' => 'text', 'label' => 'Khối "Có dự án phù hợp" - chữ trên nút'],
                'fit_url' => ['type' => 'text', 'label' => 'Khối "Có dự án phù hợp" - đường dẫn nút'],

                'banner_heading' => ['type' => 'text', 'label' => 'Banner tư vấn - tiêu đề'],
                'banner_point_1' => ['type' => 'text', 'label' => 'Banner tư vấn - gạch đầu dòng 1'],
                'banner_point_2' => ['type' => 'text', 'label' => 'Banner tư vấn - gạch đầu dòng 2'],
                'banner_point_3' => ['type' => 'text', 'label' => 'Banner tư vấn - gạch đầu dòng 3'],
                'banner_button' => ['type' => 'text', 'label' => 'Banner tư vấn - chữ trên nút'],
                'banner_url' => [
                    'type' => 'text',
                    'label' => 'Banner tư vấn - đường dẫn nút',
                    'title' => 'Để trống thì nút gọi vào số Hotline trong Cấu hình hệ thống.',
                ],
                'banner_bg' => [
                    'type' => 'images',
                    'label' => 'Banner tư vấn - ảnh nền cả khối',
                    'title' => 'Ảnh NGANG phủ kín banner (khoảng 560x420). Có ảnh này thì ảnh người tư vấn ở dưới không dùng nữa. Để trống thì dùng nền xanh + hình vẽ sẵn.',
                ],
                'banner_image' => [
                    'type' => 'images',
                    'label' => 'Banner tư vấn - ảnh người tư vấn',
                    'title' => 'Ảnh DỌC đã tách nền, đặt sát mép phải của banner. Để trống thì banner chỉ có chữ.',
                ],
            ],
        ];

        // --- Trang chi tiet du an: phan chinh ---------------------------------
        $data['projectdetail'] = [
            'label' => 'Khối 4c: Trang chi tiết dự án - phần chính',
            'description' => 'Thẻ giá ở đầu trang, và tiêu đề của các khối Tổng quan / Loại căn hộ / Vị trí / Tiến độ / Dự án tương tự',
            'value' => $this->oChiTietDuAn($icon),
        ];

        // --- Trang chi tiet du an: cot phai -----------------------------------
        $data['projectlead'] = [
            'label' => 'Khối 4d: Trang chi tiết dự án - cột phải & form đăng ký',
            'description' => 'Khối "Tư vấn nhanh", danh sách tư vấn hỗ trợ và form đăng ký nhận thông tin dự án',
            'value' => $this->oCotPhaiChiTiet($icon),
        ];

        // --- Sau o thong tin huu ich ------------------------------------------
        $data['useful'] = [
            'label' => 'Khối 5: Thông tin hữu ích',
            'description' => 'Sáu ô dẫn sang các trang chính. Để trống tiêu đề thì ô đó không hiện.',
            'value' => $this->oHuuIch($icon),
        ];

        // --- Khoi tin tuc ------------------------------------------------------
        $data['news'] = [
            'label' => 'Khối 6: Tin tức',
            'description' => 'Khối tin tức ở trang chủ, trang danh mục tin và trang chi tiết tin',
            'value' => [
                'block_heading' => ['type' => 'text', 'label' => 'Trang chủ - tiêu đề khối tin tức'],
                'more_text' => ['type' => 'text', 'label' => 'Trang chủ - chữ liên kết "xem tất cả"'],

                'heading' => ['type' => 'text', 'label' => 'Dải đầu trang - tiêu đề'],
                'description' => ['type' => 'textarea', 'label' => 'Dải đầu trang - mô tả'],

                // Cot trai - dung chung cho ca trang danh muc va trang chi tiet
                'cat_heading' => ['type' => 'text', 'label' => 'Cột trái - tiêu đề khối danh mục'],
                'cat_all_text' => ['type' => 'text', 'label' => 'Cột trái - tên mục "tất cả tin tức"'],
                'cat_all_icon' => [
                    'type' => 'select',
                    'label' => 'Cột trái - hình của mục "tất cả tin tức"',
                    'option' => $icon,
                    'title' => 'Hình của từng chuyên mục chọn trong màn hình Chuyên mục tin tức.',
                ],
                'help_heading' => ['type' => 'text', 'label' => 'Cột trái - thẻ hỗ trợ: tiêu đề'],
                'help_description' => ['type' => 'textarea', 'label' => 'Cột trái - thẻ hỗ trợ: mô tả'],
                'help_icon' => ['type' => 'select', 'label' => 'Cột trái - thẻ hỗ trợ: hình', 'option' => $icon],
                'help_button' => ['type' => 'text', 'label' => 'Cột trái - thẻ hỗ trợ: chữ trên nút'],
                'help_link' => [
                    'type' => 'text',
                    'label' => 'Cột trái - thẻ hỗ trợ: đường dẫn của nút',
                    'title' => 'Ví dụ: /cong-hoa/tu-van',
                ],

                // Cot phai
                'related_heading' => ['type' => 'text', 'label' => 'Cột phải - tiêu đề khối bài liên quan'],
                'related_all_text' => ['type' => 'text', 'label' => 'Cột phải - chữ liên kết "xem tất cả"'],

                // Giua trang
                'count_text' => [
                    'type' => 'text',
                    'label' => 'Trang danh mục - dòng đếm bài viết',
                    'title' => 'Dùng {so} thay cho con số. Ví dụ: {so} bài viết',
                ],
                'empty_text' => ['type' => 'text', 'label' => 'Câu hiện khi chuyên mục chưa có bài nào'],
                'view_text' => [
                    'type' => 'text',
                    'label' => 'Chữ sau con số lượt xem',
                    'title' => 'Ví dụ: lượt xem',
                ],
                'share_label' => ['type' => 'text', 'label' => 'Trang chi tiết - chữ trước các nút chia sẻ'],
                'copy_done_text' => ['type' => 'text', 'label' => 'Trang chi tiết - báo đã chép đường dẫn'],
                'expand_text' => ['type' => 'text', 'label' => 'Trang chi tiết - chữ trên nút mở rộng nội dung'],
                'collapse_text' => ['type' => 'text', 'label' => 'Trang chi tiết - chữ trên nút thu gọn nội dung'],
                'tag_heading' => [
                    'type' => 'text',
                    'label' => 'Trang chi tiết - tiêu đề hàng từ khoá',
                    'title' => 'Từ khoá lấy từ ô "Meta keyword" của bài viết, cách nhau dấu phẩy. Để trống ô này thì hàng từ khoá không có tiêu đề.',
                ],
            ],
        ];

        // --- The chuyen gia o cot phai ------------------------------------------
        $data['expert'] = [
            'label' => 'Thẻ chuyên gia (cột phải)',
            'description' => 'Các dòng chữ cố định của thẻ chuyên gia; tên, chức danh, cam kết và ảnh lấy từ màn hình Chuyên gia',
            'value' => [
                'heading' => ['type' => 'text', 'label' => 'Nhãn phía trên tên chuyên gia'],
                'button' => [
                    'type' => 'text',
                    'label' => 'Chữ trên nút',
                    'title' => 'Để trống thì thẻ hiện nút gọi số điện thoại của chuyên gia.',
                ],
                'button_link' => ['type' => 'text', 'label' => 'Đường dẫn của nút'],
            ],
        ];

        // --- Doi tu van --------------------------------------------------------
        $data['advisor'] = [
            'label' => 'Khối 7: Đội tư vấn hồ sơ',
            'description' => 'Khối thẻ nhân viên tư vấn ở cuối trang chủ. Danh sách người lấy từ tài khoản nhóm kinh doanh.',
            'value' => [
                'heading' => ['type' => 'text', 'label' => 'Tiêu đề khối'],
                'description' => ['type' => 'text', 'label' => 'Dòng chữ nhỏ bên dưới'],
                'more_text' => ['type' => 'text', 'label' => 'Chữ liên kết "xem tất cả"'],
                'role' => [
                    'type' => 'text',
                    'label' => 'Chức danh mặc định',
                    'title' => 'Dùng khi tài khoản chưa tự điền chức danh. Ví dụ: Tư vấn hồ sơ NOXH',
                ],
            ],
        ];

        // --- Dai dang ky nhan tin ----------------------------------------------
        $data['subscribe'] = [
            'label' => 'Khối 8: Đăng ký nhận thông tin',
            'description' => 'Dải cuối trang chủ, nơi khách để lại họ tên và số điện thoại',
            'value' => [
                'title' => ['type' => 'text', 'label' => 'Tiêu đề'],
                'description' => ['type' => 'text', 'label' => 'Mô tả ngắn'],
                'button' => ['type' => 'text', 'label' => 'Chữ trên nút đăng ký'],
                'note' => ['type' => 'text', 'label' => 'Dòng cam kết bảo mật phía dưới'],
                'image' => [
                    'type' => 'images',
                    'label' => 'Ảnh minh hoạ bên phải',
                    'title' => 'Ảnh NGANG, đặt ở góc phải của dải. Để trống thì dải chỉ có nền xanh nhạt.',
                ],
            ],
        ];

        // --- Chan trang --------------------------------------------------------
        $data['footer'] = [
            'label' => 'Chân trang',
            'description' => 'Mô tả, tiêu đề cột mạng xã hội và dòng chữ ở thanh cuối cùng',
            'value' => [
                'description' => ['type' => 'textarea', 'label' => 'Mô tả ở chân trang'],
                'social_heading' => ['type' => 'text', 'label' => 'Tiêu đề cột mạng xã hội'],
                'slogan' => [
                    'type' => 'text',
                    'label' => 'Dòng chữ bên phải thanh cuối cùng',
                    'title' => 'Ví dụ: Vì cộng đồng · Vì một Việt Nam an cư',
                ],
            ],
        ];

        // --- Trang Phong phap ly ----------------------------------------------
        $data['legal'] = [
            'label' => 'Trang Pháp lý',
            'description' => 'Toàn bộ chữ trên trang Phòng pháp lý NOXH',
            'value' => $this->oPhapLy($icon),
        ];

        // --- Chu o cac trang trong ---------------------------------------------
        foreach ([
            'dossier' => 'Hồ sơ',
            'finance' => 'Tài chính',
            'qa' => 'Hỏi đáp',
        ] as $ma => $ten) {
            $data[$ma] = [
                'label' => 'Trang ' . $ten,
                'description' => 'Tiêu đề và mô tả ở đầu trang ' . mb_strtolower($ten),
                'value' => [
                    'heading' => ['type' => 'text', 'label' => 'Tiêu đề'],
                    'description' => ['type' => 'textarea', 'label' => 'Mô tả'],
                ],
            ];
        }

        return $data;
    }

    /**
     * Muoi tab cua trang chi tiet du an, dung thu tu ban thiet ke.
     *
     * Day vua la thanh tab vua la danh sach khung noi dung: bam mot tab thi
     * doi noi dung trong khung chu khong truot xuong. Khoa o day trung voi
     * khoa ma show.blade.php dung.
     */
    public const TAB_CHI_TIET = [
        'overview' => 'Tổng quan',
        'location' => 'Vị trí',
        'units' => 'Mặt bằng',
        'amenity' => 'Tiện ích',
        'price' => 'Giá bán',
        'progress' => 'Tiến độ',
        'legal' => 'Pháp lý',
        'gallery' => 'Hình ảnh - Video',
        'doc' => 'Tài liệu',
        'faq' => 'Hỏi đáp',
    ];

    /**
     * Cac dong cua bang "Tong quan du an", theo dung thu tu ban thiet ke.
     *
     * Khoa o day trung voi khoa ma ProjectController::bangTongQuan() dung,
     * hai cho phai khop nhau thi nhan moi gan dung gia tri.
     */
    public const DONG_TONG_QUAN = [
        'name' => 'Tên dự án',
        'place' => 'Vị trí',
        'investor' => 'Chủ đầu tư',
        'land' => 'Tổng diện tích',
        'scale' => 'Quy mô',
        'units' => 'Tổng số căn',
        'types' => 'Loại hình căn hộ',
        'area' => 'Diện tích căn hộ',
        'price' => 'Giá bán dự kiến',
        'ownership' => 'Hình thức sở hữu',
        'start' => 'Khởi công',
        'handover' => 'Dự kiến bàn giao',
        'status' => 'Trạng thái',
    ];

    /**
     * Cac o chu cua trang Phong phap ly NOXH (ban ve plxh fix.jpg).
     *
     * Bon o chip o dai dau trang, nam khoi chu de, bon o cam ket cuoi trang
     * deu la so luong CO DINH theo ban ve nen khai thang o day; con danh
     * sach van ban va bai viet thi lay tu CSDL.
     */
    private function oPhapLy(array $icon): array
    {
        $o = [
            'heading' => ['type' => 'text', 'label' => 'Dải đầu trang - tiêu đề lớn'],
            'description' => ['type' => 'text', 'label' => 'Dải đầu trang - dòng dưới tiêu đề'],
            'intro' => ['type' => 'textarea', 'label' => 'Dải đầu trang - đoạn giới thiệu'],
            'hero_icon' => ['type' => 'select', 'label' => 'Dải đầu trang - hình bên trái tiêu đề', 'option' => $icon],
            'hero_bg' => [
                'type' => 'images',
                'label' => 'Dải đầu trang - ảnh nền',
                'title' => 'Ảnh NGANG làm nền cho dải đầu trang. Để trống thì dùng nền xanh vẽ sẵn.',
            ],
            'hero_banner' => [
                'type' => 'images',
                'label' => 'Dải đầu trang - ảnh minh hoạ bên phải',
                'title' => 'Ảnh đặt ở mép phải phần giới thiệu (toà nhà, cân công lý...). Để trống thì không hiện.',
            ],
        ];

        // Bon o chip nho duoi doan gioi thieu.
        for ($i = 1; $i <= 4; $i++) {
            $o["chip_{$i}_text"] = ['type' => 'text', 'label' => "Dải đầu trang - ô nhỏ {$i}, chữ"];
            $o["chip_{$i}_icon"] = ['type' => 'select', 'label' => "Dải đầu trang - ô nhỏ {$i}, hình", 'option' => $icon];
        }

        $o += [
            // Ten, anh, loi cam ket va so dien thoai lay tu chuyen vien tu van
            // mac dinh (man hinh "Chuyên gia"), o day chi khai phan chu chung.
            'help_heading' => ['type' => 'text', 'label' => 'Thẻ hỗ trợ - tiêu đề'],
            'help_image' => [
                'type' => 'images',
                'label' => 'Thẻ hỗ trợ - ảnh người tư vấn',
                'title' => 'Ảnh ĐỨNG đã tách nền, đặt ở góc phải thẻ. Để trống thì lấy ảnh của chuyên viên tư vấn mặc định.',
            ],
            'help_bullet_icon' => ['type' => 'select', 'label' => 'Thẻ hỗ trợ - hình đầu dòng cam kết', 'option' => $icon],
            'help_button' => ['type' => 'text', 'label' => 'Thẻ hỗ trợ - chữ trên nút'],
            'help_button_icon' => ['type' => 'select', 'label' => 'Thẻ hỗ trợ - hình trên nút', 'option' => $icon],
            'help_button_link' => ['type' => 'text', 'label' => 'Thẻ hỗ trợ - đường dẫn của nút'],
            'help_phone_note' => ['type' => 'text', 'label' => 'Thẻ hỗ trợ - ghi chú cạnh số điện thoại'],

            'topic_heading' => ['type' => 'text', 'label' => 'Khối Chủ đề - tiêu đề'],
        ];

        // Nam o chu de, dung so luong ban ve.
        for ($i = 1; $i <= 5; $i++) {
            $o["topic_{$i}_title"] = ['type' => 'text', 'label' => "Chủ đề {$i} - tên"];
            $o["topic_{$i}_description"] = ['type' => 'textarea', 'label' => "Chủ đề {$i} - mô tả"];
            $o["topic_{$i}_icon"] = ['type' => 'select', 'label' => "Chủ đề {$i} - hình", 'option' => $icon];
            $o["topic_{$i}_link"] = ['type' => 'text', 'label' => "Chủ đề {$i} - đường dẫn"];
        }

        $o += [
            'post_heading' => ['type' => 'text', 'label' => 'Khối Bài viết - tiêu đề'],
            'post_all_text' => ['type' => 'text', 'label' => 'Khối Bài viết - chữ liên kết góc phải'],
            'post_hot_text' => ['type' => 'text', 'label' => 'Khối Bài viết - nhãn trên ảnh bài đầu tiên'],
            'post_view_text' => [
                'type' => 'text',
                'label' => 'Khối Bài viết - chữ sau số lượt xem',
                'title' => 'Ví dụ: lượt xem',
            ],
            'post_empty' => ['type' => 'text', 'label' => 'Khối Bài viết - chữ khi chưa có bài nào'],

            'cta_title' => ['type' => 'text', 'label' => 'Dải cam kết - dòng trên'],
            'cta_description' => ['type' => 'text', 'label' => 'Dải cam kết - dòng dưới'],
            'cta_icon' => ['type' => 'select', 'label' => 'Dải cam kết - hình bên trái', 'option' => $icon],
            'cta_button' => ['type' => 'text', 'label' => 'Dải cam kết - chữ trên nút'],
            'cta_link' => ['type' => 'text', 'label' => 'Dải cam kết - đường dẫn của nút'],

            'doc_heading' => ['type' => 'text', 'label' => 'Cột phải - tiêu đề khối văn bản'],
            'doc_all_text' => ['type' => 'text', 'label' => 'Cột phải - chữ liên kết góc phải'],
            'doc_empty' => ['type' => 'text', 'label' => 'Cột phải - chữ khi chưa có văn bản nào'],
            'doc_download_title' => ['type' => 'text', 'label' => 'Cột phải - chú thích nút tải về'],
            'doc_effective_text' => [
                'type' => 'text',
                'label' => 'Cột phải - dòng ngày khi văn bản chưa có tóm tắt',
                'title' => 'Gõ {ngay} để thay bằng ngày hiệu lực. Ví dụ: Có hiệu lực từ {ngay}',
            ],
        ];

        // Bon o cam ket o dai cuoi trang.
        for ($i = 1; $i <= 4; $i++) {
            $o["trust_{$i}_title"] = ['type' => 'text', 'label' => "Dải cuối trang - ô {$i}, dòng trên"];
            $o["trust_{$i}_sub"] = ['type' => 'text', 'label' => "Dải cuối trang - ô {$i}, dòng dưới"];
            $o["trust_{$i}_icon"] = ['type' => 'select', 'label' => "Dải cuối trang - ô {$i}, hình", 'option' => $icon];
        }

        return $o;
    }

    /**
     * Cac o chu cua phan chinh trang chi tiet du an.
     *
     * Bon o thong so trong the gia (Quy mo / So can ho / Loai hinh / Ban giao)
     * chi khai NHAN va HINH o day - con so thi lay thang tu du an, nen quan
     * tri doi ten nhan ma khong so lech so lieu.
     *
     * Bon o diem nhan ben duoi la MAC DINH dung chung: du an nao muon khac thi
     * khai rieng o man hinh "Điểm nhấn dự án".
     */
    private function oChiTietDuAn(array $icon): array
    {
        $o = [
            'hero_slogan' => [
                'type' => 'textarea',
                'label' => 'Khẩu hiệu viết tay ở góc phải trên',
                'title' => 'Mỗi dòng một câu. Ví dụ: "An cư hôm nay" / "Kiến tạo tương lai". Để trống thì không hiện.',
            ],
            'hero_bg' => [
                'type' => 'images',
                'label' => 'Ảnh nền dải đầu trang',
                'title' => 'Ảnh NGANG làm nền cho vùng tiêu đề (bầu trời). Để trống thì dùng nền xanh nhạt vẽ sẵn.',
            ],
            'video_text' => ['type' => 'text', 'label' => 'Ảnh lớn - chữ trên nút xem video'],
            'gallery_more' => [
                'type' => 'text',
                'label' => 'Dải ảnh nhỏ - chữ trên ô cuối',
                'title' => 'Gõ {so} để thay bằng số ảnh còn lại. Ví dụ: + {so} ảnh',
            ],

            'price_label' => ['type' => 'text', 'label' => 'Thẻ giá - nhãn phía trên giá'],
            'price_note' => ['type' => 'text', 'label' => 'Thẻ giá - ghi chú dưới giá'],
            'price_empty' => [
                'type' => 'text',
                'label' => 'Thẻ giá - chữ thay thế khi chưa có giá',
                'title' => 'Hiện khi dự án chưa công bố giá.',
            ],
            'price_button' => ['type' => 'text', 'label' => 'Thẻ giá - chữ trên nút đăng ký'],
            'trust_1' => ['type' => 'text', 'label' => 'Thẻ giá - dòng cam kết, ý 1'],
            'trust_2' => ['type' => 'text', 'label' => 'Thẻ giá - dòng cam kết, ý 2'],
            'trust_3' => ['type' => 'text', 'label' => 'Thẻ giá - dòng cam kết, ý 3'],
            'trust_icon' => ['type' => 'select', 'label' => 'Thẻ giá - hình đầu dòng cam kết', 'option' => $icon],

            'similar_heading' => [
                'type' => 'text',
                'label' => 'Khối Dự án tương tự - tiêu đề',
                'title' => 'Gõ {tinh} để thay bằng tên tỉnh/thành của dự án đang xem.',
            ],
        ];

        // Bon o thong so: chi nhan + hinh, con so lay tu du an.
        $nhanMac = [1 => 'Quy mô', 2 => 'Số căn hộ', 3 => 'Loại hình', 4 => 'Bàn giao'];
        foreach ($nhanMac as $i => $ten) {
            $o["spec_{$i}_label"] = ['type' => 'text', 'label' => "Thẻ giá - ô thông số {$i}, nhãn (mặc định: {$ten})"];
            $o["spec_{$i}_icon"] = ['type' => 'select', 'label' => "Thẻ giá - ô thông số {$i}, hình", 'option' => $icon];
        }

        // Bon o diem nhan mac dinh.
        for ($i = 1; $i <= 4; $i++) {
            $o["point_{$i}_title"] = ['type' => 'text', 'label' => "Thẻ giá - ô điểm nhấn {$i}, dòng trên"];
            $o["point_{$i}_sub"] = ['type' => 'text', 'label' => "Thẻ giá - ô điểm nhấn {$i}, dòng dưới"];
            $o["point_{$i}_icon"] = ['type' => 'select', 'label' => "Thẻ giá - ô điểm nhấn {$i}, hình", 'option' => $icon];
        }

        $o += [
            'overview_photo_text' => ['type' => 'text', 'label' => 'Khối Tổng quan - chữ trên nút xem ảnh thực tế'],

            'units_block_heading' => [
                'type' => 'text',
                'label' => 'Khối Các loại căn hộ - tiêu đề',
                'title' => 'Khối ba thẻ căn hộ nằm dưới khung tab, khác với tab "Mặt bằng".',
            ],
            'units_all_text' => ['type' => 'text', 'label' => 'Khối Loại căn hộ - chữ liên kết góc phải'],
            'units_detail_text' => ['type' => 'text', 'label' => 'Khối Loại căn hộ - chữ trên nút của mỗi thẻ'],
            'units_area_label' => ['type' => 'text', 'label' => 'Khối Loại căn hộ - nhãn dòng diện tích'],
            'units_price_label' => ['type' => 'text', 'label' => 'Khối Loại căn hộ - nhãn dòng giá'],

            'location_button' => ['type' => 'text', 'label' => 'Khối Vị trí - chữ trên nút mở bản đồ'],
            'progress_button' => ['type' => 'text', 'label' => 'Khối Tiến độ - chữ trên nút xem cập nhật'],
            'progress_modal_heading' => [
                'type' => 'text',
                'label' => 'Khối Tiến độ - tiêu đề hộp hiện đầy đủ tiến độ',
                'title' => 'Hộp bật lên khi bấm nút xem cập nhật.',
            ],
            'price_col_name' => ['type' => 'text', 'label' => 'Tab Giá bán - tên cột loại căn hộ'],
            'price_col_area' => ['type' => 'text', 'label' => 'Tab Giá bán - tên cột diện tích'],
            'price_col_price' => ['type' => 'text', 'label' => 'Tab Giá bán - tên cột giá dự kiến'],
            'gallery_video_text' => ['type' => 'text', 'label' => 'Tab Hình ảnh - chữ trên nút mở video'],
            'doc_empty_file' => [
                'type' => 'text',
                'label' => 'Tab Pháp lý / Tài liệu - chữ khi giấy tờ chưa có file',
                'title' => 'Ví dụ: Chưa đính kèm file',
            ],
            'empty_text' => [
                'type' => 'text',
                'label' => 'Chữ hiện khi một tab chưa có dữ liệu',
                'title' => 'Ví dụ: Đang cập nhật',
            ],
            'similar_all_text' => ['type' => 'text', 'label' => 'Khối Dự án tương tự - chữ liên kết góc phải'],
        ];

        // Moi khoi noi dung co HAI o chu: tieu de in dam tren khoi, va nhan
        // ngan tren thanh tab dinh o dau trang. Thanh tab chi liet ke nhung
        // khoi THUC SU co du lieu, nen khong can o bat/tat rieng.
        foreach (self::TAB_CHI_TIET as $ma => $ten) {
            $o["{$ma}_heading"] = ['type' => 'text', 'label' => "Tab {$ten} - tiêu đề trong khung"];
            $o["{$ma}_tab"] = ['type' => 'text', 'label' => "Tab {$ten} - nhãn trên thanh tab"];
            $o["{$ma}_icon"] = ['type' => 'select', 'label' => "Tab {$ten} - hình trên thanh tab", 'option' => $icon];
        }

        // Hai khoi nam ngoai thanh tab nen chi co tieu de.
        $o['content_heading'] = ['type' => 'text', 'label' => 'Khối Giới thiệu chi tiết - tiêu đề'];

        // Nhan tung dong cua bang Tong quan. Gia tri thi lay tu du an, day
        // chi la chu o cot trai - de quan tri doi duoc "Tổng số căn" thanh
        // "Số lượng căn hộ" ma khong phai sua ma nguon.
        foreach (self::DONG_TONG_QUAN as $ma => $ten) {
            $o["row_{$ma}"] = [
                'type' => 'text',
                'label' => "Bảng Tổng quan - nhãn dòng \"{$ten}\"",
                'title' => 'Để trống thì dòng này không hiện ra trang.',
            ];
        }

        $o['similar_price_prefix'] = [
            'type' => 'text',
            'label' => 'Khối Dự án tương tự - chữ đứng trước giá',
            'title' => 'Ví dụ: Từ',
        ];

        return $o;
    }

    /**
     * Cac o chu cua cot phai trang chi tiet du an.
     *
     * Danh sach nguoi trong khoi "Danh sách tư vấn hỗ trợ" KHONG khai o day:
     * no lay tu nhung nhan vien duoc gan vao dung du an dang xem.
     */
    private function oCotPhaiChiTiet(array $icon): array
    {
        return [
            'quick_heading' => ['type' => 'text', 'label' => 'Tư vấn nhanh - tiêu đề'],
            'quick_note' => ['type' => 'text', 'label' => 'Tư vấn nhanh - mô tả ngắn'],
            'quick_channel' => [
                'type' => 'text',
                'label' => 'Tư vấn nhanh - dòng dưới số điện thoại',
                'title' => 'Ví dụ: (Zalo / Call / SMS)',
            ],
            'quick_icon' => ['type' => 'select', 'label' => 'Tư vấn nhanh - hình bên trái tiêu đề', 'option' => $icon],

            'staff_heading' => ['type' => 'text', 'label' => 'Danh sách tư vấn - tiêu đề'],
            'staff_note' => [
                'type' => 'text',
                'label' => 'Danh sách tư vấn - mô tả',
                'title' => 'Gõ {tinh} để thay bằng tên tỉnh/thành của dự án đang xem.',
            ],
            'staff_verify' => ['type' => 'text', 'label' => 'Danh sách tư vấn - dòng xác minh trong ngoặc'],
            'staff_role' => [
                'type' => 'text',
                'label' => 'Danh sách tư vấn - chức danh mặc định',
                'title' => 'Dùng khi tài khoản nhân viên chưa khai chức danh.',
            ],
            'staff_area' => [
                'type' => 'text',
                'label' => 'Danh sách tư vấn - dòng khu vực',
                'title' => 'Gõ {tinh} để thay bằng tên tỉnh/thành của dự án. Để trống thì không hiện dòng này.',
            ],
            'staff_button' => ['type' => 'text', 'label' => 'Danh sách tư vấn - chữ trên nút của mỗi người'],
            'staff_more' => ['type' => 'text', 'label' => 'Danh sách tư vấn - chữ trên nút cuối khối'],
            'staff_modal_heading' => [
                'type' => 'text',
                'label' => 'Danh sách tư vấn - tiêu đề hộp hiện đầy đủ danh sách',
                'title' => 'Hộp bật lên khi bấm nút ở cuối khối. Gõ {tinh} để thay bằng tên tỉnh/thành.',
            ],
            'staff_empty' => [
                'type' => 'text',
                'label' => 'Danh sách tư vấn - chữ khi dự án chưa gán ai',
                'title' => 'Hiện khi dự án chưa được gán nhân viên phụ trách nào.',
            ],

            'form_heading' => ['type' => 'text', 'label' => 'Form đăng ký - tiêu đề'],
            'form_note' => ['type' => 'textarea', 'label' => 'Form đăng ký - mô tả'],
            'form_name' => ['type' => 'text', 'label' => 'Form đăng ký - nhãn ô họ tên'],
            'form_phone' => ['type' => 'text', 'label' => 'Form đăng ký - nhãn ô số điện thoại'],
            'form_interest' => ['type' => 'text', 'label' => 'Form đăng ký - nhãn ô nhu cầu quan tâm'],
            'form_interest_options' => [
                'type' => 'textarea',
                'label' => 'Form đăng ký - các lựa chọn của ô nhu cầu quan tâm',
                'title' => 'Mỗi dòng một lựa chọn. Để trống thì ô này không hiện.',
            ],
            'form_timeline' => ['type' => 'text', 'label' => 'Form đăng ký - nhãn ô thời gian dự kiến mua'],
            'form_timeline_options' => [
                'type' => 'textarea',
                'label' => 'Form đăng ký - các lựa chọn của ô thời gian dự kiến mua',
                'title' => 'Mỗi dòng một lựa chọn. Để trống thì ô này không hiện.',
            ],
            'form_button' => ['type' => 'text', 'label' => 'Form đăng ký - chữ trên nút'],
            'form_privacy' => ['type' => 'text', 'label' => 'Form đăng ký - dòng cam kết bảo mật'],
        ];
    }

    /** Bon dong so lieu canh banner - moi dong co gia tri, nhan va hinh. */
    private function oSoLieu(array $icon): array
    {
        $o = [];

        for ($i = 1; $i <= 4; $i++) {
            $o["{$i}_value"] = ['type' => 'text', 'label' => "Dòng {$i} - con số (VD: 100+)"];
            $o["{$i}_label"] = ['type' => 'text', 'label' => "Dòng {$i} - nhãn"];
            $o["{$i}_icon"] = ['type' => 'select', 'label' => "Dòng {$i} - hình", 'option' => $icon];
        }

        return $o;
    }

    /**
     * Bon o so lieu o dau trang danh sach du an.
     *
     * O "con so" nhan hai dau thay the, de hai so nay luon dung voi CSDL ma
     * quan tri van doi duoc cach viet:
     *     {du_an}  - tong so du an dang hien
     *     {tinh}   - so tinh/thanh dang co du an
     */
    private function oSoLieuDuAn(array $icon): array
    {
        $o = [];

        for ($i = 1; $i <= 4; $i++) {
            $o["stat_{$i}_value"] = [
                'type' => 'text',
                'label' => "Trang Dự án - ô số liệu {$i}: con số",
                'title' => 'Gõ {du_an} để lấy tổng số dự án, {tinh} để lấy số tỉnh/thành có dự án.',
            ];
            $o["stat_{$i}_label"] = ['type' => 'text', 'label' => "Trang Dự án - ô số liệu {$i}: nhãn"];
            $o["stat_{$i}_icon"] = ['type' => 'select', 'label' => "Trang Dự án - ô số liệu {$i}: hình", 'option' => $icon];
        }

        return $o;
    }

    /** Sau o "Thong tin huu ich" - moi o co tieu de, duong dan va hinh. */
    private function oHuuIch(array $icon): array
    {
        $o = ['heading' => ['type' => 'text', 'label' => 'Tiêu đề khối']];

        for ($i = 1; $i <= 6; $i++) {
            $o["{$i}_title"] = ['type' => 'text', 'label' => "Ô {$i} - tiêu đề"];
            $o["{$i}_url"] = ['type' => 'text', 'label' => "Ô {$i} - đường dẫn"];
            $o["{$i}_icon"] = ['type' => 'select', 'label' => "Ô {$i} - hình", 'option' => $icon];
            $o["{$i}_desc"] = ['type' => 'textarea', 'label' => "Ô {$i} - mô tả"];
        }

        return $o;
    }
}
