<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreEligibilityOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eligibility_question_id' => 'required|integer|exists:eligibility_questions,id',
            'label' => 'required|string|max:191',
            'value' => 'required|string|max:191',
            'verdict' => 'required|string|max:64',
            'score' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:20000',
        ];
    }

    public function messages(): array
    {
        return [
            'eligibility_question_id.required' => 'Bạn chưa nhập thuộc câu hỏi.',
            'label.required' => 'Bạn chưa nhập nội dung đáp án.',
            'value.required' => 'Bạn chưa nhập giá trị lưu.',
            'verdict.required' => 'Bạn chưa nhập kết luận.',
        ];
    }
}
