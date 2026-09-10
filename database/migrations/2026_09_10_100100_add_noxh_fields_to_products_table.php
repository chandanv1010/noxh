<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bien bang products thanh bang du an NOXH.
 *
 * Bang products goc khong co MOT truong nao de loc du an: khong dia ly, khong
 * trang thai, gia thi chi mot gia tri. Doi chieu man hinh danh sach du an va
 * chi tiet du an trong bo anh mo phong, day la cac truong con thieu.
 *
 * Ve gia va dien tich: co du an cong bo mot con so chinh xac, co du an cong bo
 * khoang. Vi vay dung cap (type, from, to) - type = 'exact' thi chi dung `from`,
 * type = 'range' thi dung ca hai. Trang admin cho chon bang radio.
 *
 * KHONG dung lai cot `price` cu: no dang la gia san pham cua ma nguon truoc va
 * don vi khac han (VND moi san pham, so voi trieu/m2 o day). De nguyen cho khoi
 * vo du lieu mau.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // --- Dia ly: hai cap theo bo don vi hanh chinh moi ---
            $table->string('province_code', 20)->nullable()->after('product_catalogue_id');
            $table->string('ward_code', 20)->nullable()->after('province_code');
            $table->string('address', 500)->nullable()->after('ward_code');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            // --- Trang thai du an: badge tren the du an ---
            // receiving | opening_soon | in_progress | handed_over | upcoming
            $table->string('status', 32)->nullable()->after('longitude');

            // --- Gia ban (trieu dong/m2) ---
            $table->string('price_type', 10)->default('range')->after('status');
            $table->decimal('price_from', 12, 2)->nullable()->after('price_type');
            $table->decimal('price_to', 12, 2)->nullable()->after('price_from');

            // --- Dien tich can ho (m2) ---
            $table->string('area_type', 10)->default('range')->after('price_to');
            $table->decimal('area_from', 10, 2)->nullable()->after('area_type');
            $table->decimal('area_to', 10, 2)->nullable()->after('area_from');

            // --- Quy mo du an ---
            $table->unsignedInteger('total_units')->nullable()->after('area_to');
            $table->decimal('total_land_area', 12, 2)->nullable()->after('total_units');
            $table->string('scale_description', 255)->nullable()->after('total_land_area');
            $table->string('apartment_types', 255)->nullable()->after('scale_description');
            $table->string('ownership_type', 100)->nullable()->after('apartment_types');

            // --- Chu dau tu ---
            $table->unsignedBigInteger('investor_id')->nullable()->after('ownership_type');

            // --- Moc thoi gian ---
            $table->date('start_date')->nullable()->after('investor_id');
            $table->date('handover_date')->nullable()->after('start_date');
            $table->string('timeline_label', 100)->nullable()->after('handover_date');

            // --- Noi bat tren trang chu ---
            $table->boolean('is_featured')->default(false)->after('timeline_label');

            // Index cho bo loc. Cac cot nay bi loc va sap xep trong moi request o
            // man hinh danh sach du an, khong the de quet toan bang.
            $table->index('province_code');
            $table->index('ward_code');
            $table->index('status');
            $table->index(['price_from', 'price_to']);
            $table->index(['area_from', 'area_to']);
            $table->index('is_featured');
            $table->index('investor_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['province_code']);
            $table->dropIndex(['ward_code']);
            $table->dropIndex(['status']);
            $table->dropIndex(['price_from', 'price_to']);
            $table->dropIndex(['area_from', 'area_to']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['investor_id']);

            $table->dropColumn([
                'province_code', 'ward_code', 'address', 'latitude', 'longitude',
                'status', 'price_type', 'price_from', 'price_to',
                'area_type', 'area_from', 'area_to',
                'total_units', 'total_land_area', 'scale_description',
                'apartment_types', 'ownership_type', 'investor_id',
                'start_date', 'handover_date', 'timeline_label', 'is_featured',
            ]);
        });
    }
};
