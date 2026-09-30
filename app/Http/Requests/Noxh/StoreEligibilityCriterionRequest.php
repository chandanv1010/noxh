<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreEligibilityCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => 'required|string|max:191',
            'source' => 'required|string|max:20',
            'eligibility_question_id' => 'nullable|integer|exists:eligibility_questions,id',
            'pass_text' => 'nullable|string|max:500',
            'unclear_text' => 'nullable|string|max:500',
            'fail_text' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
            'publish' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Bạn chưa nhập tên tiêu chí.',
            'source.required' => 'Bạn chưa chọn nguồn kết luận.',
        ];
    }
}
