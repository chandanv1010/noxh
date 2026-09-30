<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\EligibilityAnswer;
use App\Models\EligibilityCheck;
use App\Models\EligibilityQuestion;
use App\Repositories\Noxh\ProjectQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Bo kiem tra dieu kien mua nha o xa hoi - dang WIZARD nhieu buoc
 * (ban ve noxh_image/w-1.jpg).
 *
 * Moi cau hoi la MOT buoc, mot man hinh. Thanh buoc o dau trang, so cau
 * ("Cau 1/8"), ten tung buoc, cau hoi, dap an va hinh cua tung dap an deu doc
 * tu CSDL: them hay bot mot dap an la viec cua quan tri, khong phai viec cua
 * ban cap nhat ma nguon.
 *
 * Cau tra loi giu trong PHIEN cho toi khi nguoi dung bam nut o buoc cuoi.
 * Khong ghi CSDL tung buoc: bo do giua chung la chuyen thuong, ghi som thi
 * bang eligibility_checks day nhung luot do dang.
 */
class EligibilityController extends FrontendController
{
    /** Ma tra cuu chi co gia tri 30 ngay. */
    private const SO_NGAY_HIEU_LUC = 30;

    /** Khoa giu cau tra loi dang lam do trong phien. */
    private const KHOA_PHIEN = 'noxh_check_tra_loi';

    protected $projectQuery;

    public function __construct(ProjectQuery $projectQuery)
    {
        $this->projectQuery = $projectQuery;
        parent::__construct();
    }

    /** Man hinh dong y chinh sach truoc khi hoi. */
    public function index()
    {
        return view('frontend.noxh.check.consent', [
            'system' => $this->system,
            'seo' => $this->seo('Kiểm tra điều kiện mua nhà ở xã hội', url('/kiem-tra-dieu-kien')),
        ]);
    }

    /** Mot buoc cua wizard. Khong truyen so buoc thi ve buoc 1. */
    public function form(Request $request, $buoc = null)
    {
        $cauHoi = $this->cauHoi();

        if ($cauHoi->isEmpty()) {
            abort(404);
        }

        $soBuoc = $this->chuanHoaBuoc($buoc, $cauHoi->count());

        // Nhay coc sang giua chung (go thang duong dan) thi lui ve buoc dau
        // con thieu - cham diem can du cau, de nguoi dung di tiep roi bao
        // thieu o buoc cuoi thi ho phai bam nguoc lai ca bo.
        $daCo = $this->traLoiTrongPhien($request);
        $duongDi = $this->duongDi($cauHoi, $daCo);
        $dauTien = $this->buocConThieu($cauHoi, $daCo, $duongDi);

        // Nhay coc sang giua chung (go thang duong dan), hoac go so cua mot
        // buoc da bi bo qua, thi lui ve buoc dau con thieu.
        if ($soBuoc > $dauTien || !in_array($soBuoc, $duongDi, true)) {
            return redirect()->route('noxh.check.form', $dauTien);
        }

        $cau = $cauHoi[$soBuoc - 1];

        return view('frontend.noxh.check.wizard', [
            'system' => $this->system,
            'seo' => $this->seo('Kiểm tra điều kiện mua nhà ở xã hội', url('/kiem-tra-dieu-kien/cau-hoi/' . $soBuoc)),
            'cauHoi' => $cauHoi,
            'duongDi' => $duongDi,
            'buoc' => $soBuoc,
            'cau' => $cau,
            'cuoiCung' => $cau->laBuocNhapTin() || $soBuoc === $cauHoi->count(),
            'daChon' => $daCo[$cau->id] ?? null,
            'traLoi' => $daCo,
        ]);
    }

    /** Ghi cau tra loi cua mot buoc roi sang buoc ke tiep. */
    public function step(Request $request, $buoc)
    {
        $cauHoi = $this->cauHoi();
        $soBuoc = $this->chuanHoaBuoc($buoc, $cauHoi->count());
        $cau = $cauHoi[$soBuoc - 1];

        $lui = $request->input('huong') === 'lui';

        // Bam "Quay lai" thi khong bat tra loi - nguoi dung dang di nguoc.
        if (!$lui && !$this->ghiTraLoi($request, $cau)) {
            return back()->withInput()->with(
                'nx_error',
                $this->chu('wizard_require_text', 'Bạn chưa chọn câu trả lời.')
            );
        }

        $duongDi = $this->duongDi($cauHoi, $this->traLoiTrongPhien($request));
        $viTri = array_search($soBuoc, $duongDi, true);

        if ($lui) {
            $truoc = $viTri > 0 ? $duongDi[$viTri - 1] : null;

            return $truoc === null
                ? redirect()->route('noxh.check.index')
                : redirect()->route('noxh.check.form', $truoc);
        }

        $ke = ($viTri !== false && isset($duongDi[$viTri + 1])) ? $duongDi[$viTri + 1] : $soBuoc;

        return redirect()->route('noxh.check.form', $ke);
    }

    /** Buoc cuoi: ghi not cau tra loi, lay ho ten - so dien thoai roi cham diem. */
    public function submit(Request $request)
    {
        $cauHoi = $this->cauHoi();

        if ($cauHoi->isEmpty()) {
            abort(404);
        }

        $request->validate(
            [
                'name' => 'required|string|max:191',
                'phone' => 'required|string|max:20',
            ],
            [
                'name.required' => 'Bạn chưa nhập họ tên.',
                'phone.required' => 'Bạn chưa nhập số điện thoại để nhận kết quả.',
            ]
        );

        $cuoi = $cauHoi->last();

        // Buoc cuoi trong ban ve w-5 chi xin ho ten - so dien thoai, khong
        // hoi gi nua; chi cau hoi that moi phai ghi cau tra loi.
        if (!$cuoi->laBuocNhapTin() && !$this->ghiTraLoi($request, $cuoi)) {
            return back()->withInput()->with(
                'nx_error',
                $this->chu('wizard_require_text', 'Bạn chưa chọn câu trả lời.')
            );
        }

        $traLoi = $this->traLoiTrongPhien($request);
        $ketQua = $this->chamDiem($cauHoi, $traLoi, $this->duongDi($cauHoi, $traLoi));

        $luot = DB::transaction(function () use ($request, $ketQua, $cauHoi) {
            $luot = EligibilityCheck::create([
                'code' => $this->sinhMa(),
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'province_code' => $request->input('province_code'),
                'total_questions' => $ketQua['soCau'],
                'answered' => $ketQua['daTraLoi'],
                'passed' => $ketQua['dat'],
                'unclear' => $ketQua['chuaRo'],
                'failed' => $ketQua['khongDat'],
                'score_percent' => $ketQua['phanTram'],
                'result_level' => $ketQua['muc'],
                'expires_at' => now()->addDays(self::SO_NGAY_HIEU_LUC),
                'consent' => true,
                'ip' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 480, ''),
            ]);

            foreach ($ketQua['chiTiet'] as $dong) {
                EligibilityAnswer::create([
                    'eligibility_check_id' => $luot->id,
                    'eligibility_question_id' => $dong['cauHoiId'],
                    'eligibility_option_id' => $dong['dapAnId'],
                    'answer_value' => $dong['giaTri'],
                    'verdict' => $dong['ketLuan'],
                    'score' => $dong['diem'],
                ]);
            }

            return $luot;
        });

        $request->session()->forget(self::KHOA_PHIEN);

        return redirect()->route('noxh.check.result', $luot->code);
    }

    public function result(string $code)
    {
        $luot = EligibilityCheck::with(['answers.question', 'answers.option'])
            ->where('code', $code)
            ->first();

        if (!$luot) {
            abort(404);
        }

        return view('frontend.noxh.check.result', [
            'system' => $this->system,
            'seo' => $this->seo('Kết quả kiểm tra điều kiện', url('/kiem-tra-dieu-kien/ket-qua/' . $code)),
            'luot' => $luot,
            'goiY' => $this->projectQuery->noiBat(3),
        ]);
    }

    public function lookup(Request $request)
    {
        $request->validate(['code' => 'required|string|max:40'], [
            'code.required' => 'Bạn chưa nhập mã tra cứu.',
        ]);

        $luot = EligibilityCheck::where('code', trim($request->input('code')))->first();

        if (!$luot) {
            return back()->with('nx_error', 'Không tìm thấy kết quả nào với mã này.');
        }

        return redirect()->route('noxh.check.result', $luot->code);
    }

    // -------------------------------------------------------------------------

    /** Bo cau hoi dang phat hanh, dung thu tu cua thanh buoc. */
    private function cauHoi()
    {
        return EligibilityQuestion::with(['options', 'optionGroups.options', 'panels'])
            ->where('publish', 2)
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->values();
    }

    private function chuanHoaBuoc($buoc, int $tong): int
    {
        $buoc = (int) $buoc;

        return max(1, min($tong, $buoc ?: 1));
    }

    /** @return array<int,string> cauHoiId => gia tri da chon */
    private function traLoiTrongPhien(Request $request): array
    {
        $da = $request->session()->get(self::KHOA_PHIEN, []);

        return is_array($da) ? $da : [];
    }

    /**
     * Cac buoc nguoi dung THAT SU phai di qua, tinh theo cau tra loi hien co.
     *
     * Co dap an mang co "dung som" (cot stop_flow): chon no la biet chac
     * khong du dieu kien, hoi tiep vo nghia - vi du o buoc Chinh sach chon
     * "Da tung duoc ho tro" thi bo qua buoc Nha o, di thang sang buoc nhap
     * thong tin de xem ket qua.
     *
     * Tinh lai moi lan goi chu khong nho vao phien: nguoi dung quay lai doi
     * dap an la duong di phai doi theo.
     *
     * @return array<int,int> danh sach so buoc, tang dan
     */
    private function duongDi($cauHoi, array $daCo): array
    {
        $di = [];
        $cuoi = $cauHoi->count();

        foreach ($cauHoi as $i => $cau) {
            $di[] = $i + 1;

            if ($cau->laBuocNhapTin()) {
                break;
            }

            $gia = $daCo[$cau->id] ?? null;

            if ($gia === null || $gia === '') {
                break;
            }

            $dapAn = $cau->options->firstWhere('value', $gia);

            if ($dapAn && $dapAn->stop_flow) {
                if (end($di) !== $cuoi) {
                    $di[] = $cuoi;
                }
                break;
            }
        }

        return $di;
    }

    /**
     * Buoc dau tien CHUA co cau tra loi (hoac buoc cuoi neu da tra loi het).
     *
     * Chi xet nhung buoc nam tren duong di; buoc bi bo qua khong tinh la
     * thieu. Buoc nhap thong tin khong co cau tra loi nen luon dung o do.
     */
    private function buocConThieu($cauHoi, array $daCo, array $duongDi): int
    {
        foreach ($duongDi as $so) {
            $cau = $cauHoi[$so - 1];

            if ($cau->laBuocNhapTin()) {
                return $so;
            }

            if (!isset($daCo[$cau->id]) || $daCo[$cau->id] === '') {
                return $so;
            }
        }

        return (int) (end($duongDi) ?: 1);
    }

    /**
     * Ghi cau tra loi cua mot cau vao phien.
     *
     * @return bool false khi cau bat buoc ma nguoi dung chua chon gi
     */
    private function ghiTraLoi(Request $request, $cau): bool
    {
        $gia = $request->input('traLoi');
        $gia = is_string($gia) || is_numeric($gia) ? trim((string) $gia) : '';

        // Cau co dap an dinh san thi chi nhan dung mot trong nhung gia tri do:
        // nguoi dung sua the HTML khong the nhet gia tri la vao bang diem.
        if ($gia !== '' && $cau->options->count() && !$cau->options->contains('value', $gia)) {
            $gia = '';
        }

        if ($gia === '') {
            if ($cau->required) {
                return false;
            }

            $this->quen($request, $cau->id);

            return true;
        }

        $da = $this->traLoiTrongPhien($request);
        $da[$cau->id] = $gia;
        $request->session()->put(self::KHOA_PHIEN, $da);

        return true;
    }

    private function quen(Request $request, int $cauId): void
    {
        $da = $this->traLoiTrongPhien($request);
        unset($da[$cauId]);
        $request->session()->put(self::KHOA_PHIEN, $da);
    }

    /** Mot o chu cua trang, lay tu bang introduces. */
    private function chu(string $khoa, string $du): string
    {
        $o = \App\Http\ViewComposers\NoxhComposer::intro();
        $gia = trim((string) ($o[$khoa] ?? ''));

        return $gia !== '' ? $gia : $du;
    }

    /**
     * Cham diem mot luot tra loi.
     *
     * Diem cua moi dap an va trong so cua moi cau deu lay tu CSDL. Phan tram
     * tinh bang diem dat duoc chia cho diem toi da co the dat - khong phai
     * dem so cau dung, vi moi cau co trong so khac nhau.
     */
    private function chamDiem($cauHoi, array $traLoi, array $duongDi = []): array
    {
        $diem = 0;
        $diemToiDa = 0;
        $dat = $chuaRo = $khongDat = $daTraLoi = 0;
        $soCau = 0;
        $chiTiet = [];

        foreach ($cauHoi as $i => $ch) {
            // Buoc nhap thong tin khong phai cau hoi; buoc bi bo qua thi
            // nguoi dung chua bao gio nhin thay - tinh diem ca hai la ep ho
            // mat diem vi mot cau khong duoc hoi.
            if ($ch->laBuocNhapTin()) {
                continue;
            }

            if ($duongDi && !in_array($i + 1, $duongDi, true)) {
                continue;
            }

            $soCau++;
            $trongSo = max(1, (int) $ch->weight);

            // Diem toi da cua mot cau la diem cao nhat trong cac dap an cua no.
            $diemCaoNhat = (int) ($ch->options->max('score') ?? 0);
            $diemToiDa += $diemCaoNhat * $trongSo;

            $giaTri = $traLoi[$ch->id] ?? null;

            if ($giaTri === null || $giaTri === '') {
                $chuaRo++;
                $chiTiet[] = [
                    'cauHoiId' => $ch->id, 'dapAnId' => null, 'giaTri' => null,
                    'ketLuan' => 'unclear', 'diem' => 0,
                ];
                continue;
            }

            $daTraLoi++;
            $dapAn = $ch->options->firstWhere('value', $giaTri);

            if (!$dapAn) {
                // Cau nhap so tu do (khong co dap an dinh san) thi coi la can
                // kiem tra them chu khong ket luan vong.
                $chuaRo++;
                $chiTiet[] = [
                    'cauHoiId' => $ch->id, 'dapAnId' => null, 'giaTri' => (string) $giaTri,
                    'ketLuan' => 'unclear', 'diem' => 0,
                ];
                continue;
            }

            $diemCau = (int) $dapAn->score * $trongSo;
            $diem += $diemCau;

            match ($dapAn->verdict) {
                'pass' => $dat++,
                'fail' => $khongDat++,
                default => $chuaRo++,
            };

            $chiTiet[] = [
                'cauHoiId' => $ch->id,
                'dapAnId' => $dapAn->id,
                'giaTri' => $dapAn->value,
                'ketLuan' => $dapAn->verdict,
                'diem' => $diemCau,
            ];
        }

        $phanTram = $diemToiDa > 0 ? (int) round($diem / $diemToiDa * 100) : 0;

        return [
            'soCau' => $soCau,
            'phanTram' => $phanTram,
            'muc' => $khongDat > 0 ? 'low' : ($phanTram >= 70 ? 'high' : ($phanTram >= 40 ? 'medium' : 'low')),
            'dat' => $dat,
            'chuaRo' => $chuaRo,
            'khongDat' => $khongDat,
            'daTraLoi' => $daTraLoi,
            'chiTiet' => $chiTiet,
        ];
    }

    /** Ma dang NOXH-260921-1530, them duoi neu trung. */
    private function sinhMa(): string
    {
        $goc = 'NOXH-' . now()->format('ymd-Hi');
        $ma = $goc;
        $lan = 0;

        while (EligibilityCheck::where('code', $ma)->exists()) {
            $ma = $goc . '-' . (++$lan);
        }

        return $ma;
    }

    private function seo(string $tieuDe, string $canonical): array
    {
        return [
            'meta_title' => $tieuDe . ' - NOXH.vn',
            'meta_description' => $this->system['seo_meta_description'] ?? '',
            'meta_image' => $this->system['seo_meta_images'] ?? '',
            'canonical' => $canonical,
        ];
    }
}
