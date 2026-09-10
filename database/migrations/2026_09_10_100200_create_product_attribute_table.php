<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gan thuoc tinh TRUC TIEP vao san pham (du an).
 *
 * Truoc day thuoc tinh chi gan duoc vao bien the qua product_variant_attribute.
 * Voi NOXH thi moi can nha la mot san pham rieng biet, khong dung bien the -
 * nen can bang pivot nay de sau nay loc du lieu.
 *
 * Cot products.attribute cu la chuoi JSON: khong JOIN duoc, va dang rong o ca
 * 69 dong. Bo loc co dem so luong ("Dang nhan ho so 19") bat buoc phai JOIN
 * duoc, nen phai co bang nay.
 *
 * Luu ca attribute_catalogue_id de dem theo tung nhom thuoc tinh bang mot
 * truy van GROUP BY duy nhat, khong phai JOIN nguoc len bang attributes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_catalogue_id')->nullable();
            $table->timestamps();

            $table->primary(['product_id', 'attribute_id']);
            $table->index('attribute_id');
            $table->index('attribute_catalogue_id');

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute');
    }
};
