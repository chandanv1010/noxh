<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Them o "Mau nhan" cho chuyen muc tin tuc.
 *
 * Trong ban thiet ke, ten chuyen muc phia tren moi tin co mau rieng: Chinh
 * sach mau do, Du an mau xanh la, Huong dan mau xanh lam. Mau do phai do
 * quan tri chon cho tung chuyen muc, khong duoc ghi cung theo ten chuyen muc
 * trong ma nguon - them mot chuyen muc moi la lai phai sua code.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('post_catalogues') && !Schema::hasColumn('post_catalogues', 'color')) {
            Schema::table('post_catalogues', function (Blueprint $table) {
                $table->string('color', 7)->nullable()->after('icon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('post_catalogues') && Schema::hasColumn('post_catalogues', 'color')) {
            Schema::table('post_catalogues', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        }
    }
};
