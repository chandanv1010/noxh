<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Them cac cot ma API don vi hanh chinh tra ve.
 *
 *   division_type  "thanh pho trung uong" / "tinh" / "phuong" / "xa" / "dac khu"
 *   codename       ten khong dau, dung lam duong dan
 *   phone_code     ma vung dien thoai (chi tinh/thanh)
 *
 * Nguon: https://provinces.open-api.vn/api/v2/
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vn_provinces')) {
            Schema::table('vn_provinces', function (Blueprint $table) {
                if (!Schema::hasColumn('vn_provinces', 'division_type')) {
                    $table->string('division_type', 50)->nullable()->after('name');
                }
                if (!Schema::hasColumn('vn_provinces', 'codename')) {
                    $table->string('codename', 120)->nullable()->after('division_type');
                }
                if (!Schema::hasColumn('vn_provinces', 'phone_code')) {
                    $table->string('phone_code', 10)->nullable()->after('codename');
                }
            });
        }

        if (Schema::hasTable('vn_wards')) {
            Schema::table('vn_wards', function (Blueprint $table) {
                if (!Schema::hasColumn('vn_wards', 'division_type')) {
                    $table->string('division_type', 50)->nullable()->after('name');
                }
                if (!Schema::hasColumn('vn_wards', 'codename')) {
                    $table->string('codename', 120)->nullable()->after('division_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vn_provinces')) {
            Schema::table('vn_provinces', function (Blueprint $table) {
                $table->dropColumn(array_values(array_filter(
                    ['division_type', 'codename', 'phone_code'],
                    fn ($c) => Schema::hasColumn('vn_provinces', $c)
                )));
            });
        }

        if (Schema::hasTable('vn_wards')) {
            Schema::table('vn_wards', function (Blueprint $table) {
                $table->dropColumn(array_values(array_filter(
                    ['division_type', 'codename'],
                    fn ($c) => Schema::hasColumn('vn_wards', $c)
                )));
            });
        }
    }
};
