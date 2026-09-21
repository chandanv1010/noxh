<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\Post;
use App\Models\QaQuestion;
use App\Repositories\Noxh\ProjectQuery;

/**
 * Trang chu NOXH.vn.
 *
 * Moi doan chu deu doc tu bang introduces (module Gioi thieu trong quan tri),
 * du an doc tu bang products, cau hoi tu qa_questions, tin tuc tu posts -
 * khong co chu nao ghi cung trong ma nguon.
 */
class HomeController extends FrontendController
{
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
            'duAnNoiBat' => $this->projectQuery->noiBat(4),
            'tinhThanh' => $this->projectQuery->tinhCoDuAn(),
            'cauHoi' => QaQuestion::where('publish', 2)
                ->orderByDesc('is_featured')
                ->orderByDesc('id')
                ->limit(3)
                ->get(),
            'tinTuc' => $this->tinMoi(),
        ]);
    }

    /**
     * Ba bai viet moi nhat. Ten bai nam o bang ngon ngu nen phai join, khong
     * dung quan he ->languages de khoi sinh mot truy van cho moi bai.
     */
    private function tinMoi()
    {
        return Post::query()
            ->join('post_language as pl', function ($join) {
                $join->on('pl.post_id', '=', 'posts.id')->where('pl.language_id', '=', $this->language);
            })
            ->where('posts.publish', 2)
            ->whereNull('posts.deleted_at')
            ->orderByDesc('posts.id')
            ->limit(3)
            ->get(['posts.id', 'posts.image', 'posts.created_at', 'pl.name', 'pl.canonical', 'pl.description']);
    }
}
