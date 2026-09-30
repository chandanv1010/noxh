<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bo kiem tra dieu kien chuyen sang dang WIZARD nhieu buoc
 * (ban ve noxh_image/w-1.jpg).
 *
 * Ban ve doi hai thu ma bang cu khong chua duoc:
 *
 *  - Moi dap an la mot O VUONG co hinh tron pastel ben trai. Hinh va mau cua
 *    hinh la cua RIENG tung dap an ("Nguoi co cong" hinh huy chuong nen hong,
 *    "Cong nhan" hinh nha may nen xanh...) nen phai nam cung dong voi dap an,
 *    khong the suy ra tu cau hoi.
 *
 *  - Thanh buoc o dau trang in ten NGAN cua tung cau ("Doi tuong", "Nha o",
 *    "Thu nhap"...) chu khong in ca cau hoi. Dong chu xam duoi luoi dap an
 *    cung la cua rieng tung cau.
 *
 * De trong ma nguon thi quan tri them mot dap an moi la lai phai sua code, nen
 * tat ca deu la cot trong CSDL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('eligibility_options')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                if (!Schema::hasColumn('eligibility_options', 'icon')) {
                    $table->string('icon', 60)->nullable()->after('label');
                }
                if (!Schema::hasColumn('eligibility_options', 'icon_tone')) {
                    $table->string('icon_tone', 20)->nullable()->after('icon');
                }
            });
        }

        if (Schema::hasTable('eligibility_questions')) {
            Schema::table('eligibility_questions', function (Blueprint $table) {
                if (!Schema::hasColumn('eligibility_questions', 'step_label')) {
                    $table->string('step_label', 60)->nullable()->after('question');
                }
                if (!Schema::hasColumn('eligibility_questions', 'foot_note')) {
                    $table->string('foot_note', 500)->nullable()->after('hint');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eligibility_options')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                foreach (['icon', 'icon_tone'] as $cot) {
                    if (Schema::hasColumn('eligibility_options', $cot)) {
                        $table->dropColumn($cot);
                    }
                }
            });
        }

        if (Schema::hasTable('eligibility_questions')) {
            Schema::table('eligibility_questions', function (Blueprint $table) {
                foreach (['step_label', 'foot_note'] as $cot) {
                    if (Schema::hasColumn('eligibility_questions', $cot)) {
                        $table->dropColumn($cot);
                    }
                }
            });
        }
    }
};
