<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Thu tu menu ngoai website phai TRUNG voi thu tu trong man hinh quan tri.
 *
 * Man hinh quan tri doc menu bang `order DESC` va thao tac keo tha ghi
 * `order = so muc - vi tri`, nen muc tren cung mang so lon nhat. Truoc day
 * trang ngoai sap theo `lft`, ma `lft` khong doi khi keo tha - quan tri keo
 * xong thay dung y minh, ra ngoai website van thu tu cu.
 */
class NoxhMenuTest extends TestCase
{
    public function test_menu_dau_trang_dung_thu_tu_cua_quan_tri(): void
    {
        $this->kiemTra('main-menu', '/', 'nx-header__nav', '</nav>');
    }

    public function test_menu_chan_trang_dung_thu_tu_cua_quan_tri(): void
    {
        $this->kiemTra('footer-menu', '/tin-tuc', 'nx-footer__inner', 'nx-footer__bottom');
    }

    /**
     * Thu tu quan tri dang thay - dung chinh phep sap xep cua
     * MenuController::edit.
     */
    private function thuTuQuanTri(string $nhom)
    {
        return DB::table('menus as m')
            ->join('menu_catalogues as mc', 'mc.id', '=', 'm.menu_catalogue_id')
            ->join('menu_language as ml', function ($j) {
                $j->on('ml.menu_id', '=', 'm.id')->where('ml.language_id', '=', 1);
            })
            ->where('mc.keyword', $nhom)
            ->where('m.parent_id', 0)
            ->where('m.publish', 2)
            ->orderByDesc('m.order')
            ->orderBy('m.lft')
            ->pluck('ml.name');
    }

    private function kiemTra(string $nhom, string $duongDan, string $tu, string $den): void
    {
        $ten = $this->thuTuQuanTri($nhom);

        if ($ten->count() < 2) {
            $this->markTestSkipped("Nhom {$nhom} chua du hai muc de so thu tu.");
        }

        // Chi cat dung khoi menu ra ma so. Do ca trang thi "Huong dan" trong
        // tieu de mot bai viet cung tinh la mot muc menu.
        $html = $this->khoi($this->get($duongDan)->assertOk()->getContent(), $tu, $den);

        $truoc = -1;

        foreach ($ten as $t) {
            $viTri = strpos($html, e($t));

            $this->assertNotFalse($viTri, "Khong thay muc \"{$t}\" tren trang");
            $this->assertGreaterThan(
                $truoc,
                $viTri,
                "Muc \"{$t}\" dung sai cho - thu tu ngoai website khac voi quan tri"
            );

            $truoc = $viTri;
        }
    }

    /** Cat doan HTML nam giua hai moc. */
    private function khoi(string $html, string $tu, string $den): string
    {
        $a = strpos($html, $tu);
        $this->assertNotFalse($a, "Khong thay moc {$tu}");

        $b = strpos($html, $den, $a + strlen($tu));

        return $b === false ? substr($html, $a) : substr($html, $a, $b - $a);
    }
}
