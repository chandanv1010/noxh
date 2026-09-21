<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'order' => 'nullable|integer|min:0',
            'question' => 'required|string|max:20000',
            'answer' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Bạn chưa nhập thuộc dự án.',
            'product_id.exists' => 'Dự án được chọn không tồn tại.',
            'question.required' => 'Bạn chưa nhập câu hỏi.',
        ];
    }
}
