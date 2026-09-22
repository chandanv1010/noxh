<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trang thai duyet cho du an, doi xung voi posts.approval_status.
 *
 * Chi dung cho du an do nhan vien kinh doanh TU THEM, va chi khi quan tri bat
 * o "Du an nhan vien tu them -> Phai cho quan tri duyet" trong Cau hinh he
 * thong. Du an quan tri tao hoac giao cho nhan vien khong bao gio roi vao
 * trang thai nay.
 *
 * Mac dinh 'approved' de toan bo du an dang co giu nguyen hanh vi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'approval_status')) {
                $table->string('approval_status', 20)->default('approved')->after('publish');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
