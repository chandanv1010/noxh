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
    /**
     * Khoa bo nho tam trong container.
     *
     * Dung container chu khong dung bien static: mot luot chay PHP that chi
     * phuc vu mot request nen hai cach giong nhau, nhung khi chay test (hoac
     * sau nay chay Octane) thi nhieu request di qua cung mot tien trinh -
     * bien static se giu lai du lieu cu va trang hien ra khong dung voi CSDL.
     */
    private const KHOA_CHUNG = 'noxh.composer.chung';
    private const KHOA_INTRO = 'noxh.composer.intro';

    public function compose(View $view): void
    {
        $view->with($this->duLieu());
    }

    private function duLieu(): array
    {
        if (app()->bound(self::KHOA_CHUNG)) {
            return app(self::KHOA_CHUNG);
        }

        $duLieu = [
            'menuChinh' => $this->menu('main-menu'),
            'menuChan' => $this->menu('footer-menu'),
            'chuyenGia' => $this->chuyenGia(),
            'intro' => self::intro(),
        ];

        app()->instance(self::KHOA_CHUNG, $duLieu);

        return $duLieu;
    }

    /**
     * Cac o chu quan tri sua duoc, dang khoa => noi dung.
     *
     * Static va co bo nho tam vi controller can doc truoc khi view chay
     * (vi du trang danh sach du an dung noi dung nay de dung the SEO va dai
     * so lieu), con composer thi chay sau - hai noi phai dung chung mot lan
     * truy van.
     */
    public static function intro(): array
    {
        if (app()->bound(self::KHOA_INTRO)) {
            return app(self::KHOA_INTRO);
        }

        $intro = DB::table('introduces')->where('language_id', 1)
            ->pluck('content', 'keyword')->toArray();

        app()->instance(self::KHOA_INTRO, $intro);

        return $intro;
    }

    /**
     * Doc mot nhom menu thanh cay hai cap.
     *
     * SAP XEP THEO COT `order`, GIAM DAN - dung chinh thu tu man hinh quan
     * tri dang dung (MenuController::edit doc `order DESC`, va thao tac keo
     * tha ghi `order = so muc - vi tri` nen muc tren cung mang so lon nhat).
     *
     * Truoc day cho nay sap theo `lft`, nhung keo tha trong quan tri chi ghi
     * lai `order` va `parent_id`; ham dung lai cay nested set thi doc chinh
     * `lft` cu nen `lft` khong bao gio doi theo. Ket qua: quan tri keo xong
     * thay dung thu tu minh muon, ra ngoai website van y nguyen thu tu cu.
     * `lft` chi con dung de tach cac muc bang nhau cho on dinh.
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
            ->orderByDesc('m.order')
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
