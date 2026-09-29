<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\User;
use App\Repositories\Noxh\PostQuery;
use App\Repositories\Noxh\ProjectQuery;

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

    /**
     * So tu van vien hien o trang chu.
     *
     * Dung bang so cot cua luoi: nhieu hon mot nguoi la hang thu hai chi co
     * mot the le loi, trong rat vo bo cuc.
     */
    private const SO_TU_VAN_VIEN = 6;

    /** Ban thiet ke xep ba tin nam ngang trong khoi tin tuc. */
    private const SO_TIN = 3;

    protected $projectQuery;
    protected $postQuery;

    public function __construct(ProjectQuery $projectQuery, PostQuery $postQuery)
    {
        $this->projectQuery = $projectQuery;
        $this->postQuery = $postQuery;
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
            'tinhThanh' => $this->projectQuery->moiTinhThanh(),
            'tinTuc' => $this->postQuery->moiNhat(self::SO_TIN),
            'nhanVien' => $this->tuVanVien(),
        ]);
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
