<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ba khoi noi dung phu tro: van ban phap luat, hoi dap, chuyen gia.
 *
 * - legal_documents: khoi "Van ban phap luat moi" o man hinh Phap ly NOXH. Can
 *   so hieu va ngay hieu luc de sap xep va loc, nen khong dung posts duoc.
 * - qa_questions / qa_answers: khoi "Cau hoi noi bat" o trang chu va tab "Hoi dap"
 *   cua chi tiet du an. Nguoi dung dat cau hoi, chuyen gia tra loi, co duyet.
 * - experts: "Cong Hoa", "Nguyen Hoa - Co van Phap ly" xuat hien o 6/9 man hinh
 *   voi anh, chuc danh, hotline, danh sach cam ket. Neu de trong systems se rat roi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('doc_number', 100)->nullable();
            // law | decree | circular | official_letter | other
            $table->string('doc_type', 32)->default('other');
            $table->date('issued_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('issuer', 255)->nullable();
            $table->text('summary')->nullable();
            $table->string('file')->nullable();
            // pdf | doc | docx
            $table->string('file_type', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['publish', 'effective_date']);
            $table->index('doc_type');
            $table->index('is_featured');
        });

        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title', 255)->nullable();
            $table->string('image')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('zalo', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            // Danh sach cam ket, moi dong mot y.
            $table->text('commitments')->nullable();
            $table->boolean('is_default')->default(false);
            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['publish', 'order']);
            $table->index('is_default');
        });

        Schema::create('qa_questions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500);
            $table->text('content')->nullable();
            // Nguoi hoi: khach chua dang nhap nen luu tho, khong FK customers.
            $table->string('asker_name', 255)->nullable();
            $table->string('asker_phone', 20)->nullable();
            $table->string('asker_email')->nullable();
            $table->string('asker_avatar')->nullable();
            // Gan vao du an neu hoi ve du an cu the.
            $table->unsignedBigInteger('product_id')->nullable();
            // Gan vao chu de phap ly (attribute_catalogues) neu can.
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            // pending | approved | rejected
            $table->string('status', 20)->default('pending');
            $table->tinyInteger('publish')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['publish', 'status', 'created_at']);
            $table->index('product_id');
            $table->index('is_featured');
        });

        Schema::create('qa_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('qa_question_id');
            $table->text('content');
            // Nguoi tra loi la chuyen gia hoac quan tri vien.
            $table->unsignedBigInteger('expert_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_official')->default(true);
            $table->tinyInteger('publish')->default(2);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('qa_question_id');
            $table->foreign('qa_question_id')
                  ->references('id')->on('qa_questions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_answers');
        Schema::dropIfExists('qa_questions');
        Schema::dropIfExists('experts');
        Schema::dropIfExists('legal_documents');
    }
};
