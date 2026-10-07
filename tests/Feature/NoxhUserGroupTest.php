<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCatalogue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Quan ly nhom thanh vien: co "La nhom nhan vien kinh doanh" va bo loc theo nhom.
 *
 * HAI LOI DA GAP THAT, ca hai deu lam nguoi quan tri khong hieu du lieu tu dau:
 *
 *  1. Co `is_sale` quyet dinh kha nhieu thu (thanh vien dang nhap o /sale, va co
 *     xuat hien o chon "nhan vien phu trach" trong form du an hay khong) nhung
 *     danh sach nhom KHONG hien no ra - phai mo tung nhom moi biet nhom nao dang
 *     bat. Nay co han mot cot bat/tat ngay trong danh sach.
 *
 *  2. O loc "Chon Nhom Thanh Vien" o danh sach thanh vien chi co hai lua chon
 *     VIET CUNG ("Quan tri vien", id 1) va khong he duoc dung trong truy van -
 *     chon kieu gi cung ra ca danh sach. Nay do ra tu CSDL va loc that.
 */
class NoxhUserGroupTest extends TestCase
{
    private ?UserCatalogue $nhomThu = null;
    private array $emailTam = ['nhom.thu.a@example.com', 'nhom.thu.b@example.com', 'nhom.thu.c@example.com'];

    protected function tearDown(): void
    {
        DB::table('users')->whereIn('email', $this->emailTam)->delete();

        if ($this->nhomThu) {
            DB::table('user_catalogues')->where('id', $this->nhomThu->id)->delete();
            $this->nhomThu = null;
        }

        parent::tearDown();
    }

    private function quanTri(): ?User
    {
        return User::whereHas('user_catalogues.permissions')->where('publish', 2)->first();
    }

    /** Mot nhom tam va hai thanh vien: mot nguoi thuoc nhom tam, mot nguoi khong. */
    private function dungDuLieu(): void
    {
        $this->nhomThu = UserCatalogue::create([
            'name' => 'Nhóm thử nghiệm bộ lọc',
            'description' => 'Nhóm tạm cho bài kiểm tra tự động',
            'is_sale' => 0,
            'publish' => 2,
        ]);

        DB::table('users')->whereIn('email', $this->emailTam)->delete();

        User::create([
            'name' => 'Thành viên nhóm thử A',
            'email' => $this->emailTam[0],
            'password' => Hash::make('matkhau@123'),
            'user_catalogue_id' => $this->nhomThu->id,
            'publish' => 2,
        ]);

        User::create([
            'name' => 'Thành viên nhóm thử B',
            'email' => $this->emailTam[1],
            'password' => Hash::make('matkhau@123'),
            'user_catalogue_id' => 1,
            'publish' => 2,
        ]);

        // Nguoi thu ba cung thuoc nhom tam, de nhom nay du 2 nguoi ma sinh ra
        // phan trang - can cho bai kiem tra "bo loc con giu khi sang trang 2".
        User::create([
            'name' => 'Thành viên nhóm thử C',
            'email' => $this->emailTam[2],
            'password' => Hash::make('matkhau@123'),
            'user_catalogue_id' => $this->nhomThu->id,
            'publish' => 2,
        ]);
    }

    public function test_danh_sach_nhom_co_cot_bat_co_nhan_vien_kinh_doanh(): void
    {
        $qt = $this->quanTri();

        if (!$qt) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        $this->dungDuLieu();

        $html = $this->actingAs($qt)->get('/user/catalogue/index')->assertOk()->getContent();

        $this->assertStringContainsString('Nhân viên kinh doanh', $html);
        $this->assertStringContainsString('doi-co-sale', $html);

        // O phai hien DUNG trang thai trong CSDL. Lan dau lam cot nay, danh sach
        // hien moi o deu trong du nhom dang bat co - vi truy van liet ke thieu
        // cot `is_sale` nen thuoc tinh doc ra null. Loi im lang, khong bao gi.
        $nhomSale = UserCatalogue::where('is_sale', 1)->orderBy('id')->first();

        if ($nhomSale) {
            $this->assertMatchesRegularExpression(
                '/doi-co-sale[^>]*data-id="' . $nhomSale->id . '"[^>]*checked/',
                $html,
                'Nhóm đang bật cờ nhưng ô trong danh sách lại không được tích'
            );
        }

        $this->assertDoesNotMatchRegularExpression(
            '/doi-co-sale[^>]*data-id="' . $this->nhomThu->id . '"[^>]*checked/',
            $html,
            'Nhóm chưa bật cờ mà ô trong danh sách lại được tích'
        );
    }

    public function test_bat_co_nhan_vien_kinh_doanh_ngay_trong_danh_sach(): void
    {
        $qt = $this->quanTri();

        if (!$qt) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        $this->dungDuLieu();

        $this->actingAs($qt)
            ->postJson(route('user.catalogue.is-sale', ['id' => $this->nhomThu->id]), ['is_sale' => 1])
            ->assertOk()
            ->assertJson(['ok' => true, 'is_sale' => 1]);

        $this->assertSame(1, (int) DB::table('user_catalogues')->where('id', $this->nhomThu->id)->value('is_sale'));

        // Tat lai duoc, va tra ve dung so thanh vien cua nhom.
        $this->actingAs($qt)
            ->postJson(route('user.catalogue.is-sale', ['id' => $this->nhomThu->id]), ['is_sale' => 0])
            ->assertOk()
            ->assertJson(['ok' => true, 'is_sale' => 0, 'so_thanh_vien' => 2]);

        $this->assertSame(0, (int) DB::table('user_catalogues')->where('id', $this->nhomThu->id)->value('is_sale'));
    }

    public function test_bat_co_thi_thanh_vien_hien_ra_o_chon_nhan_vien_phu_trach(): void
    {
        $qt = $this->quanTri();

        if (!$qt) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        $this->dungDuLieu();

        // Chua bat co: nguoi cua nhom tam khong duoc coi la nhan vien kinh doanh.
        $html = $this->actingAs($qt)->get('/product/create')->assertOk()->getContent();
        $this->assertStringNotContainsString('Thành viên nhóm thử A', $html);

        $this->actingAs($qt)->postJson(route('user.catalogue.is-sale', ['id' => $this->nhomThu->id]), ['is_sale' => 1]);

        $html = $this->actingAs($qt)->get('/product/create')->assertOk()->getContent();
        $this->assertStringContainsString('Thành viên nhóm thử A', $html);
    }

    public function test_o_loc_nhom_thanh_vien_do_tu_csdl_chu_khong_viet_cung(): void
    {
        $qt = $this->quanTri();

        if (!$qt) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        $this->dungDuLieu();

        $html = $this->actingAs($qt)->get('/user/index')->assertOk()->getContent();

        $this->assertStringContainsString('Tất cả nhóm thành viên', $html);
        // Truoc day o nay chi co dung mot lua chon viet cung la "Quan tri vien".
        $this->assertStringContainsString('Nhóm thử nghiệm bộ lọc', $html);
    }

    public function test_loc_thanh_vien_theo_nhom_that_su_hoat_dong(): void
    {
        $qt = $this->quanTri();

        if (!$qt) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        $this->dungDuLieu();

        $html = $this->actingAs($qt)
            ->get('/user/index?user_catalogue_id=' . $this->nhomThu->id)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('nhom.thu.a@example.com', $html, 'Không thấy thành viên của nhóm được lọc');
        $this->assertStringNotContainsString('nhom.thu.b@example.com', $html, 'Bộ lọc nhóm không có tác dụng');
    }

    public function test_loc_thanh_vien_theo_nhom_van_ton_trong_cac_trang_sau(): void
    {
        $qt = $this->quanTri();

        if (!$qt) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        $this->dungDuLieu();

        // Phan trang phai giu bo loc, khong thi bam sang trang 2 la mat.
        // perpage=1 de nhom 2 nguoi sinh ra 2 trang - co trang moi co link.
        $html = $this->actingAs($qt)
            ->get('/user/index?user_catalogue_id=' . $this->nhomThu->id . '&perpage=1')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('user_catalogue_id=' . $this->nhomThu->id, $html);
    }
}
