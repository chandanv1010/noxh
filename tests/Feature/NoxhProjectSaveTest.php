<?php

namespace Tests\Feature;

use App\Models\Investor;
use App\Models\Product;
use App\Models\ProjectDocument;
use App\Models\ProjectFaq;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Luu mot du an nha o xa hoi kem cac o rieng, roi them mot moc tien do, mot
 * ho so phap ly va mot cau hoi thuong gap cho no.
 */
class NoxhProjectSaveTest extends TestCase
{
    private function quanTri(): User
    {
        return User::orderBy('id')->firstOrFail();
    }

    private function danhMuc(): int
    {
        return (int) DB::table('product_catalogues')->orderBy('id')->value('id');
    }

    public function test_luu_du_an_kem_cac_o_noxh(): void
    {
        $u = $this->quanTri();

        $cdt = Investor::create(['name' => 'Chu dau tu thu nghiem', 'publish' => 2]);

        $this->actingAs($u)->post('/product/store', [
            'name' => 'Du an thu nghiem tu dong',
            'canonical' => 'du-an-thu-nghiem-tu-dong',
            'product_catalogue_id' => $this->danhMuc(),
            'code' => 'DA-TEST',
            'publish' => 2,

            'investor_id' => $cdt->id,
            'status' => 'receiving',
            'province_code' => '01',
            'address' => 'Khu do thi thu nghiem',
            'latitude' => '21.0278',
            'longitude' => '105.8342',
            'price_from' => '19.55',
            'price_to' => '23.99',
            'area_from' => '32',
            'area_to' => '70',
            'total_units' => '1042',
            'total_land_area' => '8.12',
            'scale_description' => '4 toa, 25 tang',
            'apartment_types' => '1PN-1WC, 2PN-2WC',
            'ownership_type' => 'So huu 50 nam',
            'start_date' => '2026-03-01',
            'handover_date' => '2027-12-31',
            'timeline_label' => 'Quy IV/2027',
            'is_featured' => '1',
        ]);

        $duAn = Product::where('code', 'DA-TEST')->first();
        $this->assertNotNull($duAn, 'Khong luu duoc du an');

        // Cac o rieng cua NOXH phai vao dung cot chu khong bi bo qua.
        $this->assertSame($cdt->id, (int) $duAn->investor_id);
        $this->assertSame('receiving', $duAn->status);
        $this->assertSame('01', $duAn->province_code, 'Ma tinh mat so 0 dung dau');
        $this->assertSame('19.55', (string) $duAn->price_from);
        $this->assertSame('23.99', (string) $duAn->price_to);
        $this->assertSame(1042, (int) $duAn->total_units);
        $this->assertSame('21.0278000', (string) $duAn->latitude);
        $this->assertTrue((bool) $duAn->is_featured);

        // --- Ba man hinh con ------------------------------------------------
        $this->actingAs($u)->post('/project/milestone/store', [
            'product_id' => $duAn->id,
            'title' => 'Khoi cong xay dung',
            'date_label' => 'Quy II/2026',
            'status' => 'doing',
            'order' => 1,
        ])->assertRedirect(route('project.milestone.index'));

        $moc = ProjectMilestone::where('product_id', $duAn->id)->first();
        $this->assertNotNull($moc);
        $this->assertSame('doing', $moc->status);

        $this->actingAs($u)->post('/project/document/store', [
            'product_id' => $duAn->id,
            'title' => 'Quyet dinh chu truong dau tu',
            'group' => 'legal',
            'doc_number' => '123/QD-UBND',
            'issued_date' => '2026-03-15',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('project.document.index'));

        $hs = ProjectDocument::where('product_id', $duAn->id)->first();
        $this->assertNotNull($hs);
        $this->assertSame('123/QD-UBND', $hs->doc_number);

        $this->actingAs($u)->post('/project/faq/store', [
            'product_id' => $duAn->id,
            'question' => 'Bao gio mo ban?',
            'answer' => 'Du kien quy IV.',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('project.faq.index'));

        $this->assertNotNull(ProjectFaq::where('product_id', $duAn->id)->first());

        // Loc theo du an phai ra dung ban ghi vua tao.
        $this->actingAs($u)->get('/project/milestone/index?product_id=' . $duAn->id)
            ->assertOk()
            ->assertSee('Khoi cong xay dung');

        // --- Don dep ---------------------------------------------------------
        // Xoa du an la ba bang con tu xoa theo (khoa ngoai cascadeOnDelete).
        DB::table('product_language')->where('product_id', $duAn->id)->delete();
        DB::table('product_catalogue_product')->where('product_id', $duAn->id)->delete();
        DB::table('routers')->where('module_id', $duAn->id)->delete();
        $duAn->forceDelete();
        $cdt->forceDelete();

        $this->assertNull(ProjectMilestone::find($moc->id), 'Xoa du an nhung moc tien do van con');
    }

    public function test_o_so_de_trong_thi_luu_null_chu_khong_phai_0(): void
    {
        $u = $this->quanTri();

        $this->actingAs($u)->post('/product/store', [
            'name' => 'Du an khong so lieu',
            'canonical' => 'du-an-khong-so-lieu',
            'product_catalogue_id' => $this->danhMuc(),
            'code' => 'DA-TEST-2',
            'publish' => 2,
            'price_from' => '',
            'price_to' => '',
            'area_from' => '',
            'total_units' => '',
            'latitude' => '',
            'start_date' => '',
        ]);

        $duAn = Product::where('code', 'DA-TEST-2')->firstOrFail();

        // Gia 0 dong hay dien tich 0 m2 se lam vo bo loc ngoai website.
        $this->assertNull($duAn->price_from);
        $this->assertNull($duAn->area_from);
        $this->assertNull($duAn->total_units);
        $this->assertNull($duAn->latitude);
        $this->assertNull($duAn->start_date);

        DB::table('product_language')->where('product_id', $duAn->id)->delete();
        DB::table('product_catalogue_product')->where('product_id', $duAn->id)->delete();
        DB::table('routers')->where('module_id', $duAn->id)->delete();
        $duAn->forceDelete();
    }

    /**
     * Man hinh them du an khong con do ra cac o cua ma nguon ban hang.
     *
     * Bang `products` von la bang san pham (gia, ton kho, bao hanh, bien
     * the...). Nguoi nhap du an nhin thay nhung o do thi khong biet dien gi,
     * nen chung bi an di - nhung VAN phai duoc gui len de khong xoa mat gia
     * tri cu khi bam Luu.
     */
    public function test_man_hinh_du_an_an_cac_o_ban_hang(): void
    {
        $html = $this->actingAs($this->quanTri())->get('/product/create')
            ->assertOk()->getContent();

        // Khong con nhin thay cac khoi cua ban hang.
        foreach (['Tồn kho', 'Thời gian bảo hành', 'Mã Nhúng Video Demo',
                  'Cấu hình Ưu đãi', 'Sản phẩm có nhiều phiên bản'] as $chu) {
            $this->assertStringNotContainsString($chu, $html, "Van con o \"{$chu}\" tren man hinh du an");
        }

        // Nhung o ay van duoc gui len duoi dang o an - thieu chung thi ban
        // ghi moi se dung vao cot NOT NULL khong co gia tri.
        foreach (['code', 'price', 'stock', 'no_offer'] as $cot) {
            $this->assertMatchesRegularExpression(
                '/<input type="hidden" name="' . $cot . '"/',
                $html,
                "Thieu o an cho cot {$cot}"
            );
        }

        // Cac o RIENG cua du an thi van phai co.
        foreach (['price_from', 'area_from', 'province_code', 'ward_code',
                  'latitude', 'longitude', 'video_url'] as $cot) {
            $this->assertStringContainsString('name="' . $cot . '"', $html, "Mat o {$cot} cua du an");
        }
    }

    /** Danh sach du an in trang thai du an chu khong phai so ton kho. */
    public function test_danh_sach_du_an_in_trang_thai_thay_cho_ton_kho(): void
    {
        $html = $this->actingAs($this->quanTri())->get('/product/index')
            ->assertOk()->getContent();

        $this->assertStringContainsString('Trạng thái dự án', $html);
        $this->assertStringNotContainsString('Tồn kho', $html);
    }

    /**
     * Luu du an ma form KHONG gui danh muc len thi du an van phai hien o trang
     * quan tri.
     *
     * Trang /product/index liet ke bang INNER JOIN product_catalogue_product, nen
     * du an khong co dong lien ket se bien mat khoi danh sach - trong khi web
     * ngoai van hien binh thuong. Do la loi "bai nay khong thay trong admin de
     * sua" da gap that.
     */
    public function test_luu_ma_khong_chon_danh_muc_thi_van_hien_o_trang_quan_tri(): void
    {
        $u = $this->quanTri();

        // Don truoc: bai kiem tra khong dung RefreshDatabase, ma `products.code`
        // khong phai khoa duy nhat - chay lai lan hai ma con ban ghi cu thi
        // `first()` se tra ve dung ban cu do va ket qua doc ra sai.
        $this->xoaDuAnTheoMa('DA-KHONG-DM');

        $this->actingAs($u)->post('/product/store', [
            'name' => 'Du an khong chon danh muc',
            'canonical' => 'du-an-khong-chon-danh-muc',
            'code' => 'DA-KHONG-DM',
            'publish' => 2,
            // Co y KHONG gui product_catalogue_id.
        ]);

        $duAn = Product::where('code', 'DA-KHONG-DM')->latest('id')->first();
        $this->assertNotNull($duAn, 'Khong luu duoc du an');

        $this->assertTrue(
            $duAn->product_catalogues()->exists(),
            'Du an khong thuoc danh muc nao thi se khong hien o trang quan tri'
        );

        $html = $this->actingAs($u)->get('/product/index')->assertOk()->getContent();
        $this->assertStringContainsString('Du an khong chon danh muc', $html);

        $this->xoaDuAnTheoMa('DA-KHONG-DM');
    }

    /**
     * Xoa sach moi ban ghi du an mang ma nay, ke ca ban con sot tu lan chay truoc.
     *
     * Phai xoa ca dong trong `routers`: `canonical` co rang buoc unique, nen neu
     * con sot thi lan chay sau se hong ngay o buoc kiem tra du lieu chu khong
     * phai o cho dang kiem.
     */
    private function xoaDuAnTheoMa(string $ma): void
    {
        $ids = DB::table('products')->where('code', $ma)->pluck('id')->all();

        if ($ids) {
            DB::table('product_catalogue_product')->whereIn('product_id', $ids)->delete();
            DB::table('product_language')->whereIn('product_id', $ids)->delete();
            DB::table('products')->whereIn('id', $ids)->delete();
        }

        DB::table('routers')->where('canonical', 'du-an-khong-chon-danh-muc')->delete();
    }
}
