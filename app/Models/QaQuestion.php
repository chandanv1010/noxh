<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\QueryScopes;

/**
 * Cau hoi do nguoi dung gui len muc Hoi dap.
 *
 * publish mac dinh la 1 (an): cau hoi phai duoc duyet moi hien ra ngoai
 * website, khong de khach dang thang len trang.
 */
class QaQuestion extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $table = 'qa_questions';

    protected $fillable = [
        'title', 'content', 'asker_name', 'asker_phone', 'asker_email', 'asker_avatar',
        'product_id', 'topic_id', 'view_count', 'is_featured', 'status', 'publish',
    ];

    protected $casts = ['is_featured' => 'boolean'];

    public const TRANG_THAI = [
        'pending' => 'Cho tra loi',
        'answered' => 'Da tra loi',
        'rejected' => 'Tu choi',
    ];

    public const MAU_TRANG_THAI = [
        'pending' => 'danger',
        'answered' => 'success',
        'rejected' => 'default',
    ];

    public function answers()
    {
        return $this->hasMany(QaAnswer::class, 'qa_question_id', 'id');
    }

    public function tenTrangThai()
    {
        return self::TRANG_THAI[$this->status] ?? $this->status;
    }

    public function mauTrangThai()
    {
        return self::MAU_TRANG_THAI[$this->status] ?? 'default';
    }

    public function getNameAttribute()
    {
        return (string) $this->title;
    }
}
