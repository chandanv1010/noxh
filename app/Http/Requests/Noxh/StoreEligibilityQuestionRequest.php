<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreEligibilityQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string|max:20000',
            'group' => 'required|string|max:64',
            'input_type' => 'required|string|max:64',
            'weight' => 'nullable|integer|min:0',
            'criteria_label' => 'nullable|string|max:191',
            'hint' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Bạn chưa nhập nội dung câu hỏi.',
            'group.required' => 'Bạn chưa nhập nhóm câu hỏi.',
            'input_type.required' => 'Bạn chưa nhập kiểu trả lời.',
        ];
    }
}
