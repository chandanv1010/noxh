<?php

namespace App\Classes;

/**
 * Khai bao cac o chu cua trang chu NOXH.vn.
 *
 * Gia tri luu trong bang introduces theo dang khoa - noi dung. Ten nhom o day
 * KHONG duoc ghep vao khoa (khac voi Cau hinh he thong), nen khoa la nguyen
 * ten o ben duoi.
 */
class Introduce
{
    public function config()
    {
        $data['brand'] = [
            'label' => 'Thương hiệu',
            'description' => 'Khẩu hiệu và mô tả ngắn hiện ở đầu trang và chân trang',
            'value' => [
                'brand_tagline' => ['type' => 'text', 'label' => 'Khẩu hiệu dưới logo'],
                'footer_description' => ['type' => 'textarea', 'label' => 'Mô tả ở chân trang'],
            ],
        ];

        $data['hero'] = [
            'label' => 'Khối 1: Banner trang chủ',
            'description' => 'Chữ và ảnh nền của khối lớn nhất ở đầu trang chủ',
            'value' => [
                'hero_image' => [
                    'type' => 'images',
                    'label' => 'Ảnh nền banner (máy tính)',
                    'title' => 'Ảnh NGANG. Dùng cho màn hình rộng, chữ nằm bên trái ảnh.',
                ],
                'hero_image_mobile' => [
                    'type' => 'images',
                    'label' => 'Ảnh nền banner (điện thoại)',
                    'title' => 'Ảnh DỌC, nên khoảng 900x1900. Trên điện thoại chữ nằm đè lên ảnh nên ảnh ngang sẽ không đủ chỗ. Để trống thì dùng luôn ảnh máy tính.',
                ],
                'hero_label' => ['type' => 'text', 'label' => 'Dòng chữ nhỏ phía trên (VD: Cổng thông tin)'],
                'hero_title' => ['type' => 'text', 'label' => 'Tiêu đề lớn (VD: Nhà ở xã hội)'],
                'hero_slogan' => ['type' => 'text', 'label' => 'Khẩu hiệu dưới tiêu đề'],
                'hero_description' => ['type' => 'textarea', 'label' => 'Mô tả ngắn'],

                'hero_usp_1' => ['type' => 'text', 'label' => 'Điểm mạnh 1 - tiêu đề'],
                'hero_usp_1_desc' => ['type' => 'text', 'label' => 'Điểm mạnh 1 - mô tả'],
                'hero_usp_2' => ['type' => 'text', 'label' => 'Điểm mạnh 2 - tiêu đề'],
                'hero_usp_2_desc' => ['type' => 'text', 'label' => 'Điểm mạnh 2 - mô tả'],
                'hero_usp_3' => ['type' => 'text', 'label' => 'Điểm mạnh 3 - tiêu đề'],
                'hero_usp_3_desc' => ['type' => 'text', 'label' => 'Điểm mạnh 3 - mô tả'],
            ],
        ];

        $data['stat'] = [
            'label' => 'Khối 2: Dải số liệu',
            'description' => 'Bốn con số trên dải xanh ngay dưới banner. Số sẽ chạy từ 0 lên khi cuộn tới.',
            'value' => [
                'stat_1_value' => ['type' => 'text', 'label' => 'Số liệu 1 (VD: 120+)'],
                'stat_1_label' => ['type' => 'text', 'label' => 'Nhãn số liệu 1'],
                'stat_2_value' => ['type' => 'text', 'label' => 'Số liệu 2'],
                'stat_2_label' => ['type' => 'text', 'label' => 'Nhãn số liệu 2'],
                'stat_3_value' => ['type' => 'text', 'label' => 'Số liệu 3'],
                'stat_3_label' => ['type' => 'text', 'label' => 'Nhãn số liệu 3'],
                'stat_4_value' => ['type' => 'text', 'label' => 'Số liệu 4'],
                'stat_4_label' => ['type' => 'text', 'label' => 'Nhãn số liệu 4'],
            ],
        ];

        $data['check'] = [
            'label' => 'Khối 3: Mời kiểm tra điều kiện',
            'description' => 'Khối mời người dùng làm bài kiểm tra điều kiện mua NOXH',
            'value' => [
                'check_title' => ['type' => 'text', 'label' => 'Tiêu đề'],
                'check_description' => ['type' => 'textarea', 'label' => 'Mô tả'],
                'check_step_1' => ['type' => 'text', 'label' => 'Bước 1 - tên'],
                'check_step_1_desc' => ['type' => 'text', 'label' => 'Bước 1 - mô tả'],
                'check_step_2' => ['type' => 'text', 'label' => 'Bước 2 - tên'],
                'check_step_2_desc' => ['type' => 'text', 'label' => 'Bước 2 - mô tả'],
                'check_step_3' => ['type' => 'text', 'label' => 'Bước 3 - tên'],
                'check_step_3_desc' => ['type' => 'text', 'label' => 'Bước 3 - mô tả'],
                'check_step_4' => ['type' => 'text', 'label' => 'Bước 4 - tên'],
                'check_step_4_desc' => ['type' => 'text', 'label' => 'Bước 4 - mô tả'],
                'check_step_5' => ['type' => 'text', 'label' => 'Bước 5 - tên (Kết quả)'],
                'check_step_5_desc' => ['type' => 'text', 'label' => 'Bước 5 - mô tả'],
            ],
        ];

        $data['useful'] = [
            'label' => 'Khối 4: Thông tin hữu ích',
            'description' => 'Năm ô dẫn sang các trang chính. Để trống tiêu đề thì ô đó không hiện.',
            'value' => [
                'useful_heading' => ['type' => 'text', 'label' => 'Tiêu đề khối'],
                'useful_1_title' => ['type' => 'text', 'label' => 'Ô 1 - tiêu đề'],
                'useful_1_desc' => ['type' => 'textarea', 'label' => 'Ô 1 - mô tả'],
                'useful_1_url' => ['type' => 'text', 'label' => 'Ô 1 - đường dẫn'],
                'useful_2_title' => ['type' => 'text', 'label' => 'Ô 2 - tiêu đề'],
                'useful_2_desc' => ['type' => 'textarea', 'label' => 'Ô 2 - mô tả'],
                'useful_2_url' => ['type' => 'text', 'label' => 'Ô 2 - đường dẫn'],
                'useful_3_title' => ['type' => 'text', 'label' => 'Ô 3 - tiêu đề'],
                'useful_3_desc' => ['type' => 'textarea', 'label' => 'Ô 3 - mô tả'],
                'useful_3_url' => ['type' => 'text', 'label' => 'Ô 3 - đường dẫn'],
                'useful_4_title' => ['type' => 'text', 'label' => 'Ô 4 - tiêu đề'],
                'useful_4_desc' => ['type' => 'textarea', 'label' => 'Ô 4 - mô tả'],
                'useful_4_url' => ['type' => 'text', 'label' => 'Ô 4 - đường dẫn'],
                'useful_5_title' => ['type' => 'text', 'label' => 'Ô 5 - tiêu đề'],
                'useful_5_desc' => ['type' => 'textarea', 'label' => 'Ô 5 - mô tả'],
                'useful_5_url' => ['type' => 'text', 'label' => 'Ô 5 - đường dẫn'],
            ],
        ];

        $data['page'] = [
            'label' => 'Chữ ở các trang trong',
            'description' => 'Tiêu đề và mô tả đầu các trang danh sách dự án, pháp lý, hồ sơ, tài chính, hỏi đáp',
            'value' => [
                'project_heading' => ['type' => 'text', 'label' => 'Trang Dự án - tiêu đề'],
                'project_description' => ['type' => 'textarea', 'label' => 'Trang Dự án - mô tả'],
                'legal_heading' => ['type' => 'text', 'label' => 'Trang Pháp lý - tiêu đề'],
                'legal_description' => ['type' => 'textarea', 'label' => 'Trang Pháp lý - mô tả'],
                'dossier_heading' => ['type' => 'text', 'label' => 'Trang Hồ sơ - tiêu đề'],
                'dossier_description' => ['type' => 'textarea', 'label' => 'Trang Hồ sơ - mô tả'],
                'finance_heading' => ['type' => 'text', 'label' => 'Trang Tài chính - tiêu đề'],
                'finance_description' => ['type' => 'textarea', 'label' => 'Trang Tài chính - mô tả'],
                'qa_heading' => ['type' => 'text', 'label' => 'Trang Hỏi đáp - tiêu đề'],
                'qa_description' => ['type' => 'textarea', 'label' => 'Trang Hỏi đáp - mô tả'],
                'news_heading' => ['type' => 'text', 'label' => 'Trang Tin tức - tiêu đề'],
                'news_description' => ['type' => 'textarea', 'label' => 'Trang Tin tức - mô tả'],
            ],
        ];

        return $data;
    }
}
