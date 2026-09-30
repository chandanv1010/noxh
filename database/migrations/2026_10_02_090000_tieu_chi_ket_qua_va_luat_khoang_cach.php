<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cham diem theo TIEU CHI va luat khoang cach (file noxh_image/cong-thuc.jpg).
 *
 * Ba ban ve ket qua (thanh-cong / luu y / that bai) deu in mot bang SAU TIEU
 * CHI chu khong phai in lai tung cau hoi: "Nhom doi tuong", "Dieu kien ve nha
 * o", "Dieu kien thu nhap", "Khu vuc cu tru/lam viec", "Chinh sach da huong",
 * "Ho so co ban". Diem la "n/6" - dem so tieu chi dat.
 *
 * Mot tieu chi khong phai lac nao cung ung voi mot cau hoi: "Khu vuc cu
 * tru/lam viec" tinh tu khoang cach, "Ho so co ban" khong hoi ai ca. Nen
 * tieu chi la BANG RIENG, moi dong khai no lay ket luan tu dau.
 *
 * LUAT KHOANG CACH (muc 3 cua cong thuc): dang co nha o van duoc mua neu
 * nha cach noi lam viec >= 30km VA noi lam viec cach du an <= 30km. Dap an
 * nao phai xet theo luat nay thi tich o "Xet theo khoang cach".
 *
 * Bang vn_wards chua co toa do - them hai cot de sau nay nap vao; khi con
 * trong thi lay tam toa do tinh (xem App\Classes\NoxhKhoangCach).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vn_wards')) {
            Schema::table('vn_wards', function (Blueprint $table) {
                if (!Schema::hasColumn('vn_wards', 'lat')) {
                    $table->decimal('lat', 10, 6)->nullable()->after('postal_code');
                }
                if (!Schema::hasColumn('vn_wards', 'lng')) {
                    $table->decimal('lng', 10, 6)->nullable()->after('lat');
                }
            });
        }

        if (Schema::hasTable('eligibility_options') && !Schema::hasColumn('eligibility_options', 'needs_distance')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                $table->boolean('needs_distance')->default(false)->after('stop_flow');
            });
        }

        if (!Schema::hasTable('eligibility_criteria')) {
            Schema::create('eligibility_criteria', function (Blueprint $table) {
                $table->id();
                $table->string('label', 191);
                // question = lay ket luan tu cau tra loi cua mot buoc
                // area     = tinh tu khoang cach noi lam viec - du an
                // fixed    = luon dat (vi du "Ho so co ban")
                $table->string('source', 20)->default('question');
                $table->unsignedBigInteger('eligibility_question_id')->nullable()->index();
                $table->string('pass_text', 500)->nullable();
                $table->string('unclear_text', 500)->nullable();
                $table->string('fail_text', 500)->nullable();
                $table->integer('order')->default(0);
                $table->tinyInteger('publish')->default(2);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('eligibility_checks')) {
            Schema::table('eligibility_checks', function (Blueprint $table) {
                if (!Schema::hasColumn('eligibility_checks', 'criteria_total')) {
                    $table->integer('criteria_total')->default(0)->after('failed');
                }
                if (!Schema::hasColumn('eligibility_checks', 'criteria_passed')) {
                    $table->integer('criteria_passed')->default(0)->after('criteria_total');
                }
                if (!Schema::hasColumn('eligibility_checks', 'criteria_json')) {
                    // Chup lai ket qua tung tieu chi ngay luc cham: quan tri
                    // sua tieu chi ve sau thi ket qua cu van doc dung nhu hom
                    // nguoi ta bam nut.
                    $table->text('criteria_json')->nullable()->after('criteria_passed');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_criteria');

        if (Schema::hasTable('eligibility_checks')) {
            Schema::table('eligibility_checks', function (Blueprint $table) {
                foreach (['criteria_total', 'criteria_passed', 'criteria_json'] as $cot) {
                    if (Schema::hasColumn('eligibility_checks', $cot)) {
                        $table->dropColumn($cot);
                    }
                }
            });
        }

        if (Schema::hasTable('eligibility_options') && Schema::hasColumn('eligibility_options', 'needs_distance')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                $table->dropColumn('needs_distance');
            });
        }

        if (Schema::hasTable('vn_wards')) {
            Schema::table('vn_wards', function (Blueprint $table) {
                foreach (['lat', 'lng'] as $cot) {
                    if (Schema::hasColumn('vn_wards', $cot)) {
                        $table->dropColumn($cot);
                    }
                }
            });
        }
    }
};
