<?php

namespace App\Classes;

/**
 * Bang mau pastel cua hinh tron dung trong o dap an "Kiem tra dieu kien".
 *
 * Mau do thang tu ban ve noxh_image/w-1.jpg: lay trung vi mau tren vanh cua
 * tung hinh tron (12 o dap an cua cau "Doi tuong"). Quan tri chon TEN mau
 * chu khong go ma mau - go tu do thi moi nguoi mot kieu, luoi dap an se loang
 * lo, va khong the doi ca bo mau cua trang chi bang mot cho sua.
 */
class NoxhTone
{
    /** Ten mau => [nhan hien trong o chon, nen hinh tron, mau net hinh]. */
    public const DANH_SACH = [
        'rose' => ['Hồng phấn', '#fbe3df', '#d9544a'],
        'sky' => ['Xanh da trời', '#e3f1fc', '#1f7ed6'],
        'green' => ['Xanh lá nhạt', '#e2f7e0', '#2f9e46'],
        'violet' => ['Tím nhạt', '#e7e6fa', '#5b5bd6'],
        'amber' => ['Vàng nhạt', '#fdf2dd', '#c9820f'],
        'teal' => ['Xanh ngọc', '#daf9f7', '#0f8f87'],
        'purple' => ['Tím oải hương', '#f2ecfb', '#7c4fd0'],
        'slate' => ['Xám xanh', '#ecf0f7', '#5d6b82'],
        'blue' => ['Xanh thương hiệu', '#e0ecfd', '#0a78f5'],
    ];

    /** Mau dung khi dap an chua chon mau nao. */
    public const MAC_DINH = 'blue';

    /** Dung lam 'option' cho o chon kieu select trong trang quan tri. */
    public static function chon(): array
    {
        $ra = [];

        foreach (self::DANH_SACH as $ten => $mau) {
            $ra[$ten] = $mau[0];
        }

        return $ra;
    }

    /** [nen, net] cua mot ten mau - ten sai thi tra ve mau mac dinh. */
    public static function mau(?string $ten): array
    {
        $mau = self::DANH_SACH[$ten] ?? self::DANH_SACH[self::MAC_DINH];

        return [$mau[1], $mau[2]];
    }
}
