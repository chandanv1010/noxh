<?php

namespace App\Models;

use App\Traits\QueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Mot dong trong bang "Chi tiet ket qua theo tung tieu chi".
 *
 * Ba ban ve ket qua (thanh-cong / luu y / that bai) deu in sau tieu chi. Mot
 * tieu chi khong phai lac nao cung ung voi mot cau hoi: "Khu vuc cu tru/lam
 * viec" tinh tu khoang cach, "Ho so co ban" khong hoi ai ca - nen moi dong
 * khai ro no LAY KET LUAN TU DAU.
 */
class EligibilityCriterion extends Model
{
    use HasFactory, QueryScopes;

    protected $table = 'eligibility_criteria';

    protected $fillable = [
        'label', 'source', 'eligibility_question_id',
        'pass_text', 'unclear_text', 'fail_text', 'order', 'publish',
    ];

    /** Tieu chi nay lay ket luan tu dau. */
    public const NGUON = [
        'question' => 'Cau tra loi cua mot buoc',
        'area' => 'Khoang cach noi lam viec - du an',
        'fixed' => 'Luon dat',
    ];

    public function question()
    {
        return $this->belongsTo(EligibilityQuestion::class, 'eligibility_question_id', 'id');
    }

    /** Dong chu xam in duoi ten tieu chi, khac nhau theo ket luan. */
    public function moTa(string $ketLuan): string
    {
        return (string) match ($ketLuan) {
            'pass' => $this->pass_text,
            'fail' => $this->fail_text,
            default => $this->unclear_text,
        };
    }

    public function getNameAttribute()
    {
        return (string) $this->label;
    }
}
