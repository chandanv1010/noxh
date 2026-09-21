<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'title' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:191',
            'zalo' => 'nullable|string|max:191',
            'email' => 'nullable|string|max:191',
            'description' => 'nullable|string|max:20000',
            'commitments' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Bạn chưa nhập họ và tên.',
        ];
    }
}
