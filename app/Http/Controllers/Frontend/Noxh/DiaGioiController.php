<?php

namespace App\Http\Controllers\Frontend\Noxh;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Tra danh sach phuong/xa cua mot tinh cho o chon ben ngoai website.
 *
 * Ca nuoc co 3.321 phuong/xa - nhet het vao trang thi nang them gan 150 KB
 * cho mot o chon ma hau het nguoi dung khong dong toi. Goi rieng khi nguoi
 * dung chon tinh thi moi lan chi vai chuc dong.
 *
 * Du lieu nap tu API chinh thuc bang lenh `php artisan noxh:dia-gioi`.
 */
class DiaGioiController extends Controller
{
    public function phuongXa(string $maTinh): JsonResponse
    {
        // Ma tinh la hai chu so. Chan ngay tu day de khong dem chuoi la vao
        // truy van, va de tra ve 200 rong thay vi 500.
        if (!preg_match('/^\d{1,2}$/', $maTinh)) {
            return response()->json([]);
        }

        $maTinh = str_pad($maTinh, 2, '0', STR_PAD_LEFT);

        $xa = DB::table('vn_wards')
            ->where('province_code', $maTinh)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['code', 'name']);

        // Danh sach hanh chinh doi rat it - cho trinh duyet giu mot ngay.
        return response()->json($xa)
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
