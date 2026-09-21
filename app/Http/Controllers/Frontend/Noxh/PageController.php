<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\DB;

/**
 * Cac trang chu yeu la van ban: Gioi thieu, Chinh sach bao mat, Dieu khoan
 * su dung, Lien he va trang tu van cua chuyen gia.
 *
 * Noi dung lay tu bang introduces de quan tri sua duoc, khong ghi cung trong
 * ma nguon.
 */
class PageController extends FrontendController
{
    /** Khoa trong bang introduces cho tung trang. */
    private const TRANG = [
        'gioi-thieu' => ['about', 'Giới thiệu NOXH.vn'],
        'chinh-sach-bao-mat' => ['privacy', 'Chính sách bảo mật thông tin'],
        'dieu-khoan-su-dung' => ['terms', 'Điều khoản sử dụng'],
    ];

    public function show(string $duongDan)
    {
        if (!isset(self::TRANG[$duongDan])) {
            abort(404);
        }

        [$khoa, $tieuDeMacDinh] = self::TRANG[$duongDan];

        $chu = DB::table('introduces')->where('language_id', $this->language)
            ->whereIn('keyword', [$khoa . '_title', $khoa . '_content'])
            ->pluck('content', 'keyword')
            ->toArray();

        return view('frontend.noxh.page.text', [
            'system' => $this->system,
            'seo' => $this->seo($chu[$khoa . '_title'] ?? $tieuDeMacDinh, url('/' . $duongDan)),
            'tieuDe' => $chu[$khoa . '_title'] ?? $tieuDeMacDinh,
            'noiDung' => $chu[$khoa . '_content'] ?? '',
        ]);
    }

    public function contact()
    {
        return view('frontend.noxh.page.contact', [
            'system' => $this->system,
            'seo' => $this->seo('Liên hệ', url('/lien-he')),
        ]);
    }

    /** Trang tu van cua chuyen vien - muc "Cong Hoa" tren thanh dieu huong. */
    public function advise()
    {
        return view('frontend.noxh.page.advise', [
            'system' => $this->system,
            'seo' => $this->seo('Đăng ký tư vấn miễn phí', url('/cong-hoa/tu-van')),
        ]);
    }

    private function seo(string $tieuDe, string $canonical): array
    {
        return [
            'meta_title' => $tieuDe . ' - NOXH.vn',
            'meta_description' => $this->system['seo_meta_description'] ?? '',
            'meta_image' => $this->system['seo_meta_images'] ?? '',
            'canonical' => $canonical,
        ];
    }
}
