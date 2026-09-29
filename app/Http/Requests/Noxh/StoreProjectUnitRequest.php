<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'name' => 'required|string|max:191',
            'image' => 'nullable|string|max:500',
            'area_from' => 'nullable|numeric|min:0',
            // Dien tich "den" phai lon hon hoac bang "tu", neu khong the can ho
            // se in ra mot khoang nguoc ("32 - 25 m2") ma khong ai phat hien.
            'area_to' => 'nullable|numeric|min:0|gte:area_from',
            'price_from' => 'nullable|numeric|min:0',
            'price_to' => 'nullable|numeric|min:0|gte:price_from',
            'price_unit' => 'nullable|string|max:20',
            'bullets' => 'nullable|string|max:2000',
            'url' => 'nullable|string|max:500',
            'publish' => 'nullable|integer',
            'order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Bạn chưa chọn dự án.',
            'product_id.exists' => 'Dự án được chọn không tồn tại.',
            'name.required' => 'Bạn chưa nhập tên loại căn hộ.',
            'area_to.gte' => 'Diện tích "đến" phải lớn hơn hoặc bằng "từ".',
            'price_to.gte' => 'Giá "đến" phải lớn hơn hoặc bằng "từ".',
        ];
    }
}
