<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Models\UserCatalogue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Che do duyet noi dung cua nhan vien kinh doanh.
 *
 * Quan tri chon trong Cau hinh he thong -> Nhan vien kinh doanh. Bai kiem tra
 * chay ca hai chieu cho ca bai viet lan du an, cong voi nut duyet ben quan tri.
 */
class NoxhSaleApprovalTest extends TestCase
{
    private ?User $sale = null;
    private ?User $quanTri = null;
    private ?UserCatalogue $nhom = null;
    private array $duAnTao = [];
    private array $caiDatCu = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->quanTri = User::whereHas('user_catalogues.permissions', function ($q) {
            $q->where('canonical', 'product.update');
        })->where('publish', 2)->first();

        $this->nhom = UserCatalogue::firstOrCreate(
            ['name' => 'Nhóm sale thử nghiệm duyệt'],
            ['is_sale' => 1, 'publish' => 2]
        );
        $this->nhom->update(['is_sale' => 1, 'publish' => 2]);

        DB::table('users')->where('email', 'sale.duyet@example.com')->delete();

        $this->sale = User::create([
            'name' => 'Nhân viên thử nghiệm duyệt',
            'email' => 'sale.duyet@example.com',
            'password' => Hash::make('matkhau@123'),
            'user_catalogue_id' => $this->nhom->id,
            'publish' => 2,
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->caiDatCu as $keyword => $cu) {
            if ($cu === null) {
                DB::table('systems')->where('keyword', $keyword)->delete();
            } else {
                DB::table('systems')->where('keyword', $keyword)->update(['content' => $cu]);
            }
        }

        foreach ($this->duAnTao as $id) {
            DB::table('product_user')->where('product_id', $id)->delete();
            DB::table('product_language')->where('product_id', $id)->delete();
            DB::table('product_catalogue_product')->where('product_id', $id)->delete();
            DB::table('routers')->where('module_id', $id)
                ->where('controllers', 'App\Http\Controllers\Frontend\ProductController')->delete();
            DB::table('products')->where('id', $id)->delete();
        }

        if ($this->sale) {
            Post::where('user_id', $this->sale->id)->forceDelete();
            DB::table('product_user')->where('user_id', $this->sale->id)->delete();
            DB::table('users')->where('id', $this->sale->id)->delete();
        }

        if ($this->nhom) {
            DB::table('user_catalogues')->where('id', $this->nhom->id)->delete();
        }

        parent::tearDown();
    }

    /** Dat mot o cai dat va nho gia tri cu de tra lai o cuoi bai. */
    private function datCaiDat(string $keyword, string $giaTri): void
    {
        if (!array_key_exists($keyword, $this->caiDatCu)) {
            $this->caiDatCu[$keyword] = DB::table('systems')->where('keyword', $keyword)->value('content');
        }

        $ngonNgu = DB::table('languages')->where('canonical', 'vn')->value('id');

        if (DB::table('systems')->where('keyword', $keyword)->exists()) {
            DB::table('systems')->where('keyword', $keyword)->update(['content' => $giaTri]);
        } else {
            // systems.user_id la NOT NULL va co khoa ngoai - phai co nguoi tao.
            DB::table('systems')->insert([
                'keyword' => $keyword,
                'content' => $giaTri,
                'language_id' => $ngonNgu,
                'user_id' => $this->quanTri->id ?? DB::table('users')->min('id'),
            ]);
        }
    }

    private function boQuanTri(): void
    {
        if (!$this->quanTri) {
            $this->markTestSkipped('Không có tài khoản quản trị nào có quyền product.update.');
        }
    }

    private function guiBai(string $canonical): ?Post
    {
        $this->actingAs($this->sale)->post('/sale/bai-viet/them-moi', [
            'name' => 'Bài thử nghiệm chế độ duyệt',
            'canonical' => $canonical,
            'post_catalogue_id' => DB::table('post_catalogues')->value('id'),
        ]);

        return Post::where('user_id', $this->sale->id)->latest('id')->first();
    }

    private function themDuAn(string $canonical): ?Product
    {
        $this->actingAs($this->sale)->post('/sale/du-an/them-moi', [
            'name' => 'Dự án thử nghiệm chế độ duyệt',
            'canonical' => $canonical,
            'product_catalogue_id' => DB::table('product_catalogues')->value('id'),
            'status' => 'upcoming',
            'publish' => 2,
        ]);

        $id = DB::table('product_language')->where('canonical', $canonical)->value('product_id');

        if (!$id) {
            return null;
        }

        $this->duAnTao[] = $id;

        return Product::find($id);
    }

    // --- Bai viet -----------------------------------------------------------

    public function test_bat_duyet_thi_bai_viet_bi_an_cho_duyet(): void
    {
        $this->datCaiDat('sale_post_approval', 'on');

        $bai = $this->guiBai('bai-che-do-duyet-bat-' . time());

        $this->assertNotNull($bai);
        $this->assertSame('pending', $bai->approval_status);
        $this->assertSame(1, (int) $bai->publish);
    }

    public function test_tat_duyet_thi_bai_viet_hien_ngay(): void
    {
        $this->datCaiDat('sale_post_approval', 'off');

        $bai = $this->guiBai('bai-che-do-duyet-tat-' . time());

        $this->assertNotNull($bai);
        $this->assertSame('approved', $bai->approval_status);
        $this->assertSame(2, (int) $bai->publish, 'Tắt duyệt nhưng bài vẫn bị ẩn');
    }

    public function test_khong_co_o_cai_dat_thi_mac_dinh_la_phai_duyet(): void
    {
        DB::table('systems')->where('keyword', 'sale_post_approval')->delete();
        $this->caiDatCu['sale_post_approval'] = null;

        $bai = $this->guiBai('bai-che-do-duyet-mac-dinh-' . time());

        $this->assertSame('pending', $bai->approval_status,
            'Chưa cấu hình gì mà bài viết đã hiện thẳng ra website');
    }

    // --- Du an --------------------------------------------------------------

    public function test_mac_dinh_du_an_tu_them_khong_can_duyet(): void
    {
        DB::table('systems')->where('keyword', 'sale_project_approval')->delete();
        $this->caiDatCu['sale_project_approval'] = null;

        $duAn = $this->themDuAn('du-an-duyet-mac-dinh-' . time());

        $this->assertNotNull($duAn);
        $this->assertSame('approved', $duAn->approval_status);
        $this->assertSame(2, (int) $duAn->publish);
    }

    public function test_bat_duyet_thi_du_an_tu_them_bi_an(): void
    {
        $this->datCaiDat('sale_project_approval', 'on');

        $duAn = $this->themDuAn('du-an-duyet-bat-' . time());

        $this->assertNotNull($duAn);
        $this->assertSame('pending', $duAn->approval_status);
        $this->assertSame(1, (int) $duAn->publish, 'Bật duyệt nhưng dự án vẫn hiện ra website');
    }

    public function test_bat_duyet_khong_dung_toi_du_an_duoc_giao(): void
    {
        $this->datCaiDat('sale_project_approval', 'on');

        // Du an do quan tri tao (user_id khac) roi giao cho nhan vien.
        $duAn = Product::create([
            'product_catalogue_id' => DB::table('product_catalogues')->value('id'),
            'user_id' => $this->quanTri->id ?? null,
            'publish' => 2,
            'status' => 'building',
        ]);
        $this->duAnTao[] = $duAn->id;

        DB::table('product_language')->insert([
            'product_id' => $duAn->id,
            'language_id' => DB::table('languages')->where('canonical', 'vn')->value('id'),
            'name' => 'Dự án được giao',
            'canonical' => 'du-an-duoc-giao-' . $duAn->id,
        ]);

        $duAn->nhanVienKinhDoanh()->attach($this->sale->id, ['order' => 0]);

        $this->actingAs($this->sale)->post('/sale/du-an/' . $duAn->id . '/sua', [
            'name' => 'Dự án được giao, sửa lại',
            'canonical' => 'du-an-duoc-giao-' . $duAn->id,
            'product_catalogue_id' => $duAn->product_catalogue_id,
            'publish' => 2,
        ]);

        $duAn->refresh();

        $this->assertSame(2, (int) $duAn->publish,
            'Sửa một dòng chữ mà dự án quản trị giao cho lại bị gỡ khỏi website');
        $this->assertSame('approved', $duAn->approval_status);
    }

    // --- Nut duyet ben quan tri ---------------------------------------------

    public function test_danh_sach_bai_viet_hien_nut_duyet(): void
    {
        $this->boQuanTri();
        $this->datCaiDat('sale_post_approval', 'on');

        $bai = $this->guiBai('bai-hien-nut-duyet-' . time());

        // Diem de hong: bang danh sach chi chon mot so cot nhat dinh. Thieu cot
        // approval_status thi nut duyet khong bao gio hien ra.
        $html = $this->actingAs($this->quanTri)->get(route('post.index'))->assertOk()->getContent();

        $this->assertStringContainsString(route('post.approve', $bai->id), $html,
            'Danh sách bài viết không hiện nút duyệt cho bài đang chờ');
    }

    public function test_quan_tri_duyet_duoc_bai_viet(): void
    {
        $this->boQuanTri();
        $this->datCaiDat('sale_post_approval', 'on');

        $bai = $this->guiBai('bai-duoc-duyet-' . time());

        $this->actingAs($this->quanTri)->get(route('post.approve', $bai->id));

        $bai->refresh();

        $this->assertSame('approved', $bai->approval_status);
        $this->assertSame(2, (int) $bai->publish);
    }

    public function test_danh_sach_du_an_hien_nut_duyet(): void
    {
        $this->boQuanTri();
        $this->datCaiDat('sale_project_approval', 'on');

        $duAn = $this->themDuAn('du-an-hien-nut-duyet-' . time());

        $html = $this->actingAs($this->quanTri)->get(route('product.index'))->assertOk()->getContent();

        $this->assertStringContainsString(route('product.approve', $duAn->id), $html,
            'Danh sách dự án không hiện nút duyệt cho dự án đang chờ');
    }

    public function test_quan_tri_duyet_duoc_du_an(): void
    {
        $this->boQuanTri();
        $this->datCaiDat('sale_project_approval', 'on');

        $duAn = $this->themDuAn('du-an-duoc-duyet-' . time());

        $this->actingAs($this->quanTri)->get(route('product.approve', $duAn->id));

        $duAn->refresh();

        $this->assertSame('approved', $duAn->approval_status);
        $this->assertSame(2, (int) $duAn->publish);
    }

    // --- Man hinh cai dat ---------------------------------------------------

    public function test_man_hinh_cau_hinh_co_hai_o_nay(): void
    {
        $this->boQuanTri();

        $html = $this->actingAs($this->quanTri)->get(route('system.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Nhân viên kinh doanh', $html);
        $this->assertStringContainsString('config[sale_post_approval]', $html);
        $this->assertStringContainsString('config[sale_project_approval]', $html);
    }
}
