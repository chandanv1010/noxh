<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module kiem tra dieu kien mua NOXH.
 *
 * Bo 8 cau hoi chia 4 tab (nha o / thu nhap / doi tuong / dieu kien khac), cham
 * diem ra ket qua co ma tra cuu va han 30 ngay.
 *
 * QUYET DINH THIET KE: quy tac cham diem nam trong CSDL, khong hard-code trong
 * PHP. Ly do: dieu kien mua NOXH doi theo nghi dinh - ban tin trong bo anh mo
 * phong nhac Nghi dinh 136/2026 nang muc thu nhap tu 07/04/2026. Neu de trong
 * code thi moi lan chinh sach doi lai phai sua code va deploy; de trong CSDL
 * thi nguoi quan tri tu sua duoc trong admin.
 *
 * Cach cham diem: moi cau hoi co `weight`. Moi dap an co `verdict`
 * (pass / unclear / fail) va `score`. Ket qua = tong diem dat / tong diem toi da.
 * Cau tra loi cho verdict = fail thi ha han ket qua ve "khong du dieu kien"
 * bat ke diem, vi day la dieu kien loai tru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibility_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question', 500);
            // Tab tren form: housing | income | subject | other
            $table->string('group', 32)->default('other');
            // boolean | select | number
            $table->string('input_type', 20)->default('boolean');
            $table->string('hint', 500)->nullable();
            // Nhan hien o bang "Chi tiet ket qua" - thuong ngan hon cau hoi.
            $table->string('criteria_label', 255)->nullable();
            $table->unsignedInteger('weight')->default(1);
            $table->boolean('required')->default(true);
            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['publish', 'group', 'order']);
        });

        Schema::create('eligibility_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('eligibility_question_id');
            $table->string('label', 255);
            // Gia tri luu vao cau tra loi.
            $table->string('value', 100);
            // pass | unclear | fail
            $table->string('verdict', 20)->default('pass');
            $table->integer('score')->default(0);
            // Ghi chu hien o cot "Ghi chu" cua bang ket qua.
            $table->string('note', 255)->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['eligibility_question_id', 'order']);
            $table->foreign('eligibility_question_id')
                  ->references('id')->on('eligibility_questions')->cascadeOnDelete();
        });

        Schema::create('eligibility_checks', function (Blueprint $table) {
            $table->id();
            // Ma ket qua hien cho nguoi dung: NOXH-260910-1530
            $table->string('code', 40)->unique();
            $table->string('name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('province_code', 20)->nullable();

            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('answered')->default(0);
            $table->unsignedInteger('passed')->default(0);
            $table->unsignedInteger('unclear')->default(0);
            $table->unsignedInteger('failed')->default(0);
            // 0-100
            $table->unsignedInteger('score_percent')->default(0);
            // high | medium | low | not_eligible
            $table->string('result_level', 20)->default('low');

            $table->timestamp('expires_at')->nullable();
            // Nguoi dung phai tich dong y chinh sach truoc khi luu.
            $table->boolean('consent')->default(false);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index('phone');
            $table->index('province_code');
            $table->index('created_at');
        });

        Schema::create('eligibility_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('eligibility_check_id');
            $table->unsignedBigInteger('eligibility_question_id');
            $table->unsignedBigInteger('eligibility_option_id')->nullable();
            // Luu ca gia tri tho: cau kieu number khong co option.
            $table->string('answer_value', 255)->nullable();
            $table->string('verdict', 20)->default('unclear');
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->index('eligibility_check_id');
            $table->foreign('eligibility_check_id')
                  ->references('id')->on('eligibility_checks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_answers');
        Schema::dropIfExists('eligibility_checks');
        Schema::dropIfExists('eligibility_options');
        Schema::dropIfExists('eligibility_questions');
    }
};
