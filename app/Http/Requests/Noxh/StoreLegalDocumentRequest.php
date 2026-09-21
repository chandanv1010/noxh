<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'doc_number' => 'nullable|string|max:191',
            'doc_type' => 'required|string|max:64',
            'issuer' => 'nullable|string|max:191',
            'issued_date' => 'nullable|date',
            'effective_date' => 'nullable|date',
            'summary' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Bạn chưa nhập tên văn bản.',
            'doc_type.required' => 'Bạn chưa nhập loại văn bản.',
        ];
    }
}
