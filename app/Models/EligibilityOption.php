<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\QueryScopes;

/**
 * Dap an cua mot cau hoi dieu kien, kem diem va ket luan.
 */
class EligibilityOption extends Model
{
    // BaseRepository::pagination() goi scope keyword()/publish() nen bat
    // buoc phai co trait nay, du bang khong co cot publish.
    use HasFactory, QueryScopes;

    protected $table = 'eligibility_options';

    protected $fillable = [
        'eligibility_question_id', 'eligibility_option_group_id',
        'label', 'image', 'icon', 'icon_tone', 'value',
        'verdict', 'score', 'note', 'order',
    ];

    public const KET_LUAN = [
        'pass' => 'Dat',
        'unclear' => 'Can kiem tra them',
        'fail' => 'Khong dat',
    ];

    public function group()
    {
        return $this->belongsTo(EligibilityOptionGroup::class, 'eligibility_option_group_id', 'id');
    }

    public function question()
    {
        return $this->belongsTo(EligibilityQuestion::class, 'eligibility_question_id', 'id');
    }

    /** [nen, net] cua hinh tron pastel - xem App\Classes\NoxhTone. */
    public function mauHinh(): array
    {
        return \App\Classes\NoxhTone::mau($this->icon_tone);
    }

    public function tenKetLuan()
    {
        return self::KET_LUAN[$this->verdict] ?? $this->verdict;
    }
}
