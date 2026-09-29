<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectHighlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'group' => 'required|string|in:price,amenity',
            'icon' => 'nullable|string|max:50',
            // Hai dong nay nam trong mot o hep chi rong bang 1/4 the gia nen
            // phai ngan, gioi han 40 ky tu la vua mot dong.
            'title' => 'required|string|max:40',
            'subtitle' => 'nullable|string|max:40',
            'order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Bạn chưa chọn dự án.',
            'product_id.exists' => 'Dự án được chọn không tồn tại.',
            'title.required' => 'Bạn chưa nhập dòng trên của ô điểm nhấn.',
            'title.max' => 'Dòng trên quá dài, ô điểm nhấn chỉ vừa khoảng 40 ký tự.',
            'subtitle.max' => 'Dòng dưới quá dài, ô điểm nhấn chỉ vừa khoảng 40 ký tự.',
        ];
    }
}
