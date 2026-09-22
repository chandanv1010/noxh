<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Mo thu MOI man hinh trong khu quan tri.
 *
 * Muc dich la bat loi 500 sau khi go cac module ke thua tu truc: mot view con
 * sot lai goi route('order.index') hay mot controller con tiem
 * LecturerRepository se lo ra o day chu khong doi toi luc nguoi dung bam vao.
 *
 * Chi mo cac route GET khong co tham so - cac man hinh sua/xoa can id da co
 * bai kiem tra rieng.
 */
class AdminSmokeTest extends TestCase
{
    private ?User $quanTri = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quanTri = User::whereHas('user_catalogues.permissions')
            ->where('publish', 2)
            ->first();
    }

    public static function duongDanQuanTri(): array
    {
        // Doc thang tu bang dinh tuyen: them module moi la tu dong duoc kiem.
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $ds = [];

        foreach (Route::getRoutes() as $r) {
            if (!in_array('GET', $r->methods(), true)) {
                continue;
            }

            if (!in_array('admin', $r->gatherMiddleware(), true)) {
                continue;
            }

            $uri = $r->uri();

            if (str_contains($uri, '{')) {
                continue;
            }

            // Bo qua cac diem cuoi ajax: chung can tham so truy van moi chay
            // duoc, goi tay khong thi bao loi la dung - khong phai hong.
            if (str_starts_with($uri, 'ajax/')) {
                continue;
            }

            $ds[$uri] = ['/' . $uri];
        }

        ksort($ds);

        return $ds;
    }

    /** @dataProvider duongDanQuanTri */
    public function test_man_hinh_quan_tri_khong_loi(string $uri): void
    {
        if (!$this->quanTri) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        $response = $this->actingAs($this->quanTri)->get($uri);

        // 403 la hop le (tai khoan thieu quyen cho module do), 500 thi khong.
        $this->assertContains(
            $response->getStatusCode(),
            [200, 302, 403],
            $uri . ' trả về ' . $response->getStatusCode()
        );
    }
}
