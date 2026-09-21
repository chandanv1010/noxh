<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\QueryScopes;

/**
 * Bo ho so theo nhom doi tuong (cong nhan, can bo, ho ngheo...).
 *
 * Ba man hinh HO SO ngoai website deu doc chung bang nay, chi khac cach hien:
 * danh sach giay to / loc ra nhung cai co file mau / checklist tich chon.
 */
class DossierSet extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $table = 'dossier_sets';

    protected $fillable = [
        'name', 'canonical', 'description', 'subject_group', 'publish', 'order',
    ];

    public function items()
    {
        return $this->hasMany(DossierItem::class, 'dossier_set_id', 'id')->orderBy('order');
    }
}
