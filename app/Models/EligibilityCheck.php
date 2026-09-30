<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\QueryScopes;

/**
 * Mot luot khach kiem tra dieu kien. Trong admin chi doc - ban ghi sinh ra tu
 * website, khong ai tao tay.
 */
class EligibilityCheck extends Model
{
    use HasFactory, QueryScopes;

    protected $table = 'eligibility_checks';

    protected $fillable = [
        'code', 'name', 'phone', 'email', 'province_code', 'ward_code', 'work_province_code', 'work_ward_code', 'project_ids',
        'total_questions', 'answered', 'passed', 'unclear', 'failed',
        'criteria_total', 'criteria_passed', 'criteria_json',
        'score_percent', 'result_level', 'expires_at', 'consent', 'ip', 'user_agent',
    ];

    /**
     * Sau dong "Chi tiet ket qua theo tung tieu chi".
     *
     * Chup lai ngay luc cham nen quan tri sua tieu chi ve sau, ket qua cu
     * van doc dung nhu hom nguoi ta bam nut.
     */
    public function tieuChi(): array
    {
        $ds = json_decode((string) $this->criteria_json, true);

        return is_array($ds) ? $ds : [];
    }

    protected $casts = [
        'expires_at' => 'datetime',
        'consent' => 'boolean',
    ];

    public const MUC = [
        'high' => 'Kha nang cao',
        'medium' => 'Can kiem tra them',
        'low' => 'Kha nang thap',
    ];

    public const MAU_MUC = [
        'high' => 'success',
        'medium' => 'warning',
        'low' => 'danger',
    ];

    public function answers()
    {
        return $this->hasMany(EligibilityAnswer::class, 'eligibility_check_id', 'id');
    }

    public function tenMuc()
    {
        return self::MUC[$this->result_level] ?? $this->result_level;
    }

    public function mauMuc()
    {
        return self::MAU_MUC[$this->result_level] ?? 'default';
    }

    /** Ma tra cuu chi co gia tri 30 ngay ke tu luc kiem tra. */
    public function conHan()
    {
        return $this->expires_at && $this->expires_at->isFuture();
    }
}
