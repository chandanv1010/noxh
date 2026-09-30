<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buoc 2 cua wizard (ban ve noxh_image/w-2.jpg) co BO CUC KHAC han buoc 1.
 *
 * Buoc 1 la mot luoi o dap an ngang hang nhau. Buoc 2 chia dap an thanh BA
 * TINH HUONG - "Doc than", "Doc than nuoi con nho", "Da ket hon" - moi tinh
 * huong la mot tam mau rieng co tranh, ten, dong ghi chu, ben trong moi tam
 * moi co cac muc thu nhap de chon.
 *
 * Nguoi tra loi van chi chon MOT dap an trong ca ba tam (thu nhap cua ho chi
 * roi vao mot truong hop), nen khong tach thanh nhieu cau hoi - chi them mot
 * tang "nhom dap an" o giua cau hoi va dap an.
 *
 * Cac cot them cho cau hoi:
 *   layout          'grid' (nhu buoc 1) hay 'matrix' (nhu buoc 2)
 *   image/icon      hinh tron in tren dau cau hoi o bo cuc matrix
 *   foot_note_sub   dong thu hai cua dai luu y (dong dau in dam)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('eligibility_questions')) {
            Schema::table('eligibility_questions', function (Blueprint $table) {
                if (!Schema::hasColumn('eligibility_questions', 'layout')) {
                    $table->string('layout', 20)->default('grid')->after('input_type');
                }
                if (!Schema::hasColumn('eligibility_questions', 'image')) {
                    $table->string('image', 255)->nullable()->after('layout');
                }
                if (!Schema::hasColumn('eligibility_questions', 'icon')) {
                    $table->string('icon', 60)->nullable()->after('image');
                }
                if (!Schema::hasColumn('eligibility_questions', 'icon_tone')) {
                    $table->string('icon_tone', 20)->nullable()->after('icon');
                }
                if (!Schema::hasColumn('eligibility_questions', 'foot_note_sub')) {
                    $table->string('foot_note_sub', 500)->nullable()->after('foot_note');
                }
            });
        }

        if (!Schema::hasTable('eligibility_option_groups')) {
            Schema::create('eligibility_option_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('eligibility_question_id')->index();
                $table->string('label', 191);
                $table->string('note', 255)->nullable();
                $table->string('image', 255)->nullable();
                $table->string('icon', 60)->nullable();
                $table->string('tone', 20)->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('eligibility_options') && !Schema::hasColumn('eligibility_options', 'eligibility_option_group_id')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                $table->unsignedBigInteger('eligibility_option_group_id')->nullable()
                    ->after('eligibility_question_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eligibility_options') && Schema::hasColumn('eligibility_options', 'eligibility_option_group_id')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                $table->dropColumn('eligibility_option_group_id');
            });
        }

        Schema::dropIfExists('eligibility_option_groups');

        if (Schema::hasTable('eligibility_questions')) {
            Schema::table('eligibility_questions', function (Blueprint $table) {
                foreach (['layout', 'image', 'icon', 'icon_tone', 'foot_note_sub'] as $cot) {
                    if (Schema::hasColumn('eligibility_questions', $cot)) {
                        $table->dropColumn($cot);
                    }
                }
            });
        }
    }
};
