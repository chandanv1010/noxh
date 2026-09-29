<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\Post;
use App\Repositories\Noxh\ProjectQuery;
use Illuminate\Http\Request;

/**
 * Trang ket qua cua o tim kiem tren dau trang.
 *
 * O do tim ca du an lan bai viet nen trang nay hoi ca hai bang roi xep ket
 * qua thanh hai khoi - khong co bang chi muc rieng, du lieu con nho nen tim
 * thang bang LIKE la du.
 */
class SearchController extends FrontendController
{
    private const SO_DU_AN = 12;
    private const SO_BAI = 12;

    protected $projectQuery;

    public function __construct(ProjectQuery $projectQuery)
    {
        $this->projectQuery = $projectQuery;
        parent::__construct();
    }

    public function index(Request $request)
    {
        $tuKhoa = trim((string) $request->input('tu-khoa'));

        $duAn = collect();
        $baiViet = collect();

        // Tu khoa qua ngan thi khong tim: mot ky tu se khop gan het bang.
        if (mb_strlen($tuKhoa) >= 2) {
            $duAn = $this->projectQuery->danhSach(['keyword' => $tuKhoa], 'moi-nhat', self::SO_DU_AN)
                ->getCollection();
            $baiViet = $this->timBaiViet($tuKhoa);
        }

        return view('frontend.noxh.search.index', [
            'system' => $this->system,
            'seo' => [
                'meta_title' => $tuKhoa !== ''
                    ? 'Kết quả tìm kiếm cho "' . $tuKhoa . '" - NOXH.vn'
                    : 'Tìm kiếm - NOXH.vn',
                'meta_description' => '',
                'canonical' => url('/tim-kiem'),
            ],
            'tuKhoa' => $tuKhoa,
            'duAn' => $duAn,
            'baiViet' => $baiViet,
        ]);
    }

    private function timBaiViet(string $tuKhoa)
    {
        return Post::query()
            ->join('post_language as pl', function ($join) {
                $join->on('pl.post_id', '=', 'posts.id')->where('pl.language_id', '=', $this->language);
            })
            ->where('posts.publish', 2)
            ->whereNull('posts.deleted_at')
            ->where(function ($q) use ($tuKhoa) {
                $q->where('pl.name', 'LIKE', '%' . $tuKhoa . '%')
                  ->orWhere('pl.description', 'LIKE', '%' . $tuKhoa . '%');
            })
            ->orderByDesc('posts.id')
            ->limit(self::SO_BAI)
            ->get(['posts.id', 'posts.image', 'posts.created_at', 'pl.name', 'pl.canonical', 'pl.description']);
    }
}
