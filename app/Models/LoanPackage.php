<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\QueryScopes;

/**
 * Goi vay ngan hang cho cong cu "Tinh khoan vay".
 *
 * Lai suat uu dai chi ap dung vai nam dau roi tha noi, nen phai tach
 * preferential_rate + preferential_months khoi standard_rate - gom thanh mot
 * con so la tinh sai tien tra hang thang tu nam thu ba tro di.
 */
class LoanPackage extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $table = 'loan_packages';

    protected $fillable = [
        'bank_name', 'bank_logo', 'package_name',
        'preferential_rate', 'preferential_months', 'standard_rate',
        'max_loan_ratio', 'max_term_years', 'prepayment_fee',
        'conditions', 'note', 'hotline',
        'is_featured', 'publish', 'order', 'effective_from',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'is_featured' => 'boolean',
    ];

    /** Bang nay khong co cot `name` ma scope keyword mac dinh lai do cot do. */
    public function getNameAttribute()
    {
        return trim($this->bank_name . ' ' . $this->package_name);
    }
}
