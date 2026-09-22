<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\Product;
use App\Models\User;
use App\Services\Noxh\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Nhan moi form de lai thong tin tren website: dang ky nhan tin o trang chu,
 * form quan tam du an, dang ky tu van, va nut "Lien he" o tung nhan vien
 * kinh doanh.
 *
 * Tat ca deu luu vao bang contacts - cot `source` cho biet form nao gui len,
 * cot `assigned_user_id` cho biet khach bam vao nhan vien nao.
 */
class LeadController extends FrontendController
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
        parent::__construct();
    }

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
                'province_code' => 'nullable|string|max:20',
            ],
            $this->loi()
        );

        $luu = $this->luu($duLieu);

        if (!$luu) {
            return back()
                ->withInput()
                ->with('nx_error', 'Không gửi được thông tin. Bạn vui lòng thử lại hoặc gọi trực tiếp hotline.');
        }

        $this->baoTelegram('Khách để lại thông tin', $duLieu);

        return back()->with('nx_success', 'Đã nhận thông tin của bạn. Chuyên viên sẽ liên hệ trong thời gian sớm nhất.');
    }

    /**
     * Nut "Lien he" o tung nhan vien kinh doanh.
     *
     * Khac form chung o cho: ban ghi duoc gan thang cho nhan vien khach bam
     * vao (assigned_user_id), va tin Telegram noi ro ten nguoi do de quan tri
     * biet ma chia viec.
     */
    public function advisor(Request $request)
    {
        $duLieu = $request->validate(
            [
                'name' => 'required|string|max:191',
                'phone' => 'required|string|max:20',
                'message' => 'nullable|string|max:2000',
                'nhan_vien_id' => 'required|integer',
                'product_id' => 'nullable|integer',
            ],
            $this->loi() + ['nhan_vien_id.required' => 'Thiếu thông tin nhân viên tư vấn.']
        );

        // Chi nhan nguoi thuc su la nhan vien kinh doanh dang hoat dong: id gui
        // len tu trinh duyet nen khong tin duoc.
        $nhanVien = User::whereHas('user_catalogues', fn ($q) => $q->where('is_sale', 1))
            ->where('publish', 2)
            ->find($duLieu['nhan_vien_id']);

        if (!$nhanVien) {
            return $this->traLoi($request, false, 'Nhân viên tư vấn này hiện không nhận yêu cầu. Bạn thử chọn người khác giúp tôi.');
        }

        // validate() chi tra ve nhung khoa CO gui len. Popup o trang chu khong
        // gan voi du an nao nen khong co product_id - doc thang la bao loi.
        $duAnId = $duLieu['product_id'] ?? null;

        $duAn = $duAnId
            ? Product::query()
                ->join('product_language as pl', function ($j) {
                    $j->on('pl.product_id', '=', 'products.id')
                      ->where('pl.language_id', '=', $this->language);
                })
                ->where('products.id', $duAnId)
                ->value('pl.name')
            : null;

        $luu = $this->luu([
            'name' => $duLieu['name'],
            'phone' => $duLieu['phone'],
            'message' => $duLieu['message'] ?? null,
            'source' => 'advisor',
            'product_id' => $duAnId,
            'interest' => $duAn,
        ], $nhanVien->id);

        if (!$luu) {
            return $this->traLoi($request, false, 'Không gửi được thông tin. Bạn vui lòng thử lại hoặc gọi trực tiếp hotline.');
        }

        $this->telegram->baoLienHe('Khách xin tư vấn qua nhân viên', [
            'Họ tên' => $duLieu['name'],
            'Điện thoại' => $duLieu['phone'],
            'Nhân viên phụ trách' => $nhanVien->name . ($nhanVien->phone ? ' - ' . $nhanVien->phone : ''),
            'Dự án' => $duAn,
            'Ghi chú' => $duLieu['message'] ?? null,
        ]);

        return $this->traLoi(
            $request,
            true,
            'Đã gửi thông tin cho ' . $nhanVien->name . '. Chuyên viên sẽ liên hệ với bạn sớm nhất.'
        );
    }

    private function luu(array $duLieu, ?int $nhanVienId = null): bool
    {
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
                'province_code' => $duLieu['province_code'] ?? null,
                'assigned_user_id' => $nhanVienId,
                'status' => 'new',
                'publish' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Luu thong tin lien he that bai: ' . $e->getMessage());

            return false;
        }
    }

    private function baoTelegram(string $tieuDe, array $duLieu): void
    {
        $this->telegram->baoLienHe($tieuDe, [
            'Họ tên' => $duLieu['name'],
            'Điện thoại' => $duLieu['phone'],
            'Email' => $duLieu['email'] ?? null,
            'Quan tâm' => $duLieu['interest'] ?? null,
            'Từ trang' => $duLieu['source'] ?? 'website',
            'Lời nhắn' => $duLieu['message'] ?? null,
        ]);
    }

    /**
     * Popup gui bang fetch nen can JSON; form thuong thi quay lai trang cu.
     */
    private function traLoi(Request $request, bool $xong, string $loiNhan)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['xong' => $xong, 'loiNhan' => $loiNhan], $xong ? 200 : 422);
        }

        return back()->with($xong ? 'nx_success' : 'nx_error', $loiNhan);
    }

    private function loi(): array
    {
        return [
            'name.required' => 'Bạn chưa nhập họ tên.',
            'phone.required' => 'Bạn chưa nhập số điện thoại.',
            'phone.max' => 'Số điện thoại không hợp lệ.',
            'email.email' => 'Email không hợp lệ.',
        ];
    }
}
