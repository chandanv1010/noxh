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
 * Bang dieu khien /sale cua nhan vien kinh doanh.
 *
 * Trong tam la PHAN QUYEN THEO DONG: nhan vien chi duoc dung toi du an cua
 * minh. Mot man hinh danh sach loc dung van co the ho khi ham sua khong kiem
 * tra lai, nen o day thu thang bang duong dan.
 *
 * Khong dung RefreshDatabase: chay tren chinh CSDL dang phat trien, ban ghi
 * tao ra deu tu don o cuoi.
 */
class NoxhSaleTest extends TestCase
{
    private ?User $sale = null;
    private ?User $saleKhac = null;
    private ?UserCatalogue $nhom = null;
    private array $duAnTao = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->nhom = UserCatalogue::firstOrCreate(
            ['name' => 'Nhóm sale thử nghiệm tự động'],
            ['is_sale' => 1, 'publish' => 2]
        );
        $this->nhom->update(['is_sale' => 1, 'publish' => 2]);

        $this->sale = $this->taoNhanVien('sale.tudong.1@example.com', 'Nhân viên thử nghiệm 1');
        $this->saleKhac = $this->taoNhanVien('sale.tudong.2@example.com', 'Nhân viên thử nghiệm 2');
    }

    protected function tearDown(): void
    {
        foreach ($this->duAnTao as $id) {
            DB::table('product_user')->where('product_id', $id)->delete();
            DB::table('product_language')->where('product_id', $id)->delete();
            DB::table('product_catalogue_product')->where('product_id', $id)->delete();
            DB::table('routers')->where('module_id', $id)
                ->where('controllers', 'App\Http\Controllers\Frontend\ProductController')->delete();
            DB::table('products')->where('id', $id)->delete();
        }

        foreach ([$this->sale, $this->saleKhac] as $u) {
            if ($u) {
                Post::where('user_id', $u->id)->forceDelete();
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
            'phone' => '0900000100',
            'user_catalogue_id' => $this->nhom->id,
            'publish' => 2,
        ]);
    }

    /** Mot du an tho, du de kiem tra pham vi truy cap. */
    private function taoDuAn(string $ten, ?int $nguoiTao = null): Product
    {
        $duAn = Product::create([
            'product_catalogue_id' => DB::table('product_catalogues')->value('id'),
            'user_id' => $nguoiTao,
            'publish' => 2,
            'status' => 'building',
        ]);

        $this->duAnTao[] = $duAn->id;

        DB::table('product_language')->insert([
            'product_id' => $duAn->id,
            'language_id' => DB::table('languages')->where('canonical', 'vn')->value('id'),
            'name' => $ten,
            'canonical' => 'du-an-thu-nghiem-' . $duAn->id,
        ]);

        return $duAn;
    }

    // --- Cua vao ------------------------------------------------------------

    public function test_chua_dang_nhap_thi_bi_day_ve_trang_dang_nhap(): void
    {
        $this->get('/sale')->assertRedirect(route('sale.auth'));
        $this->get('/sale/du-an')->assertRedirect(route('sale.auth'));
    }

    public function test_dang_nhap_duoc_va_vao_duoc_bang_dieu_khien(): void
    {
        $this->post('/sale/dang-nhap', [
            'email' => $this->sale->email,
            'password' => 'matkhau@123',
        ])->assertRedirect(route('sale.dashboard'));

        $this->actingAs($this->sale)->get('/sale')->assertOk();
    }

    public static function manHinhSale(): array
    {
        return [
            'tong quan' => ['/sale'],
            'danh sach du an' => ['/sale/du-an'],
            'them du an' => ['/sale/du-an/them-moi'],
            'danh sach bai viet' => ['/sale/bai-viet'],
            'viet bai moi' => ['/sale/bai-viet/them-moi'],
            'ho so ca nhan' => ['/sale/ho-so'],
        ];
    }

    /** @dataProvider manHinhSale */
    public function test_moi_man_hinh_deu_mo_duoc(string $uri): void
    {
        $this->actingAs($this->sale)->get($uri)->assertOk();
    }

    public function test_tai_khoan_quan_tri_khong_vao_duoc_bang_dieu_khien_sale(): void
    {
        $quanTri = User::whereHas('user_catalogues', function ($q) {
            $q->where('is_sale', 0);
        })->where('publish', 2)->first();

        if (!$quanTri) {
            $this->markTestSkipped('Không có tài khoản quản trị nào đang hoạt động.');
        }

        $this->actingAs($quanTri)->get('/sale')->assertRedirect(route('dashboard.index'));
    }

    public function test_tai_khoan_bi_khoa_thi_khong_vao_duoc(): void
    {
        $this->sale->update(['publish' => 1]);

        $this->actingAs($this->sale)->get('/sale')->assertRedirect(route('sale.auth'));
    }

    // --- Pham vi du an ------------------------------------------------------

    public function test_chi_thay_du_an_duoc_giao_va_du_an_tu_tao(): void
    {
        $duocGiao = $this->taoDuAn('Dự án được giao cho tôi');
        $duocGiao->nhanVienKinhDoanh()->attach($this->sale->id, ['order' => 0]);

        $tuTao = $this->taoDuAn('Dự án tôi tự tạo', $this->sale->id);
        $cuaNguoiKhac = $this->taoDuAn('Dự án của người khác', $this->saleKhac->id);

        $html = $this->actingAs($this->sale)->get('/sale/du-an')->assertOk()->getContent();

        $this->assertStringContainsString('Dự án được giao cho tôi', $html);
        $this->assertStringContainsString('Dự án tôi tự tạo', $html);
        $this->assertStringNotContainsString('Dự án của người khác', $html,
            'Danh sách lọt dự án không thuộc phạm vi nhân viên');
    }

    public function test_khong_mo_duoc_du_an_cua_nguoi_khac_bang_duong_dan(): void
    {
        $cuaNguoiKhac = $this->taoDuAn('Dự án của người khác', $this->saleKhac->id);

        // Diem mau chot: man hinh danh sach da loc dung, nhung neu ham edit
        // khong kiem tra lai thi doi so tren thanh dia chi la vao duoc.
        $this->actingAs($this->sale)
            ->get('/sale/du-an/' . $cuaNguoiKhac->id . '/sua')
            ->assertNotFound();
    }

    public function test_khong_luu_duoc_du_an_cua_nguoi_khac(): void
    {
        $cuaNguoiKhac = $this->taoDuAn('Dự án của người khác', $this->saleKhac->id);

        $this->actingAs($this->sale)
            ->post('/sale/du-an/' . $cuaNguoiKhac->id . '/sua', [
                'name' => 'Tên bị đổi trộm',
                'canonical' => 'ten-bi-doi-trom',
                'product_catalogue_id' => $cuaNguoiKhac->product_catalogue_id,
            ])
            ->assertNotFound();

        $this->assertSame(
            'Dự án của người khác',
            DB::table('product_language')->where('product_id', $cuaNguoiKhac->id)->value('name'),
            'Dự án của người khác đã bị sửa'
        );
    }

    public function test_sua_duoc_du_an_duoc_giao(): void
    {
        $duAn = $this->taoDuAn('Tên dự án ban đầu');
        $duAn->nhanVienKinhDoanh()->attach($this->sale->id, ['order' => 0]);

        $this->actingAs($this->sale)->get('/sale/du-an/' . $duAn->id . '/sua')->assertOk();

        $this->actingAs($this->sale)
            ->post('/sale/du-an/' . $duAn->id . '/sua', [
                'name' => 'Tên dự án đã sửa',
                'canonical' => 'du-an-thu-nghiem-' . $duAn->id,
                'product_catalogue_id' => $duAn->product_catalogue_id,
                'status' => 'receiving',
                'publish' => 2,
            ])
            ->assertRedirect(route('sale.project.index'));

        $this->assertSame('Tên dự án đã sửa',
            DB::table('product_language')->where('product_id', $duAn->id)->value('name'));
        $this->assertSame('receiving',
            DB::table('products')->where('id', $duAn->id)->value('status'));
    }

    public function test_luu_du_an_khong_lam_mat_co_noi_bat(): void
    {
        // Service doc $request->boolean('is_featured') o moi lan luu. Form ben
        // /sale gui lai gia tri hien tai bang o an - thieu no thi moi lan nhan
        // vien bam luu la du an lang le rot khoi trang chu.
        $duAn = $this->taoDuAn('Dự án nổi bật');
        $duAn->update(['is_featured' => 1]);
        $duAn->nhanVienKinhDoanh()->attach($this->sale->id, ['order' => 0]);

        $this->actingAs($this->sale)->post('/sale/du-an/' . $duAn->id . '/sua', [
            'name' => 'Dự án nổi bật',
            'canonical' => 'du-an-thu-nghiem-' . $duAn->id,
            'product_catalogue_id' => $duAn->product_catalogue_id,
            'is_featured' => 1,
            'publish' => 2,
        ]);

        $this->assertSame(1, (int) DB::table('products')->where('id', $duAn->id)->value('is_featured'));
    }

    public function test_du_an_tu_tao_thuoc_ve_nguoi_tao(): void
    {
        $canonical = 'du-an-sale-tu-tao-' . time();

        $this->actingAs($this->sale)->post('/sale/du-an/them-moi', [
            'name' => 'Dự án do nhân viên tự tạo',
            'canonical' => $canonical,
            'product_catalogue_id' => DB::table('product_catalogues')->value('id'),
            'status' => 'upcoming',
            'publish' => 2,
        ])->assertRedirect(route('sale.project.index'));

        $id = DB::table('product_language')->where('canonical', $canonical)->value('product_id');
        $this->assertNotNull($id, 'Không tạo được dự án');
        $this->duAnTao[] = $id;

        $this->assertSame($this->sale->id,
            (int) DB::table('products')->where('id', $id)->value('user_id'));

        // Va no phai hien ra trong danh sach cua chinh nguoi do.
        $this->actingAs($this->sale)->get('/sale/du-an')
            ->assertSee('Dự án do nhân viên tự tạo', false);
    }

    // --- Bai viet -----------------------------------------------------------

    public function test_bai_viet_gui_len_o_trang_thai_cho_duyet(): void
    {
        $canonical = 'bai-viet-sale-' . time();

        $this->actingAs($this->sale)->post('/sale/bai-viet/them-moi', [
            'name' => 'Bài viết thử nghiệm của nhân viên',
            'canonical' => $canonical,
            'post_catalogue_id' => DB::table('post_catalogues')->value('id'),
            // Gia lap nguoi dung co gang tu duyet bai cua minh bang cach gui
            // thang hai o nay len - may chu phai bo qua.
            'publish' => 2,
            'approval_status' => 'approved',
        ])->assertRedirect(route('sale.post.index'));

        $bai = Post::where('user_id', $this->sale->id)->latest('id')->first();

        $this->assertNotNull($bai, 'Không lưu được bài viết');
        $this->assertSame('pending', $bai->approval_status,
            'Nhân viên tự đặt được trạng thái đã duyệt');
        $this->assertSame(1, (int) $bai->publish,
            'Bài viết của nhân viên hiện ra website ngay khi chưa duyệt');
    }

    public function test_khong_sua_duoc_bai_viet_cua_nguoi_khac(): void
    {
        $baiNguoiKhac = Post::create([
            'post_catalogue_id' => DB::table('post_catalogues')->value('id'),
            'user_id' => $this->saleKhac->id,
            'publish' => 2,
            'approval_status' => 'approved',
        ]);

        $this->actingAs($this->sale)
            ->get('/sale/bai-viet/' . $baiNguoiKhac->id . '/sua')
            ->assertNotFound();
    }

    public function test_sua_bai_da_duyet_thi_quay_ve_cho_duyet(): void
    {
        $bai = Post::create([
            'post_catalogue_id' => DB::table('post_catalogues')->value('id'),
            'user_id' => $this->sale->id,
            'publish' => 2,
            'approval_status' => 'approved',
        ]);

        DB::table('post_language')->insert([
            'post_id' => $bai->id,
            'language_id' => DB::table('languages')->where('canonical', 'vn')->value('id'),
            'name' => 'Bài đã duyệt',
            'canonical' => 'bai-da-duyet-' . $bai->id,
        ]);

        $this->actingAs($this->sale)->post('/sale/bai-viet/' . $bai->id . '/sua', [
            'name' => 'Bài đã duyệt nhưng sửa lại',
            'canonical' => 'bai-da-duyet-' . $bai->id,
            'post_catalogue_id' => $bai->post_catalogue_id,
        ]);

        $bai->refresh();

        $this->assertSame('pending', $bai->approval_status);
        $this->assertSame(1, (int) $bai->publish, 'Bài sửa xong vẫn hiển thị khi chưa duyệt lại');

        DB::table('post_language')->where('post_id', $bai->id)->delete();
    }

    // --- Ho so --------------------------------------------------------------

    public function test_sua_ho_so_duoc_nhung_khong_tu_doi_duoc_nhom(): void
    {
        $nhomCu = $this->sale->user_catalogue_id;

        $this->actingAs($this->sale)->post('/sale/ho-so', [
            'name' => 'Tên đã đổi',
            'title' => 'Trưởng nhóm kinh doanh',
            'email' => $this->sale->email,
            'phone' => '0900000999',
            'zalo' => '0900000999',
            'public_email' => 'hienthi@example.com',
            // Hai o duoi day khong nam trong danh sach cho phep cua controller.
            'user_catalogue_id' => 1,
            'publish' => 1,
        ])->assertRedirect(route('sale.profile'));

        $this->sale->refresh();

        $this->assertSame('Tên đã đổi', $this->sale->name);
        $this->assertSame('Trưởng nhóm kinh doanh', $this->sale->title);
        $this->assertSame($nhomCu, $this->sale->user_catalogue_id, 'Nhân viên tự đổi được nhóm');
        $this->assertSame(2, (int) $this->sale->publish, 'Nhân viên tự đổi được trạng thái tài khoản');
    }

    // --- Hien thi ngoai website ---------------------------------------------

    public function test_nhan_vien_hien_ra_trang_chi_tiet_du_an(): void
    {
        $duAn = DB::table('product_language')->where('canonical', 'noxh-tuc-duyen')->first();

        if (!$duAn) {
            $this->markTestSkipped('Không tìm thấy dự án mẫu noxh-tuc-duyen.');
        }

        DB::table('product_user')->insert([
            'product_id' => $duAn->product_id,
            'user_id' => $this->sale->id,
            'order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $html = $this->get('/du-an/noxh-tuc-duyen')->assertOk()->getContent();

        $this->assertStringContainsString('Nhân viên kinh doanh phụ trách', $html);
        $this->assertStringContainsString($this->sale->name, $html);
        $this->assertStringContainsString('Chuyên viên tư vấn', $html);

        // Khoa tai khoan thi bien khoi trang, khong phai go tay tung du an.
        $this->sale->update(['publish' => 1]);

        $html = $this->get('/du-an/noxh-tuc-duyen')->assertOk()->getContent();
        $this->assertStringNotContainsString($this->sale->name, $html,
            'Nhân viên đã bị khóa vẫn hiện ngoài website');
    }

    public function test_du_an_khong_co_nhan_vien_thi_khong_in_khoi_nao(): void
    {
        // Khong chi dinh san mot du an: CSDL phat trien co the da duoc gan
        // nhan vien bang tay, khi do bai kiem tra hong ma khong phai vi ma
        // nguon sai. Tu tim mot du an dang thuc su khong co ai phu trach.
        $canonical = DB::table('product_language as pl')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->whereNull('p.deleted_at')
            ->where('p.publish', 2)
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)->from('product_user')
                  ->whereColumn('product_user.product_id', 'p.id');
            })
            ->value('pl.canonical');

        if (!$canonical) {
            $this->markTestSkipped('Dự án nào cũng đã có nhân viên phụ trách.');
        }

        $html = $this->get('/du-an/' . $canonical)->assertOk()->getContent();

        $this->assertStringNotContainsString('Nhân viên kinh doanh phụ trách', $html);
    }
}
