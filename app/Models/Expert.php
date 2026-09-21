<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\QueryScopes;

/**
 * Chuyen vien tu van. Xuat hien o 6/9 man hinh thiet ke nen tach bang rieng
 * thay vi nhet vao Cau hinh he thong.
 */
class Expert extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $table = 'experts';

    protected $fillable = [
        'name', 'title', 'image', 'phone', 'zalo', 'email',
        'description', 'commitments', 'is_default', 'publish', 'order',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function answers()
    {
        return $this->hasMany(QaAnswer::class, 'expert_id', 'id');
    }
}
