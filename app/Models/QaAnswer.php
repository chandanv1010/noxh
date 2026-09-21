<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Cau tra loi cua chuyen gia cho mot cau hoi.
 */
class QaAnswer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'qa_answers';

    protected $fillable = [
        'qa_question_id', 'content', 'expert_id', 'user_id', 'is_official', 'publish',
    ];

    protected $casts = ['is_official' => 'boolean'];

    public function question()
    {
        return $this->belongsTo(QaQuestion::class, 'qa_question_id', 'id');
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class, 'expert_id', 'id');
    }
}
