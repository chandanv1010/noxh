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
            'group' => 'required|string|in:legal,doc',
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
            'group.required' => 'Bạn chưa chọn giấy tờ này thuộc khối nào.',
            'group.in' => 'Khối được chọn không hợp lệ.',
            'title.required' => 'Bạn chưa nhập tên hồ sơ.',
        ];
    }
}
