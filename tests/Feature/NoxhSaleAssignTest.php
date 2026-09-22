<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\UserCatalogue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phia quan tri: gan nhan vien kinh doanh vao du an.
 *
 * Kiem tra ca hai chieu - gan duoc VA go duoc. Chieu go la chieu de hong nhat:
 * trinh duyet khong gui o chon nhieu khi khong con muc nao duoc chon, nen neu
 * ma nguon chi nhin vao $request->has() thi danh sach cu se nam nguyen do.
 */
class NoxhSaleAssignTest extends TestCase
{
    private ?User $quanTri = null;
    private ?User $sale = null;
    private ?User $saleHai = null;
    private ?UserCatalogue $nhom = null;
    private ?Product $duAn = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quanTri = User::whereHas('user_catalogues.permissions', function ($q) {
            $q->where('canonical', 'product.update');
        })->where('publish', 2)->first();

        $this->nhom = UserCatalogue::firstOrCreate(
            ['name' => 'Nhóm sale thử nghiệm gán dự án'],
            ['is_sale' => 1, 'publish' => 2]
        );
        $this->nhom->update(['is_sale' => 1, 'publish' => 2]);

        $this->sale = $this->taoNhanVien('sale.gan.1@example.com', 'Nhân viên gán 1');
        $this->saleHai = $this->taoNhanVien('sale.gan.2@example.com', 'Nhân viên gán 2');

        $this->duAn = Product::create([
            'product_catalogue_id' => DB::table('product_catalogues')->value('id'),
            'publish' => 2,
            'status' => 'building',
            'is_featured' => 1,
        ]);

        DB::table('product_language')->insert([
            'product_id' => $this->duAn->id,
            'language_id' => DB::table('languages')->where('canonical', 'vn')->value('id'),
            'name' => 'Dự án thử nghiệm gán nhân viên',
            'canonical' => 'du-an-gan-nhan-vien-' . $this->duAn->id,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->duAn) {
            DB::table('product_user')->where('product_id', $this->duAn->id)->delete();
            DB::table('product_language')->where('product_id', $this->duAn->id)->delete();
            DB::table('product_catalogue_product')->where('product_id', $this->duAn->id)->delete();
            DB::table('routers')->where('module_id', $this->duAn->id)
                ->where('controllers', 'App\Http\Controllers\Frontend\ProductController')->delete();
            DB::table('products')->where('id', $this->duAn->id)->delete();
        }

        foreach ([$this->sale, $this->saleHai] as $u) {
            if ($u) {
                DB::table('product_user')->where('user_id', $u->id)->delete();
                DB::table('users')->where('id', $u->id)->delete();
            }
        }

        if ($this->nhom) {
            DB::table('user_catalogues')->where('id', $this->nhom->id)->delete();
        }

        parent::tearDown();
    }

    private function taoNhanVien(string $email, string $ten): User
    {
        DB::table('users')->where('email', $email)->delete();

        return User::create([
            'name' => $ten,
            'title' => 'Chuyên viên tư vấn',
            'email' => $email,
            'password' => Hash::make('matkhau@123'),
            'user_catalogue_id' => $this->nhom->id,
            'publish' => 2,
        ]);
    }

    private function boQuanTri(): void
    {
        if (!$this->quanTri) {
            $this->markTestSkipped('Không có tài khoản quản trị nào có quyền product.update.');
        }
    }

    /** Du lieu toi thieu de form du an cua quan tri luu duoc. */
    private function duLieuForm(array $them = []): array
    {
        return array_merge([
            'name' => 'Dự án thử nghiệm gán nhân viên',
            'canonical' => 'du-an-gan-nhan-vien-' . $this->duAn->id,
            'product_catalogue_id' => $this->duAn->product_catalogue_id,
            'is_featured' => 1,
            'publish' => 2,
            'co_gan_nhan_vien' => 1,
        ], $them);
    }

    public function test_form_du_an_co_o_chon_nhan_vien(): void
    {
        $this->boQuanTri();

        $html = $this->actingAs($this->quanTri)
            ->get(route('product.edit', $this->duAn->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Nhân viên kinh doanh phụ trách', $html);
        $this->assertStringContainsString('nhan_vien_kinh_doanh[]', $html);
        $this->assertStringContainsString('Nhân viên gán 1', $html);
    }

    public function test_form_them_moi_du_an_cung_mo_duoc(): void
    {
        // Man hinh them moi khong co bien $product - khoi chon nhan vien phai
        // chiu duoc dieu do.
        $this->boQuanTri();

        $this->actingAs($this->quanTri)->get(route('product.create'))->assertOk()
            ->assertSee('Nhân viên kinh doanh phụ trách', false);
    }

    public function test_gan_nhan_vien_vao_du_an(): void
    {
        $this->boQuanTri();

        $this->actingAs($this->quanTri)->post(
            route('product.update', $this->duAn->id),
            $this->duLieuForm(['nhan_vien_kinh_doanh' => [$this->sale->id, $this->saleHai->id]])
        );

        $daGan = DB::table('product_user')->where('product_id', $this->duAn->id)
            ->pluck('user_id')->map(fn ($v) => (int) $v)->sort()->values()->all();

        $this->assertSame(
            collect([$this->sale->id, $this->saleHai->id])->sort()->values()->all(),
            $daGan
        );
    }

    public function test_go_het_nhan_vien_khoi_du_an(): void
    {
        $this->boQuanTri();

        $this->duAn->nhanVienKinhDoanh()->attach($this->sale->id, ['order' => 0]);

        // Form gui len KHONG co nhan_vien_kinh_doanh (khong con muc nao duoc
        // chon) nhung van co o danh dau co_gan_nhan_vien.
        $this->actingAs($this->quanTri)->post(
            route('product.update', $this->duAn->id),
            $this->duLieuForm()
        );

        $this->assertSame(
            0,
            DB::table('product_user')->where('product_id', $this->duAn->id)->count(),
            'Bỏ hết nhân viên ra khỏi dự án nhưng danh sách cũ vẫn còn'
        );
    }

    public function test_form_khong_co_o_nay_thi_khong_dung_toi_danh_sach(): void
    {
        $this->boQuanTri();

        $this->duAn->nhanVienKinhDoanh()->attach($this->sale->id, ['order' => 0]);

        // Day chinh la truong hop form ben /sale: khong co o chon nhan vien,
        // nen cung khong duoc phep xoa danh sach dang co.
        $duLieu = $this->duLieuForm();
        unset($duLieu['co_gan_nhan_vien']);

        $this->actingAs($this->quanTri)->post(route('product.update', $this->duAn->id), $duLieu);

        $this->assertSame(
            1,
            DB::table('product_user')->where('product_id', $this->duAn->id)->count(),
            'Form không có ô chọn nhân viên nhưng vẫn xóa mất danh sách'
        );
    }

    public function test_chi_nhom_co_co_is_sale_moi_hien_ra_o_chon(): void
    {
        $this->boQuanTri();

        $this->nhom->update(['is_sale' => 0]);

        $html = $this->actingAs($this->quanTri)
            ->get(route('product.edit', $this->duAn->id))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Nhân viên gán 1', $html,
            'Nhóm đã tắt cờ is_sale nhưng thành viên vẫn hiện ở ô chọn');
    }
}
