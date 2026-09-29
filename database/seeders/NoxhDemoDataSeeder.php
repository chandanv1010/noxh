<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserCatalogue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Du lieu mau cho trang chu, dung theo ban thiet ke noxh_image/home-fix.jpg.
 *
 * Ban thiet ke ve sau tu van vien va ba tin thuoc ba chuyen muc khac mau.
 * Ban cai thuc te moi co mot tai khoan kinh doanh va ca bon tin deu nam
 * trong mot chuyen muc, nen trang chu nhin trong hon han ban ve.
 *
 * Seeder nay chi THEM, khong xoa va khong ghi de len thu quan tri da sua:
 *   - tai khoan nao da co email trung thi bo qua
 *   - bai viet nao da co duong dan trung thi bo qua
 *   - chuyen muc cua bai chi gan khi bai do CHUA thuoc chuyen muc nao khac
 *
 * Chay:  php artisan db:seed --class=NoxhDemoDataSeeder --force
 *
 * LUU Y: day la du lieu DEMO. Tai khoan tu van vien dung chung mot mat khau
 * mau; truoc khi dua len may that phai doi mat khau hoac xoa han di.
 */
class NoxhDemoDataSeeder extends Seeder
{
    /** Mat khau mau cua cac tai khoan demo - doi truoc khi chay that. */
    private const MAT_KHAU_DEMO = 'noxh@demo2026';

    public function run(): void
    {
        $this->napTuVanVien();
        $this->napChuyenMucChoTin();
        $this->napTinTheoThietKe();

        $this->command?->warn('Tai khoan tu van vien la DEMO, dung chung mat khau mau - hay doi hoac xoa truoc khi chay that.');
        $this->command?->info('Da nap xong du lieu mau cho trang chu.');
    }

    // ------------------------------------------------------------ tu van vien
    private function napTuVanVien(): void
    {
        $nhom = UserCatalogue::where('is_sale', 1)->first();

        if (!$nhom) {
            $this->command?->error('Chua co nhom "Nhan vien kinh doanh". Chay NoxhSaleSeeder truoc.');
            return;
        }

        // Ho ten va khu vuc lay dung theo ban thiet ke.
        $nguoi = [
            ['Nguyễn Văn Hùng', 'hung.nv', '0912 001 001', 'Thái Nguyên'],
            ['Trần Thị Mai', 'mai.tt', '0912 001 002', 'Thái Nguyên'],
            ['Lê Thị Thu', 'thu.lt', '0912 001 003', 'Thái Nguyên'],
            ['Phạm Minh Đức', 'duc.pm', '0912 001 004', 'Thái Nguyên'],
            ['Hoàng Thị Lan', 'lan.ht', '0912 001 005', 'Bắc Ninh'],
            ['Vũ Quang Huy', 'huy.vq', '0912 001 006', 'Hà Nội'],
        ];

        $them = 0;

        foreach ($nguoi as $i => [$ten, $tenHop, $dienThoai, $khuVuc]) {
            $email = $tenHop . '@noxh.vn';

            // withTrashed: tai khoan da xoa mem van giu email, tao lai se
            // dung vao rang buoc duy nhat cua cot email.
            if (User::withTrashed()->where('email', $email)->exists()) {
                continue;
            }

            User::create([
                'name' => $ten,
                'title' => 'Tư vấn hồ sơ NOXH',
                'email' => $email,
                'password' => Hash::make(self::MAT_KHAU_DEMO),
                'phone' => $dienThoai,
                'zalo' => preg_replace('/\D/', '', $dienThoai),
                'public_email' => $email,
                'address' => $khuVuc,
                'description' => 'Hỗ trợ hồ sơ mua nhà ở xã hội khu vực ' . $khuVuc . '.',
                'user_catalogue_id' => $nhom->id,
                'publish' => 2,
            ]);

            $them++;
        }

        $this->command?->info($them > 0
            ? "Da them {$them} tu van vien mau."
            : 'Tu van vien mau da co du, khong them gi.');
    }

    // -------------------------------------------------- chuyen muc cho bai cu
    /**
     * Ban thiet ke cho thay ba tin moi nhat thuoc ba chuyen muc khac nhau,
     * moi chuyen muc mot mau nhan. Bon bai mau hien deu nam trong "Chinh
     * sach" nen ba nhan ngoai trang chu cung mau, khong thay duoc y do.
     *
     * Gan lai theo dung noi dung tung bai. Chi dong nao bai CHUA thuoc
     * chuyen muc nao moi gan - khong keo bai ra khoi chuyen muc quan tri da
     * tu xep.
     */
    private function napChuyenMucChoTin(): void
    {
        $theo = [
            'huong-dan-thu-tuc-ho-so-mua-noxh-2026' => 'huong-dan-ho-so',
            'lai-suat-vay-mua-noxh-thang-5-2026' => 'kinh-nghiem',
        ];

        $doi = 0;

        foreach ($theo as $duongDanBai => $duongDanMuc) {
            $baiId = DB::table('post_language')->where('canonical', $duongDanBai)
                ->where('language_id', 1)->value('post_id');

            $mucId = DB::table('post_catalogue_language')->where('canonical', $duongDanMuc)
                ->where('language_id', 1)->value('post_catalogue_id');

            if (!$baiId || !$mucId) {
                continue;
            }

            DB::table('post_catalogue_post')->where('post_id', $baiId)->delete();
            DB::table('post_catalogue_post')->insert([
                'post_id' => $baiId,
                'post_catalogue_id' => $mucId,
            ]);

            // Cot post_catalogue_id tren bang posts la chuyen muc chinh, dung
            // cho duong dan va SEO - phai doi theo, neu khong hai cho lech nhau.
            DB::table('posts')->where('id', $baiId)->update(['post_catalogue_id' => $mucId]);

            $doi++;
        }

        $this->command?->info("Da xep lai chuyen muc cho {$doi} bai viet.");
    }

    // ------------------------------------------------------------- tin bo sung
    /**
     * Them mot tin thuoc chuyen muc "Tin dia phuong" de khoi tin tuc ngoai
     * trang chu co du ba mau nhan nhu ban thiet ke.
     */
    private function napTinTheoThietKe(): void
    {
        $bai = [
            'ten' => 'Thái Nguyên sắp mở bán hơn 1.000 căn NOXH tại Túc Duyên',
            'duongDan' => 'thai-nguyen-sap-mo-ban-hon-1000-can-noxh-tuc-duyen',
            'muc' => 'tin-dia-phuong',
            'moTa' => 'Dự án NOXH Túc Duyên dự kiến nhận hồ sơ từ quý IV/2026 với hơn 1.000 căn hộ.',
        ];

        if (DB::table('post_language')->where('canonical', $bai['duongDan'])->exists()) {
            $this->command?->info('Tin mau da co, khong them lai.');
            return;
        }

        $mucId = DB::table('post_catalogue_language')->where('canonical', $bai['muc'])
            ->where('language_id', 1)->value('post_catalogue_id');

        if (!$mucId) {
            $this->command?->warn('Khong thay chuyen muc "' . $bai['muc'] . '", bo qua tin mau.');
            return;
        }

        $id = DB::table('posts')->insertGetId([
            'post_catalogue_id' => $mucId,
            'publish' => 2,
            'follow' => 2,
            'order' => 0,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('post_language')->insert([
            'post_id' => $id,
            'language_id' => 1,
            'name' => $bai['ten'],
            'canonical' => $bai['duongDan'],
            'description' => $bai['moTa'],
            'content' => '<p>' . e($bai['moTa']) . '</p>',
            'meta_title' => $bai['ten'],
            'meta_description' => $bai['moTa'],
        ]);

        DB::table('post_catalogue_post')->insert([
            'post_id' => $id,
            'post_catalogue_id' => $mucId,
        ]);

        // Bang `routers` quyet dinh duong dan ngoai trang. Thieu dong nay thi
        // bam vao tin se ra 404.
        if (DB::getSchemaBuilder()->hasTable('routers')
            && !DB::table('routers')->where('canonical', $bai['duongDan'])->exists()) {
            DB::table('routers')->insert([
                'canonical' => $bai['duongDan'],
                'module_id' => $id,
                'controllers' => 'App\Http\Controllers\Frontend\PostController',
                'language_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command?->info('Da them tin mau: ' . $bai['ten']);
    }
}
