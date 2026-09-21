<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Cau hoi thuong gap rieng cua mot du an.
 *
 * Chi co nghia khi di kem mot du an nen khong lam man hinh rieng: quan tri
 * sua ngay trong tab cua man hinh du an.
 */
class ProjectFaq extends Model
{
    use HasFactory;

    protected $table = 'project_faqs';

    protected $fillable = ['product_id', 'question', 'answer', 'publish', 'order'];

    public function project()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
