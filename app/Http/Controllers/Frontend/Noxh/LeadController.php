<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Nhan moi form de lai thong tin tren website: dang ky nhan tin o trang chu,
 * form quan tam du an, dang ky tu van.
 *
 * Tat ca deu luu vao bang contacts - cot `source` cho biet form nao gui len,
 * de ben kinh doanh biet khach den tu dau.
 */
class LeadController extends FrontendController
{
    public function store(Request $request)
    {
        $duLieu = $request->validate(
            [
                'name' => 'required|string|max:191',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:191',
                'interest' => 'nullable|string|max:191',
                'message' => 'nullable|string|max:5000',
                'source' => 'nullable|string|max:40',
                'product_id' => 'nullable|integer',
            ],
            [
                'name.required' => 'Bạn chưa nhập họ tên.',
                'phone.required' => 'Bạn chưa nhập số điện thoại.',
                'phone.max' => 'Số điện thoại không hợp lệ.',
                'email.email' => 'Email không hợp lệ.',
            ]
        );

        try {
            DB::table('contacts')->insert([
                'name' => $duLieu['name'],
                'phone' => $duLieu['phone'],
                // Hai cot nay khai bao NOT NULL tu ban clone ban dau nhung
                // khong co gia tri mac dinh - de null la MySQL tu choi ca dong.
                'email' => $duLieu['email'] ?? '',
                'gender' => 0,
                'message' => $duLieu['message'] ?? null,
                'interest' => $duLieu['interest'] ?? null,
                'source' => $duLieu['source'] ?? 'website',
                'product_id' => $duLieu['product_id'] ?? null,
                'status' => 'new',
                'publish' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Luu thong tin lien he that bai: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('nx_error', 'Không gửi được thông tin. Bạn vui lòng thử lại hoặc gọi trực tiếp hotline.');
        }

        return back()->with('nx_success', 'Đã nhận thông tin của bạn. Chuyên viên sẽ liên hệ trong thời gian sớm nhất.');
    }
}
