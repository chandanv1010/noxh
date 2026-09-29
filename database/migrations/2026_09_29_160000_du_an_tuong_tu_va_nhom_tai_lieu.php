<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hai thu con thieu cua trang chi tiet du an:
 *
 *   1. Bang noi product_related - quan tri tu chon du an nao hien o khoi
 *      "DU AN TUONG TU TAI ...". Truoc day khoi nay tu doc ra theo tinh, nen
 *      khong the dua mot du an cua tinh khac vao du no lien quan hon.
 *
 *   2. Cot "group" cua project_documents - ban thiet ke co hai tab rieng
 *      "Phap ly" va "Tai lieu". Hai tab do cung la giay to nen dung chung
 *      mot bang va mot man hinh quan tri, phan biet bang cot nay.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_related')) {
            Schema::create('product_related', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('related_id');
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'related_id'], 'product_related_unique');
                $table->index(['product_id', 'order'], 'product_related_order');
            });
        }

        if (!Schema::hasColumn('project_documents', 'group')) {
            Schema::table('project_documents', function (Blueprint $table) {
                $table->string('group', 20)->default('legal')->after('product_id');
                $table->index(['product_id', 'group', 'order'], 'pd_product_group_order');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_related');

        if (Schema::hasColumn('project_documents', 'group')) {
            Schema::table('project_documents', function (Blueprint $table) {
                $table->dropIndex('pd_product_group_order');
                $table->dropColumn('group');
            });
        }
    }
};
