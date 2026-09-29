<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Toa do tam cua tinh/thanh pho.
 *
 * API provinces.open-api.vn khong tra ve toa do, ma khoi "Ban do du an" o
 * trang danh sach can cham ghim dung cho. Hai cot nay do
 * NoxhProvinceCoordSeeder nap, va lenh noxh:dia-gioi KHONG ghi de len chung
 * (xem DongBoDiaGioi) de dong bo lai danh sach tinh khong lam mat toa do.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vn_provinces', function (Blueprint $bang) {
            $bang->decimal('lat', 9, 6)->nullable()->after('postal_code_prefix');
            $bang->decimal('lng', 9, 6)->nullable()->after('lat');
        });
    }

    public function down(): void
    {
        Schema::table('vn_provinces', function (Blueprint $bang) {
            $bang->dropColumn(['lat', 'lng']);
        });
    }
};
