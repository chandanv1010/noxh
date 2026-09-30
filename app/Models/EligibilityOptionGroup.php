<?php

namespace App\Models;

use App\Traits\QueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Mot TINH HUONG gom nhieu dap an, dung o bo cuc "matrix" cua wizard.
 *
 * Ban ve noxh_image/w-2.jpg chia cau hoi thu nhap thanh ba tam: "Doc than",
 * "Doc than nuoi con nho", "Da ket hon". Moi tam co tranh, ten, dong ghi chu
 * va mau nen rieng; cac muc thu nhap nam trong tam tuong ung.
 *
 * Nguoi tra loi van chi chon MOT dap an trong ca ba tam.
 */
class EligibilityOptionGroup extends Model
{
    // BaseRepository::pagination() goi scope keyword()/publish() nen bat
    // buoc phai co trait nay, du bang khong co cot publish.
    use HasFactory, QueryScopes;

    protected $table = 'eligibility_option_groups';

    protected $fillable = [
        'eligibility_question_id', 'label', 'note', 'image', 'icon', 'tone', 'order',
    ];

    public function question()
    {
        return $this->belongsTo(EligibilityQuestion::class, 'eligibility_question_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(EligibilityOption::class, 'eligibility_option_group_id', 'id')->orderBy('order');
    }

    /** [nen, net] cua tam - xem App\Classes\NoxhTone. */
    public function mauHinh(): array
    {
        return \App\Classes\NoxhTone::mau($this->tone);
    }

    public function getNameAttribute()
    {
        return (string) $this->label;
    }
}
