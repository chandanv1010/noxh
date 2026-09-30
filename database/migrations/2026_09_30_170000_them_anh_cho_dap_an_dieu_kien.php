<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Them o "Anh cua dap an" cho bang eligibility_options.
 *
 * Hinh tron trong ban ve noxh_image/w-1.jpg la TRANH MINH HOA nhieu mau (ong
 * si quan, chi cong nhan doi mu bao ho, ngoi nha mai ngoi...) chu khong phai
 * hinh net don sac, khong bo icon net nao ve lai dung duoc. Cac tranh nay cat
 * thang tu ban ve (tools/tach-anh-ban-ve.py) va luu thanh anh, nen dap an can
 * mot o duong dan anh rieng.
 *
 * Cot `icon` van giu: dap an nao chua co anh thi trang ngoai lui ve ve hinh
 * net trong vong tron mau - quan tri them mot nhom doi tuong moi la co ngay
 * hinh, khong phai di ve tranh.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('eligibility_options') && !Schema::hasColumn('eligibility_options', 'image')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                $table->string('image', 255)->nullable()->after('label');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('eligibility_options') && Schema::hasColumn('eligibility_options', 'image')) {
            Schema::table('eligibility_options', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
