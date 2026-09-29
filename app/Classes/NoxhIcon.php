<?php

namespace App\Classes;

/**
 * Danh sach hinh (icon) quan tri duoc chon trong cac o cau hinh.
 *
 * Ten o day chinh la ten dung khi goi
 * @include('frontend.noxh.component.icon', ['name' => ...]) - file Blade do
 * duoc sinh tu tools/build-icons.mjs, nguon la bo Material Symbols Rounded.
 *
 * Them hinh moi: khai bao trong tools/build-icons.mjs, chay lai lenh sinh
 * file, roi them mot dong o day de quan tri chon duoc.
 */
class NoxhIcon
{
    /** Ten hinh => nhan hien trong o chon cua quan tri. */
    public const DANH_SACH = [
        'house' => 'Ngôi nhà',
        'building' => 'Toà chung cư',
        'home' => 'Nhà (đơn giản)',
        'pin' => 'Ghim bản đồ',
        'users' => 'Nhóm người',
        'user' => 'Một người',
        'shield-check' => 'Khiên có dấu tích',
        'check-circle' => 'Dấu tích trong vòng tròn',
        'clock' => 'Đồng hồ báo thức',
        'schedule' => 'Đồng hồ',
        'lock' => 'Ổ khoá',
        'scale' => 'Cân công lý',
        'clipboard' => 'Bảng kẹp hồ sơ',
        'coins' => 'Chồng đồng xu',
        'bulb' => 'Bóng đèn',
        'question' => 'Dấu hỏi',
        'download' => 'Tải về',
        'money' => 'Tiền',
        'bank' => 'Ngân hàng',
        'calculator' => 'Máy tính',
        'piggy' => 'Lợn tiết kiệm',
        'chart' => 'Biểu đồ',
        'file' => 'Tài liệu',
        'folder' => 'Thư mục',
        'grid' => 'Lưới ô',
        'calendar' => 'Lịch',
        'ruler' => 'Thước đo',
        'layers' => 'Lớp',
        'search' => 'Kính lúp',
        'phone' => 'Điện thoại',
        'mail' => 'Phong bì',
        'send' => 'Máy bay giấy',
        'chat' => 'Bong bóng chat',
        'globe' => 'Quả địa cầu',
        'eye' => 'Con mắt',
        'info' => 'Thông tin',
        'warning' => 'Cảnh báo',
    ];

    /** Dung lam 'option' cho o chon kieu select trong trang cau hinh. */
    public static function chon(): array
    {
        return ['' => '— Không chọn —'] + self::DANH_SACH;
    }

    /** Ten hinh co hop le khong - dung khi doc gia tri tu CSDL ra. */
    public static function hopLe(?string $ten): bool
    {
        return $ten !== null && $ten !== '' && isset(self::DANH_SACH[$ten]);
    }
}
