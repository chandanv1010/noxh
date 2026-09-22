<?php

namespace App\Http\Controllers\Backend\V1;

use App\Http\Controllers\Controller;
use App\Models\EligibilityCheck;
use App\Models\Post;
use App\Models\Product;
use App\Models\QaQuestion;
use Illuminate\Support\Facades\DB;

/**
 * Trang dau cua khu quan tri.
 *
 * Truoc day day la bang dieu khien cua mot cua hang: doanh thu, don hang moi,
 * bieu do khach hang. NOXH.vn khong ban hang truc tuyen nen nhung con so do
 * luon bang 0. Gio hien dung nhung gi trang nay thuc su co.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $config = $this->config();
        $template = 'backend.dashboard.home.index';

        return view('backend.dashboard.layout', compact('template', 'config') + [
            'soLieu' => $this->soLieu(),
            'lienHeMoi' => $this->lienHeMoi(),
            'choDuyet' => $this->choDuyet(),
        ]);
    }

    private function soLieu(): array
    {
        return [
            [
                'ten' => 'Dự án',
                'so' => Product::count(),
                'phu' => Product::where('publish', 2)->count() . ' đang hiển thị',
                'icon' => 'fa-building',
                'mau' => 'navy',
                'route' => route('product.index'),
            ],
            [
                'ten' => 'Bài viết',
                'so' => Post::count(),
                'phu' => Post::where('publish', 2)->count() . ' đang hiển thị',
                'icon' => 'fa-newspaper-o',
                'mau' => 'primary',
                'route' => route('post.index'),
            ],
            [
                'ten' => 'Lượt kiểm tra điều kiện',
                'so' => EligibilityCheck::count(),
                'phu' => EligibilityCheck::whereDate('created_at', today())->count() . ' trong hôm nay',
                'icon' => 'fa-check-square-o',
                'mau' => 'info',
                'route' => route('eligibility.check.index'),
            ],
            [
                'ten' => 'Khách để lại thông tin',
                'so' => DB::table('contacts')->count(),
                'phu' => DB::table('contacts')->where('status', 'new')->count() . ' chưa xử lý',
                'icon' => 'fa-envelope-o',
                'mau' => 'warning',
                'route' => route('contact.index'),
            ],
        ];
    }

    private function lienHeMoi()
    {
        return DB::table('contacts')
            ->orderByDesc('id')
            ->limit(8)
            ->get(['id', 'name', 'phone', 'source', 'status', 'created_at']);
    }

    /**
     * Nhung thu dang cho quan tri xu ly. Day la ly do chinh de mo trang nay,
     * nen dat ngay tren cung thay vi bat nguoi dung di do tung module.
     */
    private function choDuyet(): array
    {
        return [
            'baiViet' => Post::where('approval_status', 'pending')->count(),
            'duAn' => Product::where('approval_status', 'pending')->count(),
            'cauHoi' => QaQuestion::where('status', 'pending')->count(),
        ];
    }

    private function config(): array
    {
        return ['seo' => ['create' => ['title' => 'Tổng quan']]];
    }
}
