<?php

namespace App\Http\Controllers\Backend\V1\Noxh\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * O chon du an dung chung cho cac man hinh con cua du an
 * (tien do, ho so phap ly, hoi dap, loai can ho, diem nhan).
 *
 * Nam rieng mot cho vi nam man hinh doi hoi dung mot danh sach: sua cach
 * hien ten du an o day la ca nam man hinh doi theo.
 */
trait ChonDuAn
{
    /**
     * Danh sach du an cho o chon.
     *
     * Ten du an nam o bang ngon ngu nen phai join; lay luon ma du an de phan
     * biet khi hai du an trung ten.
     */
    protected function danhSachDuAn()
    {
        return DB::table('products as p')
            ->leftJoin('product_language as pl', function ($join) {
                $join->on('pl.product_id', '=', 'p.id')
                     ->where('pl.language_id', '=', 1);
            })
            ->whereNull('p.deleted_at')
            ->orderByDesc('p.id')
            ->get([
                'p.id',
                DB::raw("CONCAT(COALESCE(pl.name, CONCAT('Du an #', p.id)), IF(p.code IS NULL OR p.code = '', '', CONCAT(' (', p.code, ')'))) AS name"),
            ]);
    }
}
