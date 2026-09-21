<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class NewsController extends FrontendController
{
    public function index()
    {
        return view('frontend.noxh.news.index', [
            'system' => $this->system,
            'seo' => $this->seo('Tin tức nhà ở xã hội', url('/tin-tuc')),
            'baiViet' => $this->khung()->orderByDesc('posts.id')->paginate(12),
        ]);
    }

    public function show(string $canonical)
    {
        $bai = $this->khung()->where('pl.canonical', $canonical)->first([
            'posts.id', 'posts.image', 'posts.created_at',
            'pl.name', 'pl.canonical', 'pl.description', 'pl.content',
            'pl.meta_title', 'pl.meta_description',
        ]);

        if (!$bai) {
            abort(404);
        }

        return view('frontend.noxh.news.show', [
            'system' => $this->system,
            'seo' => [
                'meta_title' => $bai->meta_title ?: $bai->name,
                'meta_description' => $bai->meta_description ?: strip_tags((string) $bai->description),
                'meta_image' => $bai->image,
                'canonical' => url('/tin-tuc/' . $bai->canonical),
            ],
            'bai' => $bai,
            'khac' => $this->khung()
                ->where('posts.id', '!=', $bai->id)
                ->orderByDesc('posts.id')
                ->limit(5)
                ->get(),
        ]);
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
                'posts.id', 'posts.image', 'posts.created_at',
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
