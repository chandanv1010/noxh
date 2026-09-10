<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hai module lo ra tu so do menu: TAI CHINH va HO SO.
 *
 * Ban phan tich dau tien bo sot hai muc nay - trang chu chi hien chung nhu hai
 * o "Thong tin huu ich" nen toi tuong la bai viet tinh. So do menu cho thay
 * chung la muc cap 1 co man hinh rieng:
 *
 *   TAI CHINH -> Tinh khoan vay / Kha nang tai chinh
 *   HO SO     -> Ho so can chuan bi / Mau don / Checklist
 *
 * TAI CHINH la cong cu tinh, khong phai noi dung: can bang lai suat ngan hang
 * de tinh, va lai suat thi thay doi lien tuc nen phai sua duoc trong admin.
 *
 * HO SO la danh sach giay to co the tich chon, kem file mau tai ve.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Goi vay cua tung ngan hang, dung cho cong cu "Tinh khoan vay".
        // Lai suat uu dai thuong chi ap dung mot so nam dau roi tha noi, nen
        // phai tach lam hai muc lai suat chu khong mot con so.
        Schema::create('loan_packages', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('bank_logo')->nullable();
            $table->string('package_name')->nullable();

            // Lai suat uu dai (%/nam) va so thang duoc huong.
            $table->decimal('preferential_rate', 5, 2)->nullable();
            $table->unsignedInteger('preferential_months')->nullable();
            // Lai suat sau uu dai (%/nam).
            $table->decimal('standard_rate', 5, 2)->nullable();

            // Ty le cho vay toi da tren gia tri can nha (%).
            $table->decimal('max_loan_ratio', 5, 2)->nullable();
            $table->unsignedInteger('max_term_years')->nullable();
            // Phi tra no truoc han (%).
            $table->decimal('prepayment_fee', 5, 2)->nullable();

            $table->text('conditions')->nullable();
            $table->text('note')->nullable();
            $table->string('hotline', 50)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->date('effective_from')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['publish', 'order']);
            $table->index('is_featured');
        });

        // Bo ho so: mot bo cho mot nhom doi tuong (cong nhan, can bo, ho ngheo...).
        Schema::create('dossier_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('canonical')->nullable();
            $table->text('description')->nullable();
            // Gan voi nhom doi tuong trong bo cau hoi dieu kien neu can.
            $table->string('subject_group', 100)->nullable();
            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['publish', 'order']);
            $table->index('canonical');
        });

        // Tung loai giay to trong bo ho so. Nguoi dung tich chon de theo doi
        // da chuan bi den dau (checklist), va tai file mau neu co.
        Schema::create('dossier_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dossier_set_id');
            $table->string('title');
            $table->text('description')->nullable();
            // Noi cap / xin giay to nay.
            $table->string('issued_by', 255)->nullable();
            $table->unsignedInteger('copies')->default(1);
            $table->boolean('is_required')->default(true);

            // File mau don kem theo, neu loai giay to nay co mau.
            $table->string('template_file')->nullable();
            $table->string('template_name')->nullable();
            $table->string('template_type', 20)->nullable();
            $table->unsignedInteger('download_count')->default(0);

            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['dossier_set_id', 'order']);
            $table->foreign('dossier_set_id')
                  ->references('id')->on('dossier_sets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_items');
        Schema::dropIfExists('dossier_sets');
        Schema::dropIfExists('loan_packages');
    }
};
