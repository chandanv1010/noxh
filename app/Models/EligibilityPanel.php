<?php

namespace App\Models;

use App\Traits\QueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Mot tam o cot phai cua buoc wizard.
 *
 * Ban ve w-3, w-4, w-5 deu co cot phai gom vai tam: "Vi sao can thong tin
 * nay?", "Thong tin cua ban luon duoc bao mat", mot buc tranh, "Sau khi xem
 * ket qua"... Noi dung khac nhau theo tung buoc nen phai la du lieu chu
 * khong viet cung trong Blade.
 */
class EligibilityPanel extends Model
{
    // BaseRepository::pagination() goi scope keyword()/publish() nen bat
    // buoc phai co trait nay, du bang khong co cot publish.
    use HasFactory, QueryScopes;

    protected $table = 'eligibility_panels';

    protected $fillable = [
        'eligibility_question_id', 'heading', 'body', 'bullets',
        'image', 'icon', 'tone', 'order',
    ];

    public function question()
    {
        return $this->belongsTo(EligibilityQuestion::class, 'eligibility_question_id', 'id');
    }

    /** Moi dong mot y - bo dong trong va khoang trang thua. */
    public function dongY(): array
    {
        $ra = [];

        foreach (preg_split('/\r\n|\r|\n/', (string) $this->bullets) as $dong) {
            $dong = trim($dong);

            if ($dong !== '') {
                $ra[] = $dong;
            }
        }

        return $ra;
    }

    /** [nen, net] cua tam - xem App\Classes\NoxhTone. */
    public function mauHinh(): array
    {
        return \App\Classes\NoxhTone::mau($this->tone);
    }

    public function getNameAttribute()
    {
        return (string) $this->heading;
    }
}
