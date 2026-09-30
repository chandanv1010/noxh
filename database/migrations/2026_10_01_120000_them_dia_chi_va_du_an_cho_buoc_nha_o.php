<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nua duoi ban ve noxh_image/w-4.jpg: dia chi nha o, noi lam viec va danh
 * sach du an quan tam.
 *
 * Buoc "Nha o" khong chi hoi mot cau: sau khi chon loai nha, nguoi dung con
 * khai dia chi nha o hien tai, noi lam viec hien tai, roi tich cac du an
 * NOXH tren dia ban noi lam viec. Khoang cach tu noi lam viec toi du an la
 * mot tieu chi that trong quy dinh nen phai luu lai.
 *
 *   eligibility_questions.extras
 *       Buoc nay hoi them gi ngoai cau hoi chinh. Bo trong la khong hoi gi
 *       them; 'address' la khoi dia chi + danh sach du an. De trong ma nguon
 *       thi them mot buoc dang nay lai phai sua code.
 *
 *   eligibility_checks.*
 *       Bang luot kiem tra da co province_code; them ba o dia gioi con lai
 *       va danh sach id du an nguoi dung tich.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('eligibility_questions') && !Schema::hasColumn('eligibility_questions', 'extras')) {
            Schema::table('eligibility_questions', function (Blueprint $table) {
                $table->string('extras', 40)->nullable()->after('layout');
            });
        }

        if (Schema::hasTable('eligibility_checks')) {
            Schema::table('eligibility_checks', function (Blueprint $table) {
                if (!Schema::hasColumn('eligibility_checks', 'ward_code')) {
                    $table->string('ward_code', 20)->nullable()->after('province_code');
                }
                if (!Schema::hasColumn('eligibility_checks', 'work_province_code')) {
                    $table->string('work_province_code', 20)->nullable()->after('ward_code');
                }
                if (!Schema::hasColumn('eligibility_checks', 'work_ward_code')) {
                    $table->string('work_ward_code', 20)->nullable()->after('work_province_code');
                }
                if (!Schema::hasColumn('eligibility_checks', 'project_ids')) {
                    $table->string('project_ids', 255)->nullable()->after('work_ward_code');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eligibility_questions') && Schema::hasColumn('eligibility_questions', 'extras')) {
            Schema::table('eligibility_questions', function (Blueprint $table) {
                $table->dropColumn('extras');
            });
        }

        if (Schema::hasTable('eligibility_checks')) {
            Schema::table('eligibility_checks', function (Blueprint $table) {
                foreach (['ward_code', 'work_province_code', 'work_ward_code', 'project_ids'] as $cot) {
                    if (Schema::hasColumn('eligibility_checks', $cot)) {
                        $table->dropColumn($cot);
                    }
                }
            });
        }
    }
};
