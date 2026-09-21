<?php

namespace App\Http\ViewComposers;

use App\Models\Expert;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Cap du lieu dung chung cho moi trang cua NOXH.vn:
 *
 *   $menuChinh   - thanh dieu huong tren dau, lay tu bang menus (nhom main-menu)
 *   $menuChan    - cot lien ket o chan trang (nhom footer-menu)
 *   $chuyenGia   - chuyen vien tu van mac dinh, xuat hien o 6/9 man hinh
 *   $intro       - cac o chu quan tri sua duoc (bang introduces)
 *
 * Composer chay cho MOI view duoc dung, ke ca tung @include, nen phai co bo
 * nho tam trong mot luot goi - neu khong mot trang se truy van CSDL vai chuc
 * lan cho cung mot thu.
 */
class NoxhComposer
{
    protected static $cache = [];

    public function compose(View $view): void
    {
        $view->with($this->duLieu());
    }

    private function duLieu(): array
    {
        if (!empty(self::$cache)) {
            return self::$cache;
        }

        return self::$cache = [
            'menuChinh' => $this->menu('main-menu'),
            'menuChan' => $this->menu('footer-menu'),
            'chuyenGia' => $this->chuyenGia(),
            'intro' => DB::table('introduces')->where('language_id', 1)
                ->pluck('content', 'keyword')->toArray(),
        ];
    }

    /**
     * Doc mot nhom menu thanh cay hai cap.
     *
     * Bang menus dung nested set (lft/rgt) de sap xep, con quan he cha con thi
     * doc theo parent_id cho de hieu.
     */
    private function menu(string $keyword): array
    {
        $dong = DB::table('menus as m')
            ->join('menu_catalogues as mc', 'mc.id', '=', 'm.menu_catalogue_id')
            ->join('menu_language as ml', function ($join) {
                $join->on('ml.menu_id', '=', 'm.id')->where('ml.language_id', '=', 1);
            })
            ->where('mc.keyword', $keyword)
            ->where('m.publish', 2)
            ->orderBy('m.lft')
            ->get(['m.id', 'm.parent_id', 'ml.name', 'ml.canonical']);

        $goc = [];
        $con = [];

        foreach ($dong as $d) {
            $muc = [
                'name' => $d->name,
                'url' => $d->canonical === '' ? url('/') : url('/' . ltrim($d->canonical, '/')),
                'canonical' => trim($d->canonical, '/'),
                'children' => [],
            ];

            if ((int) $d->parent_id === 0) {
                $goc[$d->id] = $muc;
            } else {
                $con[$d->parent_id][] = $muc;
            }
        }

        foreach ($con as $chaId => $ds) {
            if (isset($goc[$chaId])) {
                $goc[$chaId]['children'] = $ds;
            }
        }

        return array_values($goc);
    }

    private function chuyenGia()
    {
        return Expert::where('publish', 2)
            ->orderByDesc('is_default')
            ->orderBy('order')
            ->first();
    }
}
