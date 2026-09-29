<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\LegalDocument;
use App\Models\Post;
use Illuminate\Http\Request;

/**
 * Phong phap ly NOXH: chu de, bai viet phap ly va thu vien van ban.
 */
class LegalController extends FrontendController
{
    /** So the tren mot hang theo ban ve plxh fix.jpg. */
    private const SO_BAI_VIET = 4;
    private const SO_VAN_BAN = 4;

    public function index()
    {
        return view('frontend.noxh.legal.index', [
            'system' => $this->system,
            'seo' => $this->seo('Phòng pháp lý NOXH', url('/phap-ly-noxh')),
            'baiViet' => $this->baiViet(self::SO_BAI_VIET),
            'vanBan' => LegalDocument::where('publish', 2)
                ->orderByDesc('is_featured')
                ->orderByRaw('effective_date IS NULL, effective_date DESC')
                ->limit(self::SO_VAN_BAN)
                ->get(),
        ]);
    }

    public function documents(Request $request)
    {
        $loai = $request->input('loai');

        $query = LegalDocument::where('publish', 2);

        if (array_key_exists((string) $loai, LegalDocument::LOAI)) {
            $query->where('doc_type', $loai);
        }

        if ($tu = trim((string) $request->input('tu-khoa'))) {
            $query->where(function ($q) use ($tu) {
                $q->where('title', 'LIKE', '%' . $tu . '%')
                  ->orWhere('doc_number', 'LIKE', '%' . $tu . '%');
            });
        }

        return view('frontend.noxh.legal.documents', [
            'system' => $this->system,
            'seo' => $this->seo('Văn bản pháp luật về nhà ở xã hội', url('/phap-ly-noxh/van-ban')),
            'vanBan' => $query->orderByRaw('effective_date IS NULL, effective_date DESC')
                ->paginate(15)->withQueryString(),
            'loai' => LegalDocument::LOAI,
            'loaiChon' => $loai,
            'demTheoLoai' => LegalDocument::where('publish', 2)
                ->selectRaw('doc_type, COUNT(*) AS so')
                ->groupBy('doc_type')
                ->pluck('so', 'doc_type')
                ->toArray(),
        ]);
    }

    /**
     * Dem luot tai roi chuyen sang file that.
     *
     * Dem bang tang truc tiep tren CSDL chu khong doc ra roi ghi lai: hai
     * nguoi bam cung luc ma doc-roi-ghi thi mot luot bi mat.
     */
    public function download(int $id)
    {
        $vanBan = LegalDocument::where('publish', 2)->findOrFail($id);

        if (empty($vanBan->file)) {
            abort(404);
        }

        LegalDocument::where('id', $vanBan->id)->increment('download_count');

        return redirect($vanBan->file);
    }

    private function baiViet(int $soLuong)
    {
        return Post::query()
            ->join('post_language as pl', function ($join) {
                $join->on('pl.post_id', '=', 'posts.id')->where('pl.language_id', '=', $this->language);
            })
            ->where('posts.publish', 2)
            ->whereNull('posts.deleted_at')
            ->orderByDesc('posts.id')
            ->limit($soLuong)
            ->get([
                'posts.id', 'posts.image', 'posts.created_at', 'posts.viewed', 'posts.recommend',
                'pl.name', 'pl.canonical', 'pl.description',
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
