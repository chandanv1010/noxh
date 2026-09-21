<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:191',
            'package_name' => 'nullable|string|max:191',
            'preferential_rate' => 'nullable|numeric|min:0',
            'preferential_months' => 'nullable|integer|min:0',
            'standard_rate' => 'nullable|numeric|min:0',
            'max_loan_ratio' => 'nullable|numeric|min:0',
            'max_term_years' => 'nullable|integer|min:0',
            'prepayment_fee' => 'nullable|numeric|min:0',
            'hotline' => 'nullable|string|max:191',
            'effective_from' => 'nullable|date',
            'conditions' => 'nullable|string|max:20000',
            'note' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_name.required' => 'Bạn chưa nhập tên ngân hàng.',
        ];
    }
}
