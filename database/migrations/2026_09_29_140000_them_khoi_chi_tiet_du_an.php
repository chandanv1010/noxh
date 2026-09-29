<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhung o du lieu con thieu cua man hinh chi tiet du an
 * (ban thiet ke noxh_image/product-detail-fix.jpg).
 *
 * Doi chieu ban ve voi bang products hien co thi thieu han sau thu, tat ca
 * deu la thu quan tri phai nhap duoc chu khong the tinh ra:
 *
 *   - nut "Xem video du an" de len anh lon        -> video_url
 *   - anh mat bang tong the ben phai bang Tong quan -> site_plan_image
 *   - anh cong truong trong khoi Tien do            -> progress_image
 *   - nut "Xem cap nhat tien do"                    -> progress_url
 *   - anh ban do trong khoi Vi tri du an            -> map_image
 *   - nut "Xem tren Google Maps"                    -> map_url
 *
 * map_url de trong thi trang tu dung duong dan Google Maps tu latitude/longitude
 * (da co san), nen day chi la duong tat khi chu dau tu gui link rieng.
 *
 * Kem hai bang moi: cac loai can ho va bon o diem nhan trong the gia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('video_url', 500)->nullable()->after('image');
            $table->string('site_plan_image', 500)->nullable()->after('video_url');
            $table->string('progress_image', 500)->nullable()->after('site_plan_image');
            $table->string('progress_url', 500)->nullable()->after('progress_image');
            $table->string('map_image', 500)->nullable()->after('progress_url');
            $table->string('map_url', 500)->nullable()->after('map_image');
        });

        // Khoi "CAC LOAI CAN HO": moi the la mot loai can, co anh mat bang
        // rieng, khoang dien tich rieng va khoang gia rieng.
        //
        // Gia o day tinh bang TY dong cho ca can ("1,075 - 1,180 ty"), khac
        // han price_from/price_to cua du an (trieu dong moi m2). Hai don vi
        // khac nhau nen phai la cot khac, khong dung chung duoc.
        //
        // `bullets`: moi dong mot gach dau dong ("Phu hop nguoi doc than").
        // Khong tach thanh bang rieng vi quan tri chi go vai dong, mot o
        // textarea de nhap hon mot man hinh con.
        Schema::create('project_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name');
            $table->string('image', 500)->nullable();
            $table->decimal('area_from', 10, 2)->nullable();
            $table->decimal('area_to', 10, 2)->nullable();
            $table->decimal('price_from', 12, 3)->nullable();
            $table->decimal('price_to', 12, 3)->nullable();
            $table->string('price_unit', 20)->default('tỷ');
            $table->text('bullets')->nullable();
            $table->string('url', 500)->nullable();
            $table->tinyInteger('publish')->default(2);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'order']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        // Bon o duoi bang thong so trong the gia: "Vi tri / trung tam",
        // "Ha tang / dong bo"... Moi du an ban mot the manh khac nhau nen
        // phai sua duoc tung du an; du an nao khong nhap thi trang lay bon o
        // mac dinh trong Cau hinh chung.
        Schema::create('project_highlights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('icon', 50)->nullable();
            $table->string('title', 100);
            $table->string('subtitle', 100)->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'order']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        // Form "DANG KY NHAN THONG TIN DU AN" o cot phai co bon o. Ba o dau da
        // co cho chua (name, phone, interest), rieng "Thoi gian du kien mua"
        // thi chua - ma day chinh la o cho sale biet nen goi khach nao truoc.
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('buy_timeline', 100)->nullable()->after('interest');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('buy_timeline');
        });

        Schema::dropIfExists('project_highlights');
        Schema::dropIfExists('project_units');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'video_url', 'site_plan_image', 'progress_image',
                'progress_url', 'map_image', 'map_url',
            ]);
        });
    }
};
