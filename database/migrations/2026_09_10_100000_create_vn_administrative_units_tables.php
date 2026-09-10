<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Don vi hanh chinh Viet Nam - bo moi, HAI cap.
 *
 * Tu 01/07/2025 Viet Nam bo cap quan/huyen: chi con tinh/thanh -> xa/phuong.
 * Bo du lieu cu trong DB nay (63 tinh, 705 quan huyen, 10.598 phuong xa) da
 * lac hau va sai cau truc, khong dung duoc nua.
 *
 * Bo moi: 34 tinh/thanh, 3.321 xa/phuong/dac khu, theo Nghi quyet 30/2026/QH16.
 * Nguon: github.com/ThangLeQuoc/vietnamese-provinces-database (json/vn_only_simplified)
 * Ban sao dat tai database/data/vn_units.json de seed lai duoc khong can mang.
 *
 * Dat ten bang co tien to vn_ de khong dung cac bang provinces/districts/wards
 * cu - chung van con dung o vai cho trong code cu, chua go het.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vn_provinces', function (Blueprint $table) {
            // Ma do nha nuoc cap, khong phai auto increment.
            $table->string('code', 20)->primary();
            $table->string('name', 255);
            $table->string('postal_code_prefix', 10)->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('vn_wards', function (Blueprint $table) {
            $table->string('code', 20)->primary();
            $table->string('name', 255);
            $table->string('province_code', 20);
            $table->string('postal_code', 10)->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('province_code');
            $table->index('name');
            $table->foreign('province_code')->references('code')->on('vn_provinces')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vn_wards');
        Schema::dropIfExists('vn_provinces');
    }
};
