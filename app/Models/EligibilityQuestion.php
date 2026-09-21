<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\QueryScopes;

/**
 * Cau hoi trong bo "Kiem tra dieu kien mua NOXH".
 *
 * Quy tac cham diem nam trong CSDL chu khong ghi cung trong ma nguon: dieu
 * kien mua NOXH thay doi theo nghi dinh, de trong code thi moi lan doi chinh
 * sach lai phai sua roi trien khai lai ban cap nhat.
 */
class EligibilityQuestion extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $table = 'eligibility_questions';

    protected $fillable = [
        'question', 'group', 'input_type', 'hint', 'criteria_label',
        'weight', 'required', 'publish', 'order',
    ];

    protected $casts = ['required' => 'boolean'];

    public const NHOM = [
        'housing' => 'Nha o',
        'income' => 'Thu nhap',
        'subject' => 'Doi tuong',
        'other' => 'Dieu kien khac',
    ];

    public const KIEU_NHAP = [
        'boolean' => 'Co / Khong',
        'select' => 'Chon mot dap an',
        'number' => 'Nhap so',
    ];

    public function options()
    {
        return $this->hasMany(EligibilityOption::class, 'eligibility_question_id', 'id')->orderBy('order');
    }

    public function tenNhom()
    {
        return self::NHOM[$this->group] ?? $this->group;
    }

    public function getNameAttribute()
    {
        return (string) $this->question;
    }
}
