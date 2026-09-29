<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Mot o diem nhan trong the gia o dau trang chi tiet du an:
 * icon + dong tren ("Vi tri") + dong duoi ("trung tam").
 *
 * Du an nao khong khai o nao thi trang lay bon o mac dinh trong Cau hinh
 * chung, nen bang nay chi chua phan RIENG cua du an.
 */
class ProjectHighlight extends Model
{
    use HasFactory;

    protected $table = 'project_highlights';

    /** Hai khoi dung chung bang nay - xem cot `group`. */
    public const NHOM = [
        'price' => 'Ô điểm nhấn trong thẻ giá (đầu trang)',
        'amenity' => 'Ô tiện ích trong khối "Tiện ích"',
    ];

    protected $fillable = ['product_id', 'group', 'icon', 'title', 'subtitle', 'order'];

    public function project()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
