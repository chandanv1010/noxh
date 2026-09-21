<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ho so phap ly cua mot du an.
 *
 * Chi co nghia khi di kem mot du an nen khong lam man hinh rieng: quan tri
 * sua ngay trong tab cua man hinh du an.
 */
class ProjectDocument extends Model
{
    use HasFactory;

    protected $table = 'project_documents';

    protected $fillable = ['product_id', 'title', 'doc_number', 'issued_date', 'issuer', 'file', 'file_type', 'description', 'publish', 'order'];

    public function project()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
