<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\QueryScopes;

/**
 * Van ban phap luat cho trang "Phong phap ly".
 *
 * Khong dung chung bang posts vi can so hieu va ngay hieu luc de loc va sap
 * xep - hai thong tin posts khong co cho de luu.
 */
class LegalDocument extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $table = 'legal_documents';

    protected $fillable = [
        'title', 'doc_number', 'doc_type', 'issued_date', 'effective_date',
        'issuer', 'summary', 'file', 'file_type', 'file_size',
        'download_count', 'is_featured', 'publish', 'order', 'user_id',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'effective_date' => 'date',
        'is_featured' => 'boolean',
    ];

    public const LOAI = [
        'luat' => 'Luat',
        'nghi_dinh' => 'Nghi dinh',
        'thong_tu' => 'Thong tu',
        'quyet_dinh' => 'Quyet dinh',
        'cong_van' => 'Cong van',
        'other' => 'Van ban khac',
    ];

    public function tenLoai()
    {
        return self::LOAI[$this->doc_type] ?? $this->doc_type;
    }

    /** Scope keyword mac dinh do cot `name`, bang nay dung `title`. */
    public function getNameAttribute()
    {
        return (string) $this->title;
    }
}
