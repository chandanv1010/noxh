<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Http\ViewComposers\NoxhComposer;
use App\Models\DossierItem;
use App\Models\DossierSet;
use Illuminate\Http\Request;

/**
 * Ba man hinh HO SO dung chung mot bo du lieu, chi khac cach hien:
 *   can-chuan-bi : danh sach giay to theo tung nhom doi tuong
 *   mau-don      : loc ra nhung giay to co file mau
 *   checklist    : chinh danh sach do nhung tich chon duoc de theo doi
 */
class DossierController extends FrontendController
{
    public function index(Request $request)
    {
        return $this->hien($request, 'prepare', 'Hồ sơ cần chuẩn bị', url('/ho-so/can-chuan-bi'));
    }

    public function templates(Request $request)
    {
        return $this->hien($request, 'templates', 'Mẫu đơn nhà ở xã hội', url('/ho-so/mau-don'));
    }

    public function checklist(Request $request)
    {
        return $this->hien($request, 'checklist', 'Checklist hồ sơ nhà ở xã hội', url('/ho-so/checklist'));
    }

    /**
     * "Download trọn bộ" — tải ca bo ho so cua mot nhom doi tuong trong MOT lan.
     *
     * Trong goi ZIP luon co:
     *   - Huong-dan-ho-so.html: ban in liet ke day du giay to (ten, ghi chu, noi
     *     cap, so ban, giay to nao bat buoc). Mo duoc bang trinh duyet hoac Word.
     *   - Cac tep mau don that, neu giay to do co dinh kem.
     *
     * Vi sao KHONG dung PDF: du an khong co thu vien sinh PDF nao (xem
     * composer.json). Them mot thu vien chi de in mot danh sach la khong dang, ma
     * tep HTML lai mo duoc o moi may va in ra giay duoc.
     *
     * Vi sao luon nhet tep huong dan: hien tai gan nhu chua co tep mau nao duoc
     * tai len, nen neu chi nhet tep mau thi nguoi dung bam xong se nhan mot goi
     * ZIP RONG - ho se tuong nut hong.
     */
    public function tronBo(string $bo)
    {
        $boHoSo = DossierSet::with(['items' => fn ($q) => $q->where('publish', 2)->orderBy('order')])
            ->where('publish', 2)
            ->where(function ($q) use ($bo) {
                $q->where('canonical', $bo);
                if (ctype_digit($bo)) {
                    $q->orWhere('id', (int) $bo);
                }
            })
            ->firstOrFail();

        $huongDan = view('frontend.noxh.dossier.tron-bo', [
            'bo' => $boHoSo,
            'system' => $this->system,
        ])->render();

        $duongTam = tempnam(sys_get_temp_dir(), 'nxh') . '.zip';

        $zip = new \ZipArchive();

        if ($zip->open($duongTam, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Không tạo được tệp nén.');
        }

        $zip->addFromString('Huong-dan-ho-so.html', $huongDan);

        foreach ($boHoSo->items as $thuTu => $gt) {
            $this->nhetTepMau($zip, $gt, $thuTu + 1);
        }

        $zip->close();

        // Dem luot tai: giay to nao nam trong goi thi tinh cho giay to do.
        DossierItem::whereIn('id', $boHoSo->items->pluck('id'))->increment('download_count');

        $ten = 'ho-so-' . ($boHoSo->canonical ?: $boHoSo->id) . '.zip';

        return response()->download($duongTam, $ten, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Nhet tep mau cua mot giay to vao goi ZIP, neu tep that su nam tren may.
     *
     * Duong dan trong CSDL co the la duong dan noi bo (/uploads/...) hoac dia chi
     * mang day du. Chi nhet duoc loai thu nhat; loai thu hai thi de nguyen trong
     * tep huong dan, khong tai ve (tai trong luc nguoi dung dang cho la khong nen).
     */
    private function nhetTepMau(\ZipArchive $zip, DossierItem $gt, int $thuTu): void
    {
        $duong = trim((string) $gt->template_file);

        if ($duong === '' || preg_match('#^https?://#i', $duong)) {
            return;
        }

        $tep = public_path(ltrim($duong, '/'));

        if (!is_file($tep)) {
            return;
        }

        $duoi = pathinfo($tep, PATHINFO_EXTENSION);
        $ten = sprintf('%02d-%s%s', $thuTu, \Illuminate\Support\Str::slug($gt->title), $duoi ? '.' . $duoi : '');

        $zip->addFile($tep, 'Mau-don/' . $ten);
    }

    private function hien(Request $request, string $kieu, string $tieuDe, string $canonical)
    {
        $boChon = $request->input('bo');

        $boHoSo = DossierSet::with(['items' => function ($q) use ($kieu) {
            $q->where('publish', 2)->orderBy('order');

            // Man hinh "Mau don" chi quan tam giay to co file mau - loc ngay
            // o day thay vi loc trong view de khong hien bo ho so rong.
            if ($kieu === 'templates') {
                $q->whereNotNull('template_file')->where('template_file', '!=', '');
            }
        }])
            ->where('publish', 2)
            ->orderBy('order')
            ->get()
            ->filter(fn($bo) => $bo->items->count());

        $nhomDangXem = null;

        if ($boChon) {
            $loc = $boHoSo->firstWhere('canonical', $boChon) ?? $boHoSo->firstWhere('id', (int) $boChon);
            if ($loc) {
                $nhomDangXem = $loc;
                $boHoSo = collect([$loc]);
            }
        }

        // The <title> va meta description la thu hien ra khi dan lien ket sang
        // Zalo/Telegram/Facebook, hoac khi re chuot vao lien ket. Truoc day ca ba
        // man hinh Ho so deu dung chung mot cau mo ta cua toan website, nen nguoi
        // doc khong biet trang nay co gi - va khi da loc theo mot nhom doi tuong
        // thi cang khong biet minh dang xem nhom nao.
        $intro = NoxhComposer::intro();

        if ($nhomDangXem) {
            // Vai nhom da tu bat dau bang "Ho so cho..." - ghep them tien to nua
            // thi tieu de thanh "Ho so can chuan bi - Ho so cho cong nhan...",
            // doc rat loan. Nhom nao chua co thi moi ghep.
            $tenNhom = trim((string) $nhomDangXem->name);

            $tieuDeSeo = preg_match('/^hồ sơ/iu', $tenNhom)
                ? $tenNhom
                : $tieuDe . ' - ' . $tenNhom;

            $moTaSeo = sprintf(
                '%d giấy tờ cần chuẩn bị cho nhóm "%s". %s',
                $nhomDangXem->items->count(),
                $tenNhom,
                nx_chu_thuan($nhomDangXem->description, 30)
            );
        } else {
            $tieuDeSeo = $tieuDe;
            $moTaSeo = nx_chu_thuan($intro['dossier_description'] ?? '')
                ?: 'Danh sách giấy tờ cần chuẩn bị khi mua nhà ở xã hội, theo từng nhóm đối tượng.';
        }

        return view('frontend.noxh.dossier.index', [
            'system' => $this->system,
            'seo' => $this->seo($tieuDeSeo, $canonical, $moTaSeo),
            'kieu' => $kieu,
            'tieuDe' => $tieuDe,
            'boHoSo' => $boHoSo,
            'tatCaBo' => DossierSet::where('publish', 2)->orderBy('order')->get(['id', 'name', 'canonical']),
            'boChon' => $boChon,
        ]);
    }

    private function seo(string $tieuDe, string $canonical, string $moTa = ''): array
    {
        return [
            'meta_title' => $tieuDe . ' - NOXH.vn',
            'meta_description' => $moTa ?: ($this->system['seo_meta_description'] ?? ''),
            'meta_image' => $this->system['seo_meta_images'] ?? '',
            'canonical' => $canonical,
        ];
    }
}
