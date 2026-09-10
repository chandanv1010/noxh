<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bien contacts thanh bang quan ly lead dung nghia.
 *
 * Bo anh mo phong co 4 diem thu lead khac nhau: form sidebar chi tiet du an,
 * "Dang ky tu van" tren the du an, ket qua kiem tra dieu kien, va form nhan tin
 * o trang chu. Bang contacts hien tai khong phan biet duoc chung, va quan trong
 * hon: khong co trang thai xu ly va khong gan duoc chuyen vien phu trach.
 *
 * Thieu hai cot `status` va `assigned_user_id` thi doi sale se quay lai xuat
 * Excel lam tay - ma toan bo gia tri cua cong thong tin nam o cho nay.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Lead den tu man hinh nao: project_detail | project_card | eligibility
            //                           | newsletter | legal_question | contact_page
            $table->string('source', 40)->nullable()->after('type');
            // Nhu cau quan tam: loai can ho / muc gia khach quan tam.
            $table->string('interest', 255)->nullable()->after('source');
            $table->string('province_code', 20)->nullable()->after('interest');
            // Neu lead sinh ra tu mot luot kiem tra dieu kien thi gan vao day,
            // de sale mo ra xem duoc ket qua 8 tieu chi cua khach.
            $table->unsignedBigInteger('eligibility_check_id')->nullable()->after('post_id');

            // --- Quy trinh xu ly ---
            // new | contacted | consulting | success | cancelled
            $table->string('status', 20)->default('new')->after('publish');
            $table->unsignedBigInteger('assigned_user_id')->nullable()->after('status');
            $table->timestamp('contacted_at')->nullable()->after('assigned_user_id');
            $table->text('note')->nullable()->after('contacted_at');

            $table->index('status');
            $table->index('assigned_user_id');
            $table->index('source');
            $table->index('eligibility_check_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['assigned_user_id']);
            $table->dropIndex(['source']);
            $table->dropIndex(['eligibility_check_id']);
            $table->dropIndex(['created_at']);

            $table->dropColumn([
                'source', 'interest', 'province_code', 'eligibility_check_id',
                'status', 'assigned_user_id', 'contacted_at', 'note',
            ]);
        });
    }
};
