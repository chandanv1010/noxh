<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Http\ViewComposers\NoxhComposer;
use App\Models\Post;
use App\Models\PostCatalogue;
use Illuminate\Http\Request;

/**
 * Trang danh muc tin tuc va trang chi tiet tin.
 *
 * Ban ve: noxh_image/tin-tuc-fix.webp. Hai trang dung CHUNG cot trai (danh
 * muc + the ho tro) va cot phai (bai lien quan + chuyen gia), chi khac phan
 * giua - nen phan du lieu cua hai cot do gom vao mot cho.
 */
class NewsController extends FrontendController
{
    /** So bai moi trang o trang danh muc. */
    private const MOI_TRANG = 8;

    /** So bai trong khoi "Bai viet lien quan" o cot phai. */
    private const SO_LIEN_QUAN = 5;

    public function index(Request $request, string $canonical = '')
    {
        $chuyenMuc = $canonical === '' ? null : $this->chuyenMuc($canonical);

        if ($canonical !== '' && !$chuyenMuc) {
            abort(404);
        }

        $khung = $this->khung();

        if ($chuyenMuc) {
            $this->locTheoChuyenMuc($khung, (int) $chuyenMuc->id);
        }

        $baiViet = $khung->orderByDesc('posts.id')
            ->paginate(self::MOI_TRANG)
            ->withQueryString();

        $intro = NoxhComposer::intro();
        $ten = $chuyenMuc->name ?? ($intro['news_heading'] ?? 'Tin tức');

        return view('frontend.noxh.news.index', [
            'system' => $this->system,
            'seo' => [
                'meta_title' => ($chuyenMuc->meta_title ?? '') ?: $ten . ' - NOXH.vn',
                'meta_description' => ($chuyenMuc->meta_description ?? '')
                    ?: ($this->system['seo_meta_description'] ?? ''),
                'meta_image' => $this->system['seo_meta_images'] ?? '',
                'canonical' => $chuyenMuc
                    ? url('/tin-tuc/chuyen-muc/' . $chuyenMuc->canonical)
                    : url('/tin-tuc'),
            ],
            'baiViet' => $baiViet,
            'chuyenMuc' => $chuyenMuc,
            'danhMuc' => $this->danhMuc(),
            'dangMo' => $chuyenMuc->id ?? null,
            'lienQuan' => $this->lienQuan(),
        ]);
    }

    public function show(string $canonical)
    {
        // addSelect chu KHONG phai first([...]): khung() da goi select() roi,
        // ma first($cot) chi dat cot khi truy van chua co cot nao - truyen vao
        // day thi Laravel bo qua, bai viet doc ra se thieu han phan noi dung.
        $bai = $this->khung()
            ->addSelect([
                'posts.image_caption',
                'pl.content', 'pl.meta_title', 'pl.meta_keyword', 'pl.meta_description',
            ])
            ->where('pl.canonical', $canonical)
            ->first();

        if (!$bai) {
            abort(404);
        }

        // Dem luot xem. Dung increment chu khong doc ra roi ghi lai: hai nguoi
        // mo cung mot bai trong cung mot giay se khong de mat mot luot.
        Post::where('id', $bai->id)->increment('viewed');
        $bai->viewed = (int) $bai->viewed + 1;

        $chuyenMuc = $this->chuyenMucTheoId((int) $bai->post_catalogue_id);

        return view('frontend.noxh.news.show', [
            'system' => $this->system,
            'seo' => [
                'meta_title' => $bai->meta_title ?: $bai->name,
                'meta_description' => $bai->meta_description ?: strip_tags((string) $bai->description),
                'meta_image' => $bai->image,
                'canonical' => url('/tin-tuc/' . $bai->canonical),
            ],
            'bai' => $bai,
            'chuyenMuc' => $chuyenMuc,
            'danhMuc' => $this->danhMuc(),
            'dangMo' => $chuyenMuc->id ?? null,
            'lienQuan' => $this->lienQuan((int) $bai->id, (int) $bai->post_catalogue_id),
        ]);
    }

    /**
     * Cot trai: danh sach chuyen muc kem so bai.
     *
     * Chuyen muc RONG van hien - ban ve liet ke du bay dong. Nguoi dung bam
     * vao mot muc chua co bai thi thay cau "chua co bai nao", ro rang hon la
     * mot muc tu bien mat.
     */
    private function danhMuc()
    {
        return PostCatalogue::query()
            ->join('post_catalogue_language as cl', function ($join) {
                $join->on('cl.post_catalogue_id', '=', 'post_catalogues.id')
                    ->where('cl.language_id', '=', $this->language);
            })
            ->where('post_catalogues.publish', 2)
            ->whereNull('post_catalogues.deleted_at')
            ->orderBy('post_catalogues.lft')
            ->get(['post_catalogues.id', 'post_catalogues.icon', 'post_catalogues.color',
                'cl.name', 'cl.canonical']);
    }

    /** Cot phai: bai moi nhat, uu tien cung chuyen muc voi bai dang doc. */
    private function lienQuan(int $boQuaId = 0, int $chuyenMucId = 0)
    {
        $lay = function ($cungMuc) use ($boQuaId, $chuyenMucId) {
            $khung = $this->khung();

            if ($boQuaId) {
                $khung->where('posts.id', '!=', $boQuaId);
            }

            if ($cungMuc) {
                $this->locTheoChuyenMuc($khung, $chuyenMucId);
            }

            return $khung->orderByDesc('posts.id')->limit(self::SO_LIEN_QUAN)->get();
        };

        $ds = $chuyenMucId ? $lay(true) : collect();

        if ($ds->count() >= self::SO_LIEN_QUAN) {
            return $ds;
        }

        // Chua du thi lay them bai moi nhat cua cac muc khac cho day khoi.
        $da = $ds->pluck('id')->all();

        return $ds->concat(
            $lay(false)->reject(fn ($b) => in_array($b->id, $da, true))
        )->take(self::SO_LIEN_QUAN)->values();
    }

    private function chuyenMuc(string $canonical)
    {
        return $this->khungChuyenMuc()->where('cl.canonical', $canonical)->first();
    }

    private function chuyenMucTheoId(int $id)
    {
        return $id ? $this->khungChuyenMuc()->where('post_catalogues.id', $id)->first() : null;
    }

    private function khungChuyenMuc()
    {
        return PostCatalogue::query()
            ->join('post_catalogue_language as cl', function ($join) {
                $join->on('cl.post_catalogue_id', '=', 'post_catalogues.id')
                    ->where('cl.language_id', '=', $this->language);
            })
            ->where('post_catalogues.publish', 2)
            ->whereNull('post_catalogues.deleted_at')
            ->select(['post_catalogues.id', 'post_catalogues.icon', 'post_catalogues.color',
                'cl.name', 'cl.canonical', 'cl.description',
                'cl.meta_title', 'cl.meta_description']);
    }

    /**
     * Mot bai co the nam o nhieu chuyen muc: mot muc chinh (cot
     * posts.post_catalogue_id) va cac muc phu (bang noi post_catalogue_post).
     * Loc phai xet ca hai, khong thi bai gan muc phu se khong bao gio hien.
     */
    private function locTheoChuyenMuc($khung, int $id): void
    {
        $khung->where(function ($q) use ($id) {
            $q->where('posts.post_catalogue_id', $id)
                ->orWhereExists(function ($sub) use ($id) {
                    $sub->from('post_catalogue_post as pcp')
                        ->whereColumn('pcp.post_id', 'posts.id')
                        ->where('pcp.post_catalogue_id', $id);
                });
        });
    }

    /**
     * Khung truy van chung. Ten bai nam o bang ngon ngu nen phai join san,
     * dung quan he ->languages se sinh mot truy van cho tung bai.
     */
    private function khung()
    {
        return Post::query()
            ->join('post_language as pl', function ($join) {
                $join->on('pl.post_id', '=', 'posts.id')->where('pl.language_id', '=', $this->language);
            })
            ->where('posts.publish', 2)
            ->whereNull('posts.deleted_at')
            ->select([
                'posts.id', 'posts.image', 'posts.viewed', 'posts.recommend',
                'posts.post_catalogue_id', 'posts.created_at',
                'pl.name', 'pl.canonical', 'pl.description',
            ]);
    }
}
