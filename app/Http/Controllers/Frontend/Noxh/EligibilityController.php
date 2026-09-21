<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\EligibilityAnswer;
use App\Models\EligibilityCheck;
use App\Models\EligibilityOption;
use App\Models\EligibilityQuestion;
use App\Repositories\Noxh\ProjectQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Bo kiem tra dieu kien mua nha o xa hoi.
 *
 * Quy tac cham diem KHONG nam trong lop nay: cau hoi, dap an, diem va ket
 * luan deu doc tu CSDL (quan tri sua duoc). Dieu kien mua NOXH thay doi theo
 * nghi dinh, de trong code thi moi lan doi chinh sach lai phai trien khai lai.
 */
class EligibilityController extends FrontendController
{
    /** Ma tra cuu chi co gia tri 30 ngay. */
    private const SO_NGAY_HIEU_LUC = 30;

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

    /** Bo cau hoi, chia theo bon nhom. */
    public function form()
    {
        $cauHoi = EligibilityQuestion::with('options')
            ->where('publish', 2)
            ->orderBy('order')
            ->get();

        return view('frontend.noxh.check.form', [
            'system' => $this->system,
            'seo' => $this->seo('Kiểm tra điều kiện mua nhà ở xã hội', url('/kiem-tra-dieu-kien/cau-hoi')),
            'nhom' => EligibilityQuestion::NHOM,
            'cauHoi' => $cauHoi,
            'cauHoiTheoNhom' => $cauHoi->groupBy('group'),
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:191',
                'phone' => 'required|string|max:20',
                'traLoi' => 'required|array',
            ],
            [
                'name.required' => 'Bạn chưa nhập họ tên.',
                'phone.required' => 'Bạn chưa nhập số điện thoại để nhận kết quả.',
                'traLoi.required' => 'Bạn chưa trả lời câu hỏi nào.',
            ]
        );

        $cauHoi = EligibilityQuestion::with('options')->where('publish', 2)->orderBy('order')->get();
        $traLoi = (array) $request->input('traLoi');

        $ketQua = $this->chamDiem($cauHoi, $traLoi);

        $luot = DB::transaction(function () use ($request, $ketQua, $cauHoi) {
            $luot = EligibilityCheck::create([
                'code' => $this->sinhMa(),
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'province_code' => $request->input('province_code'),
                'total_questions' => $cauHoi->count(),
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

    /**
     * Cham diem mot luot tra loi.
     *
     * Diem cua moi dap an va trong so cua moi cau deu lay tu CSDL. Phan tram
     * tinh bang diem dat duoc chia cho diem toi da co the dat - khong phai
     * dem so cau dung, vi moi cau co trong so khac nhau.
     */
    private function chamDiem($cauHoi, array $traLoi): array
    {
        $diem = 0;
        $diemToiDa = 0;
        $dat = $chuaRo = $khongDat = $daTraLoi = 0;
        $chiTiet = [];

        foreach ($cauHoi as $ch) {
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
