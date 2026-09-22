<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\QueryScopes;

class Product extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $fillable = [
        'image',
        'album',
        'publish',
        'follow',
        'order',
        'user_id',
        'product_catalogue_id',
        'price',
        'combo_price',
        'stock',
        'made_in',
        'code',
        'attributeCatalogue',
        'attribute',
        'variant',
        'qrcode',
        'warranty',
        'check',
        'seller_id',
        'total_lesson',
        'duration',
        'lecturer_id',
        'lession_content',
        'chapter',
        'iframe',
        'percent',
        'ml',

        // Cac cot rieng cua du an nha o xa hoi.
        'province_code',
        'ward_code',
        'address',
        'latitude',
        'longitude',
        'status',
        'price_type',
        'price_from',
        'price_to',
        'area_type',
        'area_from',
        'area_to',
        'total_units',
        'total_land_area',
        'scale_description',
        'apartment_types',
        'ownership_type',
        'investor_id',
        'start_date',
        'handover_date',
        'timeline_label',
        'is_featured',
    ];

    protected $casts = [
        'attribute' => 'json',
        'chapter' => 'json'
    ];


    /**
     * Trang thai du an nha o xa hoi. Quyet dinh nhan mau tren the du an va
     * mot trong bon nhom cua bo loc ngoai website.
     */
    public const TRANG_THAI_DU_AN = [
        'receiving' => 'Dang nhan ho so',
        'upcoming' => 'Sap mo ban',
        'building' => 'Dang trien khai',
        'handed' => 'Da ban giao',
    ];

    protected $table = 'products';

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'product_language', 'product_id', 'language_id')
            ->withPivot(
                'name',
                'canonical',
                'meta_title',
                'meta_keyword',
                'meta_description',
                'description',
                'content',
                'url',
            )->withTimestamps();
    }


    public function product_catalogues()
    {
        return $this->belongsToMany(ProductCatalogue::class, 'product_catalogue_product', 'product_id', 'product_catalogue_id');
    }

    public function product_variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_product_variant', 'product_id', 'promotion_id')
            ->withPivot(
                'variant_uuid',
                'model',
            )->withTimestamps();
    }


    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product', 'product_id', 'order_id')
            ->withPivot(
                'uuid',
                'name',
                'qty',
                'price',
                'priceOriginal',
                'option',
            );
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function sellers()
    {
        return $this->belongsTo(Customer::class, 'seller_id', 'id');
    }

    public function lecturers()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'id');
    }

    /**
     * Gioi han danh sach du an ve dung pham vi cua mot nhan vien kinh doanh:
     * du an TU HO TAO ra, cong voi du an duoc quan tri giao phu trach.
     *
     * Dung chung cho ca man hinh danh sach lan cac thao tac sua/xoa. Man hinh
     * danh sach ma loc dung nhung sua thi khong kiem tra lai la ho doi so tren
     * thanh dia chi se vao duoc du an cua nguoi khac.
     */
    public function scopeCuaNhanVien($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('products.user_id', $userId)
              ->orWhereExists(function ($sub) use ($userId) {
                  $sub->selectRaw(1)
                      ->from('product_user')
                      ->whereColumn('product_user.product_id', 'products.id')
                      ->where('product_user.user_id', $userId);
              });
        });
    }

    /**
     * Nhan vien kinh doanh phu trach du an nay - vua la danh sach hien ra
     * trang chi tiet du an, vua la can cu phan quyen cho bang dieu khien /sale.
     */
    public function nhanVienKinhDoanh()
    {
        return $this->belongsToMany(User::class, 'product_user', 'product_id', 'user_id')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('product_user.order');
    }
}
