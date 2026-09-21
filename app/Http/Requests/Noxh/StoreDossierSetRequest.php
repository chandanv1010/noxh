<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreDossierSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'subject_group' => 'nullable|string|max:191',
            'canonical' => 'nullable|string|max:191',
            'description' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Bạn chưa nhập tên bộ hồ sơ.',
        ];
    }
}
