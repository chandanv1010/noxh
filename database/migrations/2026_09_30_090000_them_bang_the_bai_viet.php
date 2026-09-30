<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bang "the" (tag) cua bai viet.
 *
 * Truoc day hang chu duoi bai viet lay tu o Meta keyword va tro sang trang
 * tim kiem - do chi la chuoi tu khoa cho may tim kiem, khong phai the. The
 * phai la mot doi tuong that: co duong dan rieng (/tags/...), va bam vao thi
 * ra dung nhung bai cung mang the do.
 *
 * Ten the la toan bo noi dung nen khong tach bang *_language rieng, chi giu
 * cot language_id ngay trong bang - mot the cua tieng Viet va mot the cua
 * tieng Anh la hai dong khac nhau.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('language_id')->default(1);
                $table->string('name', 191);
                $table->string('canonical', 191);
                $table->timestamps();

                // Mot duong dan chi tro toi mot the trong cung mot ngon ngu.
                $table->unique(['language_id', 'canonical']);
                $table->index('name');
            });
        }

        if (!Schema::hasTable('post_tag')) {
            Schema::create('post_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('post_id');
                $table->unsignedBigInteger('tag_id');

                $table->primary(['post_id', 'tag_id']);
                $table->index('tag_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tags');
    }
};
