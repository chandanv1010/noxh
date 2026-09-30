<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreEligibilityPanelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eligibility_question_id' => 'required|integer|exists:eligibility_questions,id',
            'heading' => 'nullable|string|max:191',
            'body' => 'nullable|string|max:5000',
            'bullets' => 'nullable|string|max:5000',
            'image' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:60',
            'tone' => 'nullable|string|max:20',
            'order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'eligibility_question_id.required' => 'Bạn chưa chọn bước.',
        ];
    }
}
