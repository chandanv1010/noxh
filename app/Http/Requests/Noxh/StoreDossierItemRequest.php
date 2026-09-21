<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreDossierItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dossier_set_id' => 'required|integer|exists:dossier_sets,id',
            'title' => 'required|string|max:191',
            'issued_by' => 'nullable|string|max:191',
            'copies' => 'nullable|integer|min:0',
            'template_name' => 'nullable|string|max:191',
            'description' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'dossier_set_id.required' => 'Bạn chưa nhập thuộc bộ hồ sơ.',
            'title.required' => 'Bạn chưa nhập tên giấy tờ.',
        ];
    }
}
