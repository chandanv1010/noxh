<?php

namespace App\Http\Controllers\Sale;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardController extends SaleController
{
    public function index()
    {
        $toi = $this->toiLaAi();

        $soLieu = [
            'duAn' => Product::cuaNhanVien($toi)->count(),
            'duAnHien' => Product::cuaNhanVien($toi)->where('products.publish', 2)->count(),
            'baiViet' => Post::where('user_id', $toi)->count(),
            'baiChoDuyet' => Post::where('user_id', $toi)->where('approval_status', 'pending')->count(),
        ];

        $duAnGanDay = Product::cuaNhanVien($toi)
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'products.id')
                  ->where('pl.language_id', '=', $this->language);
            })
            ->select('products.id', 'products.publish', 'products.image', 'products.status', 'pl.name')
            ->orderByDesc('products.updated_at')
            ->limit(5)
            ->get();

        return $this->khung('sale.dashboard.index', [
            'nguoiDung' => Auth::user(),
            'soLieu' => $soLieu,
            'duAnGanDay' => $duAnGanDay,
            'tieuDe' => 'Tổng quan',
        ]);
    }
}
