<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Trang chi tiet du an, dung lai theo ban thiet ke
 * noxh_image/product-detail-fix.jpg.
 *
 * Trong tam la hai loi hua voi nguoi dung:
 *
 *   1. Khong mot chu nao tren trang duoc viet cung - sua o quan tri la trang
 *      doi theo.
 *   2. Khoi "Danh sách tư vấn hỗ trợ" lay dung nhung nguoi duoc gan vao CHINH
 *      du an dang xem, khong phai danh sach tu van vien mac dinh.
 *
 * Khong dung RefreshDatabase - chay tren CSDL dang phat trien, giong cac test
 * frontend khac. Bai nao phai sua du lieu thi tra lai nguyen trang trong
 * finally.
 */
class NoxhProjectDetailTest extends TestCase
{
    /** Mot du an bat ky de mo trang chi tiet. */
    private function duAn(): ?object
    {
        return DB::table('products as p')
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'p.id')->where('pl.language_id', 1);
            })
            ->whereNull('p.deleted_at')->where('p.publish', 2)
            ->first(['p.id', 'p.province_code', 'p.latitude', 'p.longitude', 'pl.canonical', 'pl.name']);
    }

    /** Doi tam mot o quan tri, chay $viec, roi tra lai gia tri cu. */
    private function doiO(string $khoa, ?string $moi, callable $viec): void
    {
        $cu = DB::table('introduces')
            ->where('keyword', $khoa)->where('language_id', 1)->value('content');

        DB::table('introduces')
            ->where('keyword', $khoa)->where('language_id', 1)
            ->update(['content' => $moi]);

        try {
            $viec();
        } finally {
            DB::table('introduces')
                ->where('keyword', $khoa)->where('language_id', 1)
                ->update(['content' => $cu]);
        }
    }

    public function test_trang_chi_tiet_mo_duoc_va_co_the_gia(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

        foreach (['projectdetail_price_label', 'projectdetail_price_button'] as $khoa) {
            $chu = DB::table('introduces')->where('keyword', $khoa)->where('language_id', 1)->value('content');
            $this->assertStringContainsString($chu, $html, "Thieu o chu {$khoa}");
        }
    }

    public function test_nhan_o_thong_so_lay_tu_quan_tri(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $this->doiO('projectdetail_spec_1_label', 'Tổng quy mô khu đất', function () use ($d) {
            $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();
            $this->assertStringContainsString('Tổng quy mô khu đất', $html);
        });
    }

    public function test_nhan_dong_bang_tong_quan_lay_tu_quan_tri(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $this->doiO('projectdetail_row_name', 'Tên gọi thương mại', function () use ($d) {
            $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();
            $this->assertStringContainsString('Tên gọi thương mại', $html);
            $this->assertStringNotContainsString('<th>Tên dự án</th>', $html);
        });
    }

    public function test_xoa_trang_nhan_thi_dong_do_khong_hien(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $this->doiO('projectdetail_row_ownership', '', function () use ($d) {
            $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();
            $this->assertStringNotContainsString('<th>Hình thức sở hữu</th>', $html);
        });
    }

    public function test_dau_tinh_trong_tieu_de_duoc_thay_bang_ten_tinh_that(): void
    {
        $d = DB::table('products as p')
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'p.id')->where('pl.language_id', 1);
            })
            ->join('vn_provinces as pr', 'pr.code', '=', 'p.province_code')
            ->whereNull('p.deleted_at')->where('p.publish', 2)
            ->first(['pl.canonical', 'pr.name as tinh']);

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao gan tinh.');
        }

        $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

        $this->assertStringNotContainsString('{tinh}', $html, 'Dau {tinh} lot ra ngoai trang');
        $this->assertStringContainsString(nx_ten_dia_gioi_ngan($d->tinh), $html);
    }

    public function test_o_diem_nhan_rieng_cua_du_an_de_len_bon_o_mac_dinh(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $macDinh = DB::table('introduces')
            ->where('keyword', 'projectdetail_point_1_title')->where('language_id', 1)
            ->value('content');

        $id = DB::table('project_highlights')->insertGetId([
            'product_id' => $d->id,
            'group' => 'price',
            'icon' => 'verified',
            'title' => 'Điểm nhấn riêng',
            'subtitle' => 'của dự án',
            'order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

            // Chi soi trong THE GIA. "Vị trí" con xuat hien o bang Tong quan
            // va tren thanh tab, doi chieu ca trang la bat nham.
            $dau = strpos($html, 'nx-pd-gia__o');
            $cuoi = strpos($html, 'nx-pd-gia__nut');
            $theGia = ($dau !== false && $cuoi !== false) ? substr($html, $dau, $cuoi - $dau) : '';

            $this->assertStringContainsString('Điểm nhấn riêng', $theGia);

            if ($macDinh) {
                $this->assertStringNotContainsString(
                    '>' . $macDinh . '<',
                    $theGia,
                    'Du an da khai diem nhan rieng ma the gia van in o mac dinh'
                );
            }
        } finally {
            DB::table('project_highlights')->where('id', $id)->delete();
        }
    }

    public function test_the_loai_can_ho_hien_ten_dien_tich_va_gia(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $id = DB::table('project_units')->insertGetId([
            'product_id' => $d->id,
            'name' => 'Căn thử nghiệm 9PN',
            'area_from' => 19.55,
            'area_to' => 21,
            'price_from' => 1.075,
            'price_to' => 1.180,
            'price_unit' => 'tỷ',
            'bullets' => "Gạch đầu dòng thử\nGạch thứ hai",
            'publish' => 2,
            'order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

            $this->assertStringContainsString('Căn thử nghiệm 9PN', $html);
            $this->assertStringContainsString('19,55 – 21 m²', $html);
            // Gia can tinh bang ty phai giu ba chu so le, lam tron hai so la
            // lech hang trieu dong. Hai dau cung phai cung so chu so le:
            // "1,075 - 1,18" nhin nhu mot loi danh may.
            $this->assertStringContainsString('1,075 – 1,180 tỷ', $html);
            $this->assertStringContainsString('Gạch đầu dòng thử', $html);
        } finally {
            DB::table('project_units')->where('id', $id)->delete();
        }
    }

    public function test_danh_sach_tu_van_lay_tu_nguoi_duoc_gan_vao_du_an(): void
    {
        $d = DB::table('products as p')
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'p.id')->where('pl.language_id', 1);
            })
            ->join('product_user as pu', 'pu.product_id', '=', 'p.id')
            ->join('users as u', 'u.id', '=', 'pu.user_id')
            ->whereNull('p.deleted_at')->where('p.publish', 2)->where('u.publish', 2)
            ->orderBy('pu.order')
            ->first(['pl.canonical', 'u.id as user_id', 'u.name as user_name']);

        if (!$d) {
            $this->markTestSkipped('Chua du an nao duoc gan nhan vien.');
        }

        $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

        $this->assertStringContainsString($d->user_name, $html);

        // Nguoi KHONG duoc gan vao du an nay thi khong duoc hien - day chinh
        // la cho de sai nhat: lay nham danh sach tu van vien mac dinh.
        $nguoiKhac = DB::table('users as u')
            ->join('user_catalogues as uc', 'uc.id', '=', 'u.user_catalogue_id')
            ->where('uc.is_sale', 1)->where('u.publish', 2)->whereNull('u.deleted_at')
            ->whereNotExists(function ($q) use ($d) {
                $q->selectRaw(1)->from('product_user')
                  ->join('product_language as pl2', function ($j) {
                      $j->on('pl2.product_id', '=', 'product_user.product_id')
                        ->where('pl2.language_id', 1);
                  })
                  ->where('pl2.canonical', $d->canonical)
                  ->whereColumn('product_user.user_id', 'u.id');
            })
            ->value('u.name');

        if ($nguoiKhac) {
            $this->assertStringNotContainsString(
                $nguoiKhac,
                $html,
                'Trang dang in ca nhan vien KHONG duoc gan vao du an nay'
            );
        }
    }

    public function test_du_an_chua_gan_ai_thi_hien_loi_moi_de_lai_so(): void
    {
        $canonical = DB::table('product_language as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->whereNull('p.deleted_at')->where('p.publish', 2)
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)->from('product_user')
                  ->whereColumn('product_user.product_id', 'p.id');
            })
            ->value('pl.canonical');

        if (!$canonical) {
            $this->markTestSkipped('Du an nao cung da co nguoi phu trach.');
        }

        $chu = DB::table('introduces')
            ->where('keyword', 'projectlead_staff_empty')->where('language_id', 1)
            ->value('content');

        $this->get('/du-an/' . $canonical)->assertOk()->assertSee($chu, false);
    }

    public function test_thanh_tab_chi_liet_ke_khoi_co_du_lieu(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

        // Khoi Tong quan luon co du lieu (it nhat la ten du an) nen tab nay
        // phai co mat.
        $this->assertStringContainsString('data-nx-tab="overview"', $html);

        // Du an khong co hoi dap thi khong duoc ve tab dan xuong cho trong.
        $coFaq = DB::table('project_faqs')
            ->where('product_id', $d->id)->where('publish', 2)->exists();

        if (!$coFaq) {
            $this->assertStringNotContainsString('data-nx-tab="faq"', $html);
        }
    }

    public function test_form_dang_ky_co_du_bon_o(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

        foreach (['name="name"', 'name="phone"', 'name="interest"', 'name="buy_timeline"'] as $o) {
            $this->assertStringContainsString($o, $html, "Form dang ky thieu o {$o}");
        }
    }

    public function test_form_dang_ky_luu_duoc_thoi_gian_du_kien_mua(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $sdt = '09' . random_int(10000000, 99999999);

        $this->post('/de-lai-thong-tin', [
            'name' => 'Khách kiểm thử',
            'phone' => $sdt,
            'source' => 'project_detail',
            'product_id' => $d->id,
            'interest' => 'Căn 2 phòng ngủ',
            'buy_timeline' => 'Trong 3 tháng tới',
        ])->assertRedirect();

        try {
            $dong = DB::table('contacts')->where('phone', $sdt)->first();

            $this->assertNotNull($dong, 'Khong luu duoc lead');
            $this->assertSame('Căn 2 phòng ngủ', $dong->interest);
            $this->assertSame('Trong 3 tháng tới', $dong->buy_timeline);
        } finally {
            DB::table('contacts')->where('phone', $sdt)->delete();
        }
    }

    public function test_khong_khai_link_ban_do_thi_tu_dung_tu_toa_do(): void
    {
        $d = DB::table('products as p')
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'p.id')->where('pl.language_id', 1);
            })
            ->whereNull('p.deleted_at')->where('p.publish', 2)
            ->whereNotNull('p.latitude')->whereNotNull('p.longitude')
            ->where(function ($q) {
                $q->whereNull('p.map_url')->orWhere('p.map_url', '');
            })
            ->first(['pl.canonical', 'p.latitude', 'p.longitude']);

        if (!$d) {
            $this->markTestSkipped('Khong co du an nao co toa do ma chua khai link ban do.');
        }

        $this->get('/du-an/' . $d->canonical)
            ->assertOk()
            ->assertSee(rawurlencode($d->latitude . ',' . $d->longitude), false);
    }

    public function test_ngay_thang_hien_theo_quy(): void
    {
        $this->assertSame('Quý I/2024', nx_quy_nam('2024-01-15'));
        $this->assertSame('Quý II/2024', nx_quy_nam('2024-06-30'));
        $this->assertSame('Quý IV/2026', nx_quy_nam('2026-10-01'));
        $this->assertSame('', nx_quy_nam(null));
    }

    public function test_moi_tab_co_mot_khung_noi_dung_di_kem(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

        preg_match_all('/data-nx-tab="([a-z]+)"/', $html, $m);
        $tab = array_unique($m[1]);

        $this->assertNotEmpty($tab, 'Trang khong ve tab nao');

        // Bam mot tab thi khung cung ma phai co san trong trang - khong co
        // thi bam vao la mat trang, chu khong phai doi noi dung.
        foreach ($tab as $ma) {
            $this->assertStringContainsString(
                'data-nx-pane="' . $ma . '"',
                $html,
                "Tab {$ma} khong co khung noi dung di kem"
            );
        }

        // Dung mot khung duoc mo san, khong phai khong cai nao hay hai cai.
        $this->assertSame(1, substr_count($html, 'nx-pd-khung__o is-hien'));
    }

    public function test_giay_to_tach_hai_tab_theo_nhom(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $idA = DB::table('project_documents')->insertGetId([
            'product_id' => $d->id, 'group' => 'legal',
            'title' => 'Giay to phap ly thu nghiem', 'publish' => 2, 'order' => 90,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $idB = DB::table('project_documents')->insertGetId([
            'product_id' => $d->id, 'group' => 'doc',
            'title' => 'Tai lieu tai ve thu nghiem', 'publish' => 2, 'order' => 91,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        try {
            $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

            $phapLy = $this->khung($html, 'legal');
            $taiLieu = $this->khung($html, 'doc');

            $this->assertStringContainsString('Giay to phap ly thu nghiem', $phapLy);
            $this->assertStringNotContainsString('Tai lieu tai ve thu nghiem', $phapLy);

            $this->assertStringContainsString('Tai lieu tai ve thu nghiem', $taiLieu);
            $this->assertStringNotContainsString('Giay to phap ly thu nghiem', $taiLieu);
        } finally {
            DB::table('project_documents')->whereIn('id', [$idA, $idB])->delete();
        }
    }

    public function test_du_an_tuong_tu_lay_theo_lua_chon_cua_quan_tri(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        // Mot du an o TINH KHAC - danh sach tu doc theo tinh se khong bao gio
        // lay no, nen no chung minh duoc lua chon cua quan tri thang the.
        $khac = DB::table('products as p')
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'p.id')->where('pl.language_id', 1);
            })
            ->whereNull('p.deleted_at')->where('p.publish', 2)
            ->where('p.id', '!=', $d->id)
            ->where(function ($q) use ($d) {
                $q->where('p.province_code', '!=', $d->province_code)
                  ->orWhereNull('p.province_code');
            })
            ->first(['p.id', 'pl.name']);

        if (!$khac) {
            $this->markTestSkipped('Khong co du an nao o tinh khac de thu.');
        }

        $cu = DB::table('product_related')->where('product_id', $d->id)->get();
        DB::table('product_related')->where('product_id', $d->id)->delete();

        DB::table('product_related')->insert([
            'product_id' => $d->id, 'related_id' => $khac->id, 'order' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        try {
            $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();
            $this->assertStringContainsString(e($khac->name), $html);
        } finally {
            DB::table('product_related')->where('product_id', $d->id)->delete();

            foreach ($cu as $o) {
                DB::table('product_related')->insert([
                    'product_id' => $o->product_id, 'related_id' => $o->related_id,
                    'order' => $o->order, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function test_hop_tien_do_liet_ke_day_du_con_khoi_nho_thi_cat_bot(): void
    {
        $d = $this->duAn();

        if (!$d) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $them = [];

        // Nhoi cho vuot han nguong SO_MOC_TIEN_DO de thay duoc su khac nhau.
        for ($i = 0; $i < 7; $i++) {
            $them[] = DB::table('project_milestones')->insertGetId([
                'product_id' => $d->id,
                'title' => 'Moc thu nghiem so ' . $i,
                'date_label' => 'Quý I/2030',
                'status' => 'pending',
                'order' => 500 + $i,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        try {
            $html = $this->get('/du-an/' . $d->canonical)->assertOk()->getContent();

            $hop = $this->hop($html, 'tien-do-day');

            // Hop bat len phai co DU moc, ke ca moc cuoi cung.
            $this->assertStringContainsString('Moc thu nghiem so 6', $hop);

            // Khoi nho canh ban do chi ve bon moc dau.
            $khoiNho = substr($html, strpos($html, 'nx-pd-tiendo'), 4000);
            $this->assertStringNotContainsString('Moc thu nghiem so 6', $khoiNho);
        } finally {
            DB::table('project_milestones')->whereIn('id', $them)->delete();
        }
    }

    /**
     * Tu van vien phu trach du an phai hien ANH CHAN DUNG, khong phai dia
     * chu cai.
     *
     * Anh cat tu ban ve (noxh_image/product-detail-fix.jpg) bang lenh
     * `python tools/tach-anh-ban-ve.py`, roi
     * `php artisan db:seed --class=NoxhDemoDataSeeder` gan vao tung nguoi.
     */
    public function test_tu_van_vien_hien_anh_chan_dung(): void
    {
        $duAn = DB::table('product_language')->where('language_id', 1)
            ->whereNotNull('canonical')->where('canonical', '!=', '')->value('canonical');

        $this->assertNotEmpty($duAn, 'Chua co du an nao de mo');

        $html = $this->get('/du-an/' . $duAn)->assertOk()->getContent();

        if (!str_contains($html, 'nx-pd-tuvan__anh')) {
            $this->markTestSkipped('Du an nay chua gan tu van vien nao.');
        }

        $coAnh = DB::table('users')->whereNull('deleted_at')
            ->whereNotNull('image')->where('image', 'like', '/uploads/noxh/tv-%')
            ->pluck('image', 'name');

        if ($coAnh->isEmpty()) {
            $this->markTestSkipped('Chua cat anh tu van vien - chay `python tools/tach-anh-ban-ve.py`.');
        }

        $hien = 0;

        foreach ($coAnh as $ten => $anh) {
            if (!str_contains($html, e($ten))) {
                continue;
            }

            $this->assertStringContainsString($anh, $html, "Tu van vien {$ten} van chua hien anh");
            $hien++;
        }

        $this->assertGreaterThan(0, $hien, 'Khong tu van vien nao co anh xuat hien tren trang');
    }

    /**
     * Cat lay phan HTML cua MOT khung tab.
     *
     * Phai cat chu khong do ca trang: ten mot giay to cung xuat hien o khung
     * ben canh thi do ca trang se bao "co" du no nam sai cho.
     */
    private function khung(string $html, string $pane): string
    {
        $tu = strpos($html, 'data-nx-pane="' . $pane . '"');
        $this->assertNotFalse($tu, "Khong tim thay khung {$pane}");

        // Ket thuc o khung ke tiep, hoac het khoi tab neu day la khung cuoi.
        $den = strpos($html, 'data-nx-pane="', $tu + 20);

        return $den === false ? substr($html, $tu) : substr($html, $tu, $den - $tu);
    }

    /** Phan HTML cua mot hop bat len - cac hop deu nam o cuoi trang. */
    private function hop(string $html, string $id): string
    {
        $tu = strpos($html, 'id="' . $id . '"');
        $this->assertNotFalse($tu, "Khong tim thay hop {$id}");

        return substr($html, $tu);
    }

}
