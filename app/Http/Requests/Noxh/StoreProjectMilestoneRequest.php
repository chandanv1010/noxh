<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectMilestoneRequest extends FormRequest
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
            'date_label' => 'nullable|string|max:191',
            'sort_date' => 'nullable|date',
            'status' => 'required|string|max:32',
            'order' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Bạn chưa nhập thuộc dự án.',
            'product_id.exists' => 'Dự án được chọn không tồn tại.',
            'title.required' => 'Bạn chưa nhập tên mốc.',
            'status.required' => 'Bạn chưa nhập trạng thái.',
        ];
    }
}
