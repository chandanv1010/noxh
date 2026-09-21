<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\QueryScopes;

/**
 * Chu dau tu du an. Mot chu dau tu co nhieu du an nen phai tach bang rieng
 * chu khong nhet thang vao bang products.
 */
class Investor extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $table = 'investors';

    protected $fillable = [
        'name', 'short_name', 'logo', 'hotline', 'email', 'website',
        'address', 'description', 'publish', 'order', 'user_id',
    ];

    public function projects()
    {
        return $this->hasMany(Product::class, 'investor_id', 'id');
    }
}
