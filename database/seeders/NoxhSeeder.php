<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * MOT lenh duy nhat de nap toan bo du lieu nen cua NOXH.vn.
 *
 *     php artisan db:seed --class=NoxhSeeder --force
 *
 * Truoc day moi lan trien khai phai nho goi tung seeder mot theo dung thu
 * tu; quen mot cai la trang thieu chu, hoac goi sai thu tu la seeder sau
 * khong tim thay thu seeder truoc tao ra. Danh sach duoi day la thu tu dung,
 * va MOI seeder deu chay lai duoc nhieu lan ma khong sinh ban trung - chay
 * lenh nay sau moi lan trien khai la an toan.
 *
 * Them seeder moi thi them MOT dong vao day, dung vi tri trong chuoi phu
 * thuoc; khong them thi may chu se khong bao gio chay no.
 */
class NoxhSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // --- 1. Nen he thong -------------------------------------------
            // KHONG co seeder tao tai khoan quan tri: tai khoan dau tien di
            // theo ban CSDL khoi tao. (UserSeeder cu cua ma nguon goc sinh
            // 100.000 nguoi dung gia nen da bi xoa han.)
            //
            // Quyen cua tung nhom nguoi dung.
            DistributionSeeder::class,
            // Khung cac o noi dung quan tri sua duoc.
            IntroduceSeeder::class,

            // --- 2. Don vi hanh chinh --------------------------------------
            // 34 tinh/thanh + 3.321 phuong/xa theo co cau moi tu 01/07/2025.
            // Phai co TRUOC du an va truoc bo kiem tra dieu kien, vi ca hai
            // deu chon tinh/phuong tu hai bang nay.
            VnAdministrativeUnitSeeder::class,
            // Toa do tam tinh - khoi ban do cham ghim theo hai so nay.
            NoxhProvinceCoordSeeder::class,

            // --- 3. Khung du lieu NOXH -------------------------------------
            NoxhStarterDataSeeder::class,
            // Menu dau trang va chan trang. NoxhHomeDesignSeeder chia lai
            // nhom footer-menu nen phai chay sau cai nay.
            NoxhMenuSeeder::class,
            // Quyen cho cac module quan tri rieng cua NOXH - thieu la mo
            // menu ra bao 403.
            NoxhAdminPermissionSeeder::class,
            // Nhom "Nhan vien kinh doanh" - NoxhDemoDataSeeder can nhom nay
            // moi tao duoc tu van vien.
            NoxhSaleSeeder::class,

            // --- 4. Noi dung tung trang ------------------------------------
            NoxhFrontendContentSeeder::class,
            // Noi lai danh muc cho du an da co. RemoveLegacyCatalogDataSeeder
            // xoa sach product_catalogue_product, ma seeder noi dung o tren lai
            // bo qua du an da ton tai - thieu buoc nay thi trang quan tri
            // /product/index hien trong.
            NoxhProjectCatalogueSeeder::class,
            NoxhHomeDesignSeeder::class,
            NoxhProjectPageSeeder::class,
            NoxhProjectDetailSeeder::class,
            NoxhNewsPageSeeder::class,
            NoxhLegalPageSeeder::class,
            NoxhProjectMapSeeder::class,

            // --- 5. Bo kiem tra dieu kien ----------------------------------
            NoxhEligibilityWizardSeeder::class,

            // --- 6. Du lieu mau --------------------------------------------
            // Du an mau, tu van vien mau (kem anh cat tu ban ve), tin mau.
            // Day la du lieu DE XEM THU - xoa duoc sau khi co du lieu that.
            NoxhDemoDataSeeder::class,
            NoxhProjectDetailDemoSeeder::class,
        ]);

        $this->command?->newLine();
        $this->command?->info('Da nap xong du lieu nen cua NOXH.vn.');
        $this->command?->warn(
            'Anh cat tu ban thiet ke KHONG nam trong CSDL. May nao chua co '
            . 'thu muc public/uploads/noxh thi chay `python tools/tach-anh-ban-ve.py`.'
        );
        $this->command?->warn(
            'Toa do that cua du an lay bang `php artisan noxh:toa-do` (them --xa '
            . 'de nap luon toa do phuong/xa).'
        );
    }
}
