<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bang project_highlights phuc vu them khoi "Tien ich" cua trang chi tiet.
 *
 * Hai khoi nay giong het nhau ve du lieu - deu la "mot hinh + mot dong chu
 * + mot dong chu phu" gan vao du an - nen dung chung mot bang va phan biet
 * bang cot `group`, thay vi de them mot bang va mot man hinh quan tri nua
 * ma quan tri lai phai hoc lai tu dau.
 *
 *   price   - bon o diem nhan trong the gia o dau trang
 *   amenity - cac o tien ich trong khoi "Tien ich"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_highlights', function (Blueprint $table) {
            $table->string('group', 20)->default('price')->after('product_id');
            $table->index(['product_id', 'group', 'order'], 'ph_product_group_order');
        });
    }

    public function down(): void
    {
        Schema::table('project_highlights', function (Blueprint $table) {
            $table->dropIndex('ph_product_group_order');
            $table->dropColumn('group');
        });
    }
};
