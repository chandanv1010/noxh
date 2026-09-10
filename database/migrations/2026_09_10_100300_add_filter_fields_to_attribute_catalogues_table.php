<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cho admin quyet dinh nhom thuoc tinh nao hien o bo loc, va hien kieu gi.
 *
 * Man hinh danh sach du an co 4 khoi loc voi hai kieu khac nhau: chon nhieu
 * (checkbox, kem so luong) va khoang so (muc gia, dien tich). Khong co cac cot
 * nay thi phai hard-code tung khoi trong view, moi lan them tieu chi loc lai
 * phai sua code.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_catalogues', function (Blueprint $table) {
            $table->boolean('filterable')->default(false)->after('publish');
            // checkbox | radio | select | range
            $table->string('filter_type', 20)->default('checkbox')->after('filterable');
            $table->unsignedInteger('filter_order')->default(0)->after('filter_type');
            // Rut gon danh sach dai: 0 = hien het, N = hien N dong dau roi "Xem them".
            $table->unsignedInteger('filter_visible_count')->default(0)->after('filter_order');

            $table->index(['filterable', 'filter_order']);
        });
    }

    public function down(): void
    {
        Schema::table('attribute_catalogues', function (Blueprint $table) {
            $table->dropIndex(['filterable', 'filter_order']);
            $table->dropColumn(['filterable', 'filter_type', 'filter_order', 'filter_visible_count']);
        });
    }
};
