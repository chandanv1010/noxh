<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
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

        if ($boChon) {
            $loc = $boHoSo->firstWhere('canonical', $boChon) ?? $boHoSo->firstWhere('id', (int) $boChon);
            if ($loc) {
                $boHoSo = collect([$loc]);
            }
        }

        return view('frontend.noxh.dossier.index', [
            'system' => $this->system,
            'seo' => $this->seo($tieuDe, $canonical),
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
