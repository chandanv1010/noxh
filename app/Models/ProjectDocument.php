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

    /**
     * Hai tab "Phap ly" va "Tai lieu" cua trang chi tiet du an.
     *
     * Cung la giay to nen dung chung mot bang va mot man hinh quan tri; cai
     * khac nhau chi la no hien o tab nao.
     */
    public const NHOM = [
        'legal' => 'Hồ sơ pháp lý (tab "Pháp lý")',
        'doc' => 'Tài liệu tải về (tab "Tài liệu")',
    ];

    protected $fillable = ['product_id', 'group', 'title', 'doc_number', 'issued_date', 'issuer', 'file', 'file_type', 'description', 'publish', 'order'];

    public function project()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
