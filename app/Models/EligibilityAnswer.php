<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Cau tra loi cua mot luot kiem tra dieu kien.
 */
class EligibilityAnswer extends Model
{
    use HasFactory;

    protected $table = 'eligibility_answers';

    protected $fillable = [
        'eligibility_check_id', 'eligibility_question_id', 'eligibility_option_id',
        'answer_value', 'verdict', 'score',
    ];

    public function question()
    {
        return $this->belongsTo(EligibilityQuestion::class, 'eligibility_question_id', 'id');
    }

    public function option()
    {
        return $this->belongsTo(EligibilityOption::class, 'eligibility_option_id', 'id');
    }
}
