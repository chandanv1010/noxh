<?php

namespace App\Repositories\Noxh;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

/**
 * Truy van bai viet dung chung cho frontend NOXH.
 *
 * Khoi tin tuc xuat hien o ba cho (trang chu, cot phai trang danh sach du
 * an, va sau nay la trang chi tiet) voi cung mot cach lay: bai moi nhat kem
 * ten va mau nhan cua chuyen muc. Gom vao mot cho de ba noi khong lech nhau
 * khi doi cach sap xep.
 */
class PostQuery
{
    /** Chi co mot ngon ngu; giu bien de sau nay them ngon ngu khong phai sua truy van. */
    protected int $language = 1;

    /**
     * Bai viet moi nhat, kem catalogue_name / catalogue_color /
     * catalogue_canonical.
     */
    public function moiNhat(int $soLuong = 3)
    {
        $bai = Post::query()
            ->join('post_language as pl', function ($join) {
                $join->on('pl.post_id', '=', 'posts.id')
                    ->where('pl.language_id', '=', $this->language);
            })
            ->where('posts.publish', 2)
            ->whereNull('posts.deleted_at')
            ->orderByDesc('posts.id')
            ->limit($soLuong)
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

    /**
     * Chuyen muc dau tien cua tung bai, tra ve mang bai_id => dong.
     *
     * Mot bai co the thuoc nhieu chuyen muc; lay chuyen muc co id nho nhat
     * cho on dinh - neu khong, nhan mau cua cung mot bai co the doi giua hai
     * lan mo trang.
     */
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
}
