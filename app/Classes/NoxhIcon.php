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

        // Trang danh sách dự án
        'city' => 'Ba toà nhà (thành phố)',
        'map-pins' => 'Ghim cắm xuống bản đồ',
        'group' => 'Hai người',
        'verified' => 'Huy hiệu kiểm chứng',
        'filter' => 'Phễu lọc',
        'map' => 'Bản đồ gấp',
        'news' => 'Tờ báo',
        'bulb-rays' => 'Bóng đèn toả tia',
        'area' => 'Khung bốn góc (diện tích)',
        'units' => 'Ba khối xếp (số căn)',
        'sort' => 'Mũi tên lên xuống',

        // Trang chi tiết dự án
        'headset' => 'Tai nghe hỗ trợ',
        'play' => 'Nút phát video',
        'photo' => 'Khung ảnh có dấu cộng',
        'update' => 'Đồng hồ cập nhật',
        'directions' => 'Biển chỉ đường',
        'floor-plan' => 'Mặt bằng tầng',
        'door' => 'Cửa mở (căn hộ)',

        // Trang Phòng pháp lý
        'book' => 'Sách mở',
        'doc-line' => 'Tờ giấy có dòng kẻ',
        'people' => 'Hai người (nét viền)',
        'home-door' => 'Nhà có cửa sổ và cửa ra vào',
        'doc-pen' => 'Tờ giấy kèm bút',
        'clipboard-check' => 'Bảng kẹp có dấu tích',
        'sms' => 'Bong bóng chat ba chấm',
        'trust-doc' => 'Tờ giấy kèm khiên tích',
        'trust-live' => 'Đồng hồ có vạch tốc độ',
        'trust-chat' => 'Bong bóng chat kèm dấu tích',
        'trust-lock' => 'Bảng kẹp kèm dấu tích',

        // Trang Tin tuc - hinh cua tung chuyen muc o cot trai
        'news-all' => 'Khung tin (tất cả tin tức)',
        'scale-line' => 'Cân công lý (nét viền)',
        'trend' => 'Biểu đồ có đường đi lên',
        'bulb-line' => 'Bóng đèn có tia (nét viền)',
        'pin-line' => 'Ghim bản đồ (nét viền)',
        'calendar-line' => 'Lịch (nét viền)',
        'eye-line' => 'Con mắt (nét viền)',
        'link' => 'Mắt xích (đường dẫn)',

        // Trang Kiem tra dieu kien - hinh cua tung o dap an
        'medal' => 'Huy chương',
        'cottage' => 'Nhà mái dốc (nông thôn)',
        'storm' => 'Xoáy bão (thiên tai)',
        'factory' => 'Nhà máy',
        'military' => 'Khiên có người (lực lượng vũ trang)',
        'badge' => 'Thẻ tên (cán bộ, viên chức)',
        'school' => 'Mũ tốt nghiệp',
        'handshake' => 'Bắt tay (doanh nghiệp)',
        'family' => 'Gia đình có con',
        'dots' => 'Ba chấm (chưa xác định)',
        'wallet' => 'Ví tiền',
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
