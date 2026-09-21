<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Moc tien do cua mot du an.
 *
 * Chi co nghia khi di kem mot du an nen khong lam man hinh rieng: quan tri
 * sua ngay trong tab cua man hinh du an.
 */
class ProjectMilestone extends Model
{
    use HasFactory;

    protected $table = 'project_milestones';

    protected $fillable = ['product_id', 'title', 'date_label', 'sort_date', 'description', 'image', 'status', 'order'];

    public function project()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
