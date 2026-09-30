<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Them o "Chu thich anh dai dien" cho bai viet.
 *
 * Ban ve trang chi tiet tin (noxh_image/tin-tuc-fix.webp) in mot dong chu nho
 * mau xam ngay duoi anh dau bai: "Viec nang muc thu nhap mo rong them co hoi
 * ... (Anh minh hoa)". Dong do la cua RIENG tung bai nen khong the lay tu o
 * cau hinh chung, cung khong phai doan mo ta ngan (doan do dung o danh sach).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts') && !Schema::hasColumn('posts', 'image_caption')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->string('image_caption', 500)->nullable()->after('image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'image_caption')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('image_caption');
            });
        }
    }
};
