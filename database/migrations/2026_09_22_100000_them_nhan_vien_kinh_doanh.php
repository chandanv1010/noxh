<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nen cho tinh nang "nhan vien kinh doanh phu trach du an".
 *
 * Bon thay doi:
 *   1. Bang noi product_user - du an nao do nhung ai phu trach.
 *   2. Ba cot ho so cong khai tren bang users (chuc danh, zalo, email hien thi).
 *   3. Co is_sale tren user_catalogues - danh dau nhom nao la nhan vien kinh
 *      doanh, de khong phai ghi cung id nhom vao ma nguon.
 *   4. Cot approval_status tren posts - bai do nhan vien gui len phai cho
 *      quan tri duyet.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_user')) {
            Schema::create('product_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('user_id');
                // Thu tu hien thi ngoai trang chi tiet du an.
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'user_id'], 'product_user_unique');
                $table->index('user_id');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'title')) {
                $table->string('title', 150)->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'zalo')) {
                $table->string('zalo', 50)->nullable()->after('phone');
            }
            // Email dang nhap co the la email noi bo, khong phai email muon
            // cho khach nhin thay - nen tach rieng mot cot.
            if (!Schema::hasColumn('users', 'public_email')) {
                $table->string('public_email', 191)->nullable()->after('zalo');
            }
        });

        Schema::table('user_catalogues', function (Blueprint $table) {
            if (!Schema::hasColumn('user_catalogues', 'is_sale')) {
                $table->boolean('is_sale')->default(0)->after('description');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'approval_status')) {
                // approved | pending | rejected. Mac dinh la approved de moi
                // bai viet cu do quan tri tao van giu nguyen hanh vi.
                $table->string('approval_status', 20)->default('approved')->after('publish');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['title', 'zalo', 'public_email']);
        });

        Schema::table('user_catalogues', function (Blueprint $table) {
            $table->dropColumn('is_sale');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
