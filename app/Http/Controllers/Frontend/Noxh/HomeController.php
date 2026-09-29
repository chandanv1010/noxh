<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Noxh\ProjectQuery;
use Illuminate\Support\Facades\DB;

/**
 * Trang chu NOXH.vn.
 *
 * Moi doan chu deu doc tu bang introduces (module Gioi thieu trong quan tri),
 * du an doc tu bang products, tin tuc tu posts, tu van vien tu users -
 * khong co chu nao ghi cung trong ma nguon.
 */
class HomeController extends FrontendController
{
    /**
     * So du an nap cho khoi "Du an noi bat".
     *
     * Nap du 12 roi loc bang JS theo the tinh/thanh, thay vi moi lan bam the
     * lai goi may chu - danh sach nho nen tai het mot lan van nhe hon.
     */
    private const SO_DU_AN_NOI_BAT = 12;

    private const SO_TU_VAN_VIEN = 12;

    /** Ban thiet ke xep ba tin nam ngang trong khoi tin tuc. */
    private const SO_TIN = 3;

    protected $projectQuery;

    public function __construct(ProjectQuery $projectQuery)
    {
        $this->projectQuery = $projectQuery;
        parent::__construct();
    }

    public function index()
    {
        $system = $this->system;

        return view('frontend.noxh.home.index', [
            'system' => $system,
            'seo' => [
                'meta_title' => $system['seo_meta_title'] ?: 'NOXH.vn - Cổng thông tin nhà ở xã hội',
                'meta_keyword' => $system['seo_meta_keyword'] ?? '',
                'meta_description' => $system['seo_meta_description'] ?? '',
                'meta_image' => $system['seo_meta_images'] ?? '',
                'canonical' => url('/'),
            ],
            'duAnNoiBat' => $this->projectQuery->noiBat(self::SO_DU_AN_NOI_BAT),
            'tinhThanh' => $this->projectQuery->tinhCoDuAn(),
            'tinTuc' => $this->tinMoi(),
            'nhanVien' => $this->tuVanVien(),
        ]);
    }

    /**
     * Ba bai viet moi nhat, kem ten va mau nhan cua chuyen muc.
     *
     * Chuyen muc lay qua bang noi post_catalogue_post; mot bai co the thuoc
     * nhieu chuyen muc nen chi lay chuyen muc co id nho nhat cho on dinh.
     */
    private function tinMoi()
    {
        $bai = Post::query()
            ->join('post_language as pl', function ($join) {
                $join->on('pl.post_id', '=', 'posts.id')->where('pl.language_id', '=', $this->language);
            })
            ->where('posts.publish', 2)
            ->whereNull('posts.deleted_at')
            ->orderByDesc('posts.id')
            ->limit(self::SO_TIN)
            ->get(['posts.id', 'posts.image', 'posts.created_at', 'pl.name', 'pl.canonical', 'pl.description']);

        if ($bai->isEmpty()) {
            return $bai;
        }

        $chuyenMuc = $this->chuyenMucCuaBai($bai->pluck('id')->all());

        foreach ($bai as $b) {
            $cm = $chuyenMuc[$b->id] ?? null;
            $b->catalogue_name = $cm->name ?? null;
            $b->catalogue_color = nx_mau_chuyen_muc($cm->color ?? null);
            $b->catalogue_canonical = $cm->canonical ?? null;
        }

        return $bai;
    }

    /** Chuyen muc dau tien cua tung bai, tra ve mang bai_id => dong. */
    private function chuyenMucCuaBai(array $baiId): array
    {
        return DB::table('post_catalogue_post as pcp')
            ->join('post_catalogues as pc', 'pc.id', '=', 'pcp.post_catalogue_id')
            ->join('post_catalogue_language as pcl', function ($join) {
                $join->on('pcl.post_catalogue_id', '=', 'pc.id')
                    ->where('pcl.language_id', '=', $this->language);
            })
            ->whereIn('pcp.post_id', $baiId)
            ->whereNull('pc.deleted_at')
            ->orderBy('pcp.post_id')
            ->orderBy('pc.id')
            ->get(['pcp.post_id', 'pcl.name', 'pcl.canonical', 'pc.color'])
            ->keyBy('post_id')
            ->all();
    }

    /**
     * Doi tu van ho so hien o khoi "Tu van ho so tai khu vuc cua ban".
     *
     * Lay chinh nhung nguoi trong nhom nhan vien kinh doanh - khong co bang
     * rieng, nen ho tu cap nhat anh va chuc danh trong bang dieu khien /sale
     * la ngoai website doi theo.
     */
    private function tuVanVien()
    {
        return User::whereHas('user_catalogues', fn ($q) => $q->where('is_sale', 1))
            ->where('publish', 2)
            ->orderBy('name')
            ->limit(self::SO_TU_VAN_VIEN)
            ->get(['id', 'name', 'title', 'image', 'address', 'phone']);
    }
}
