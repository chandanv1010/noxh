<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserCatalogue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Nen cho tinh nang nhan vien kinh doanh.
 *
 * Chay duoc nhieu lan ma khong sinh ban trung: tim theo ten nhom va theo email.
 *
 * Nhom nay KHONG can quyen nao trong bang permissions. Bang dieu khien /sale
 * khong hoi Gate 'modules' o cho nao - no chan bang middleware 'sale' va bang
 * pham vi du an cua tung nguoi. Cap them quyen cho nhom chi mo them cua vao
 * trang quan tri ma khong duoc gi.
 */
class NoxhSaleSeeder extends Seeder
{
    public function run(): void
    {
        $nhom = UserCatalogue::withTrashed()->where('name', 'Nhân viên kinh doanh')->first();

        if (!$nhom) {
            $nhom = UserCatalogue::create([
                'name' => 'Nhân viên kinh doanh',
                'description' => 'Đăng nhập ở /sale, chỉ thấy dự án được giao và bài viết của chính mình',
                'is_sale' => 1,
                'publish' => 2,
            ]);
            $this->command->info('Đã tạo nhóm "Nhân viên kinh doanh" (id ' . $nhom->id . ')');
        } else {
            $nhom->restore();
            $nhom->update(['is_sale' => 1, 'publish' => 2]);
            $this->command->info('Nhóm "Nhân viên kinh doanh" đã có, đã bật lại cờ is_sale');
        }

        // Mot tai khoan de thu. Mat khau dat san o day la mat khau DEMO, doi
        // ngay truoc khi dua len may that.
        $email = 'sale@noxh.vn';
        $daCo = User::withTrashed()->where('email', $email)->first();

        if ($daCo) {
            $this->command->warn('Tài khoản ' . $email . ' đã tồn tại, không tạo lại');
            return;
        }

        User::create([
            'name' => 'Nguyễn Văn Sale',
            'title' => 'Chuyên viên tư vấn',
            'email' => $email,
            'password' => Hash::make('sale@123456'),
            'phone' => '0912345678',
            'zalo' => '0912345678',
            'public_email' => $email,
            'description' => 'Hỗ trợ khách hàng khu vực Hà Nội và các tỉnh lân cận.',
            'user_catalogue_id' => $nhom->id,
            'publish' => 2,
        ]);

        $this->command->info('Đã tạo tài khoản thử: ' . $email . ' / sale@123456 (HÃY ĐỔI MẬT KHẨU)');
    }
}
