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
            'description' => 'Tiêu đề khối dự án ở trang chủ và phần đầu trang danh sách dự án',
            'value' => [
                'block_heading' => ['type' => 'text', 'label' => 'Trang chủ - tiêu đề khối dự án nổi bật'],
                'more_text' => ['type' => 'text', 'label' => 'Trang chủ - chữ liên kết "xem tất cả"'],
                'heading' => ['type' => 'text', 'label' => 'Trang Dự án - tiêu đề'],
                'description' => ['type' => 'textarea', 'label' => 'Trang Dự án - mô tả'],
            ],
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
            'description' => 'Khối tin tức ở trang chủ và phần đầu trang Tin tức',
            'value' => [
                'block_heading' => ['type' => 'text', 'label' => 'Trang chủ - tiêu đề khối tin tức'],
                'more_text' => ['type' => 'text', 'label' => 'Trang chủ - chữ liên kết "xem tất cả"'],
                'heading' => ['type' => 'text', 'label' => 'Trang Tin tức - tiêu đề'],
                'description' => ['type' => 'textarea', 'label' => 'Trang Tin tức - mô tả'],
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

        // --- Chu o cac trang trong ---------------------------------------------
        foreach ([
            'legal' => 'Pháp lý',
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
