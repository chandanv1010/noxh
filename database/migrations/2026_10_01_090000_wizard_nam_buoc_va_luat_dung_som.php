<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bo kiem tra dieu kien rut ve NAM BUOC va them luat "dung som".
 *
 * Nam ban ve w-1..w-5 cho thay bo cau hoi that chi con nam buoc: Doi tuong,
 * Thu nhap, Chinh sach, Nha o, Ket qua. Buoc cuoi khong phai cau hoi ma la o
 * nhap ho ten - so dien thoai, nen cau hoi can them kieu bo cuc 'contact'.
 *
 * Them hai thu:
 *
 *   eligibility_options.stop_flow
 *       Chon dap an nay la BIET CHAC khong du dieu kien, hoi tiep vo nghia.
 *       Vi du o buoc Chinh sach: "Da tung duoc ho tro" thi bo qua buoc Nha o,
 *       di thang sang buoc Ket qua. De luat nay trong ma nguon thi moi lan
 *       nghi dinh doi lai phai sua code, nen no la mot o tich trong quan tri.
 *
 *   eligibility_panels
 *       Cac tam o cot phai cua ban ve w-3, w-4, w-5 ("Vi sao can thong tin
 *       nay?", "Thong tin cua ban luon duoc bao mat", "Sau khi xem ket
 *       qua"...). Moi tam co hinh, tieu de, doan chu va mot danh sach gach
 *       dau dong; noi dung khac nhau theo tung buoc nen phai la du lieu.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('eligibility_options') && !Schema::hasColumn('eligibility_options', 'stop_flow')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                $table->boolean('stop_flow')->default(false)->after('verdict');
            });
        }

        if (!Schema::hasTable('eligibility_panels')) {
            Schema::create('eligibility_panels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('eligibility_question_id')->index();
                $table->string('heading', 191)->nullable();
                $table->text('body')->nullable();
                // Moi dong mot y - trang ngoai ve thanh gach dau dong co dau tich.
                $table->text('bullets')->nullable();
                $table->string('image', 255)->nullable();
                $table->string('icon', 60)->nullable();
                $table->string('tone', 20)->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_panels');

        if (Schema::hasTable('eligibility_options') && Schema::hasColumn('eligibility_options', 'stop_flow')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                $table->dropColumn('stop_flow');
            });
        }
    }
};
