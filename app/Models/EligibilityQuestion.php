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
        'question', 'step_label', 'group', 'input_type', 'layout', 'image',
        'icon', 'icon_tone', 'hint', 'foot_note', 'foot_note_sub',
        'criteria_label', 'weight', 'required', 'publish', 'order',
    ];

    protected $casts = ['required' => 'boolean'];

    public const NHOM = [
        'housing' => 'Nha o',
        'income' => 'Thu nhap',
        'subject' => 'Doi tuong',
        'other' => 'Dieu kien khac',
    ];

    /**
     * Bo cuc cua khoi dap an ngoai trang.
     *
     * 'grid'   - luoi o dap an ngang hang nhau (ban ve w-1.jpg)
     * 'matrix' - chia thanh nhieu tam tinh huong, moi tam mot nhom dap an
     *            (ban ve w-2.jpg)
     */
    public const BO_CUC = [
        'grid' => 'Luoi o dap an',
        'matrix' => 'Chia theo tinh huong',
        'list' => 'Danh sach doc co mo ta',
        'card' => 'Ba the lon',
        'contact' => 'O nhap thong tin (buoc cuoi)',
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

    public function optionGroups()
    {
        return $this->hasMany(EligibilityOptionGroup::class, 'eligibility_question_id', 'id')
            ->orderBy('order')->orderBy('id');
    }

    public function panels()
    {
        return $this->hasMany(EligibilityPanel::class, 'eligibility_question_id', 'id')
            ->orderBy('order')->orderBy('id');
    }

    /** Buoc cuoi: khong hoi gi, chi xin ho ten - so dien thoai. */
    public function laBuocNhapTin(): bool
    {
        return $this->layout === 'contact';
    }

    /** Cau hoi nay ve theo kieu chia tam tinh huong? */
    public function laMatrix(): bool
    {
        return $this->layout === 'matrix';
    }

    /** Ten ngan in tren thanh buoc - chua dat thi lui ve ten nhom. */
    public function tenBuoc(): string
    {
        $ten = trim((string) $this->step_label);

        return $ten !== '' ? $ten : $this->tenNhom();
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
