<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvestorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'short_name' => 'nullable|string|max:191',
            'hotline' => 'nullable|string|max:191',
            'email' => 'nullable|string|max:191',
            'website' => 'nullable|string|max:191',
            'address' => 'nullable|string|max:191',
            'description' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Bạn chưa nhập tên chủ đầu tư.',
        ];
    }
}
