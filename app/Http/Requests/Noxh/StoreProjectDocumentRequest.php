<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'title' => 'required|string|max:191',
            'doc_number' => 'nullable|string|max:191',
            'issued_date' => 'nullable|date',
            'issuer' => 'nullable|string|max:191',
            'order' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Bạn chưa nhập thuộc dự án.',
            'product_id.exists' => 'Dự án được chọn không tồn tại.',
            'title.required' => 'Bạn chưa nhập tên hồ sơ.',
        ];
    }
}
