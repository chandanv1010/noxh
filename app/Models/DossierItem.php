<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\QueryScopes;

/**
 * Mot loai giay to trong bo ho so.
 *
 * "Mau don" khong phai bang rieng - no chi la nhung dong co template_file.
 */
class DossierItem extends Model
{
    use HasFactory, QueryScopes;

    protected $table = 'dossier_items';

    protected $fillable = [
        'dossier_set_id', 'title', 'description', 'issued_by', 'copies',
        'is_required', 'template_file', 'template_name', 'template_type',
        'download_count', 'publish', 'order',
    ];

    protected $casts = ['is_required' => 'boolean'];

    public function set()
    {
        return $this->belongsTo(DossierSet::class, 'dossier_set_id', 'id');
    }

    public function getNameAttribute()
    {
        return (string) $this->title;
    }
}
