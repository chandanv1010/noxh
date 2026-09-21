<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\LoanPackage;

/**
 * Hai cong cu tinh toan. Phep tinh chay thang tren trinh duyet de nguoi dung
 * keo thanh truot la thay so doi ngay, khong phai gui len may chu.
 *
 * Danh sach goi vay va lai suat thi lay tu CSDL - lai suat doi lien tuc nen
 * bat buoc phai sua duoc trong quan tri.
 */
class FinanceController extends FrontendController
{
    public function index()
    {
        return $this->loan();
    }

    public function loan()
    {
        return view('frontend.noxh.finance.loan', [
            'system' => $this->system,
            'seo' => $this->seo('Tính khoản vay mua nhà ở xã hội', url('/tai-chinh/tinh-khoan-vay')),
            'goiVay' => $this->goiVay(),
        ]);
    }

    public function capacity()
    {
        return view('frontend.noxh.finance.capacity', [
            'system' => $this->system,
            'seo' => $this->seo('Tính khả năng tài chính', url('/tai-chinh/kha-nang-tai-chinh')),
            'goiVay' => $this->goiVay(),
        ]);
    }

    private function goiVay()
    {
        return LoanPackage::where('publish', 2)
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->get();
    }

    private function seo(string $tieuDe, string $canonical, string $moTa = ''): array
    {
        return [
            'meta_title' => $tieuDe . ' - NOXH.vn',
            'meta_description' => $moTa ?: ($this->system['seo_meta_description'] ?? ''),
            'meta_image' => $this->system['seo_meta_images'] ?? '',
            'canonical' => $canonical,
        ];
    }
}
