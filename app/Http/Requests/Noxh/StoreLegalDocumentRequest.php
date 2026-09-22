<?php

namespace App\Http\Requests\Noxh;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'doc_number' => 'nullable|string|max:191',
            'doc_type' => 'required|string|max:64',
            'issuer' => 'nullable|string|max:191',
            'issued_date' => 'nullable|date',
            'effective_date' => 'nullable|date',
            'summary' => 'nullable|string|max:20000',

            // Chi nhan cac dinh dang tai lieu thuong gap. Chan theo DUOI file
            // chu khong theo kieu MIME trinh duyet khai: trinh duyet khai kieu
            // cho .docx khong dong nhat giua Windows va macOS.
            'tep_tai_len' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,rtf,txt,csv,zip,rar',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Bạn chưa nhập tên văn bản.',
            'doc_type.required' => 'Bạn chưa nhập loại văn bản.',
            'tep_tai_len.max' => 'File không được lớn hơn 20MB.',
            'tep_tai_len.mimes' => 'Chỉ nhận file PDF, Word, Excel, PowerPoint, văn bản thuần, CSV hoặc file nén.',
        ];
    }
}
