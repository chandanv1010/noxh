<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bon bang phuc vu cac tab cua man hinh chi tiet du an.
 *
 * Gom chung mot migration vi chung la mot khoi chuc nang: chu dau tu, tien do,
 * ho so phap ly, cau hoi thuong gap - deu chi co nghia khi di kem mot du an.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Chu dau tu. Man hinh chi tiet du an co han khoi "Thong tin lien he du an"
        // voi ten cong ty, hotline, email, website, dia chi.
        // Mot chu dau tu co nhieu du an nen phai tach bang, khong nhet vao products.
        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name', 100)->nullable();
            $table->string('logo')->nullable();
            $table->string('hotline', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('address', 500)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('publish');
            $table->index('name');
        });

        // Tien do du an: cac moc "Quy II/2024 - Khoi cong xay dung".
        // date_label la chuoi vi du an cong bo theo quy, khong theo ngay cu the;
        // sort_date de sap xep dung thu tu thoi gian.
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('title');
            $table->string('date_label', 100)->nullable();
            $table->date('sort_date')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            // pending | doing | done
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'order']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        // Ho so phap ly cua du an: "Quyet dinh chu truong dau tu - So 123/QD-UBND
        // - Ngay 15/03/2024". Can so hieu va ngay ban hanh nen khong dung posts duoc.
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('title');
            $table->string('doc_number', 100)->nullable();
            $table->date('issued_date')->nullable();
            $table->string('issuer', 255)->nullable();
            $table->string('file')->nullable();
            // pdf | doc | image
            $table->string('file_type', 20)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'order']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        // Cau hoi thuong gap rieng cua tung du an (khoi accordion cuoi trang).
        Schema::create('project_faqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('question', 500);
            $table->text('answer')->nullable();
            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'order']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_faqs');
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('investors');
    }
};
