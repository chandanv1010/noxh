<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Mot loai can ho cua du an - khoi "CAC LOAI CAN HO" o trang chi tiet.
 *
 * Gia o day la gia CA CAN tinh bang ty dong, khac don vi voi gia du an
 * (trieu dong moi m2). Dung nham hai cai nay thi the can ho hien sai gap
 * hang nghin lan ma khong loi gi ca, nen don vi duoc ghi han vao cot
 * price_unit de con doi chieu.
 */
class ProjectUnit extends Model
{
    use HasFactory;

    protected $table = 'project_units';

    protected $fillable = [
        'product_id', 'name', 'image',
        'area_from', 'area_to',
        'price_from', 'price_to', 'price_unit',
        'bullets', 'url', 'publish', 'order',
    ];

    public function project()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /** Cac gach dau dong, moi dong mot y. Bo dong trong do quan tri go thua. */
    public function getDiemAttribute(): array
    {
        $dong = preg_split('/\r\n|\r|\n/', (string) $this->bullets) ?: [];

        return array_values(array_filter(array_map('trim', $dong), fn ($d) => $d !== ''));
    }
}
