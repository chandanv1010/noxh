<?php

namespace App\Repositories\Noxh;

use Illuminate\Support\Facades\DB;

/**
 * Cac truy van du an dung chung cho nhieu trang.
 *
 * Ten va mo ta du an nam o bang product_language nen moi truy van deu phai
 * join san - dung quan he ->languages se sinh mot truy van cho tung du an.
 */
class ProjectQuery
{
    private const COT = [
        'p.id', 'p.image', 'p.code', 'p.status', 'p.province_code',
        'p.price_from', 'p.price_to', 'p.area_from', 'p.area_to',
        'p.total_units', 'p.total_land_area', 'p.scale_description',
        'p.timeline_label', 'p.is_featured', 'p.updated_at',
        'pl.name', 'pl.canonical', 'pl.description',
        'pr.name as province_name',
    ];

    /** Khung truy van chung: chi du an dang hien, kem ten va ten tinh. */
    public function co()
    {
        return DB::table('products as p')
            ->join('product_language as pl', function ($join) {
                $join->on('pl.product_id', '=', 'p.id')->where('pl.language_id', '=', 1);
            })
            ->leftJoin('provinces as pr', 'pr.code', '=', 'p.province_code')
            ->where('p.publish', 2)
            ->whereNull('p.deleted_at');
    }

    public function noiBat(int $soLuong = 4)
    {
        return $this->co()
            ->orderByDesc('p.is_featured')
            ->orderByDesc('p.id')
            ->limit($soLuong)
            ->get(self::COT);
    }

    /**
     * Danh sach du an co loc va sap xep, dung o trang Du an.
     *
     * $loc: province_code, status[], price (khoang), area (khoang), keyword
     */
    public function danhSach(array $loc, string $sapXep = 'moi-nhat', int $moiTrang = 10)
    {
        $query = $this->co();
        $this->apDungLoc($query, $loc);

        match ($sapXep) {
            'gia-tang' => $query->orderByRaw('p.price_from IS NULL, p.price_from ASC'),
            'gia-giam' => $query->orderByRaw('p.price_to IS NULL, p.price_to DESC'),
            'dien-tich' => $query->orderByRaw('p.area_from IS NULL, p.area_from ASC'),
            default => $query->orderByDesc('p.id'),
        };

        return $query->paginate($moiTrang, self::COT)->withQueryString();
    }

    /**
     * Dem so du an cho tung lua chon cua bo loc.
     *
     * Gom ve MOT truy van GROUP BY cho moi nhom thay vi dem tung o - neu khong
     * rieng cot loc da la hai chuc truy van moi lan mo trang.
     */
    public function demTheoTrangThai(array $loc): array
    {
        $query = $this->co();
        // Bo dieu kien trang thai ra: so dem phai cho biet "neu chon them muc
        // nay thi con bao nhieu", chu khong phai dem trong ket qua da loc roi.
        $this->apDungLoc($query, array_diff_key($loc, ['status' => 1]));

        return $query->groupBy('p.status')
            ->pluck(DB::raw('COUNT(*)'), 'p.status')
            ->toArray();
    }

    public function demTheoKhoang(array $loc, string $cotTu, string $cotDen, array $khoang): array
    {
        $ket = [];

        foreach ($khoang as $ma => $m) {
            $query = $this->co();
            $this->apDungLoc($query, array_diff_key($loc, ['price' => 1, 'area' => 1]));
            $this->khoang($query, $cotTu, $cotDen, $m['tu'], $m['den']);
            $ket[$ma] = $query->count();
        }

        return $ket;
    }

    /** Cac tinh dang co du an, kem so luong - dung cho khoi chip o cot phai. */
    public function tinhCoDuAn(int $soLuong = 8)
    {
        return $this->co()
            ->whereNotNull('p.province_code')
            ->groupBy('p.province_code', 'pr.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit($soLuong)
            ->get(['p.province_code', 'pr.name as province_name', DB::raw('COUNT(*) as so_du_an')]);
    }

    public function tongSoDuAn(): int
    {
        return $this->co()->count();
    }

    public function tongSoTinh(): int
    {
        return (int) $this->co()->whereNotNull('p.province_code')->distinct()->count('p.province_code');
    }

    /** Mot du an theo duong dan, kem chu dau tu. */
    public function theoCanonical(string $canonical)
    {
        return $this->co()
            ->leftJoin('investors as iv', 'iv.id', '=', 'p.investor_id')
            ->where('pl.canonical', $canonical)
            ->first(array_merge(self::COT, [
                'p.address', 'p.latitude', 'p.longitude', 'p.ward_code',
                'p.ownership_type', 'p.apartment_types', 'p.start_date', 'p.handover_date',
                'p.album', 'p.investor_id',
                'pl.content', 'pl.meta_title', 'pl.meta_description',
                'iv.name as investor_name', 'iv.hotline as investor_hotline',
                'iv.email as investor_email', 'iv.website as investor_website',
                'iv.address as investor_address',
            ]));
    }

    /** Du an cung tinh, bo chinh du an dang xem. */
    public function tuongTu($duAn, int $soLuong = 3)
    {
        return $this->co()
            ->where('p.id', '!=', $duAn->id)
            ->when($duAn->province_code, fn($q) => $q->where('p.province_code', $duAn->province_code))
            ->orderByDesc('p.is_featured')
            ->limit($soLuong)
            ->get(self::COT);
    }

    // -------------------------------------------------------------------------

    private function apDungLoc($query, array $loc): void
    {
        if (!empty($loc['keyword'])) {
            $tu = $loc['keyword'];
            $query->where(function ($q) use ($tu) {
                $q->where('pl.name', 'LIKE', '%' . $tu . '%')
                  ->orWhere('p.address', 'LIKE', '%' . $tu . '%')
                  ->orWhere('pr.name', 'LIKE', '%' . $tu . '%');
            });
        }

        if (!empty($loc['province_code'])) {
            $query->where('p.province_code', $loc['province_code']);
        }

        if (!empty($loc['status'])) {
            $query->whereIn('p.status', (array) $loc['status']);
        }

        foreach ((array) ($loc['price'] ?? []) as $m) {
            $this->khoang($query, 'price_from', 'price_to', $m['tu'], $m['den']);
        }

        foreach ((array) ($loc['area'] ?? []) as $m) {
            $this->khoang($query, 'area_from', 'area_to', $m['tu'], $m['den']);
        }
    }

    /**
     * Du an luu mot KHOANG gia (tu - den), nguoi dung cung chon mot khoang.
     * Hai khoang duoc coi la khop khi chung GIAO NHAU, chu khong phai khi
     * khoang cua du an nam gon trong khoang loc - neu khong du an 19-24
     * trieu se rot khoi ca muc "18-20" lan "20-22".
     */
    private function khoang($query, string $cotTu, string $cotDen, ?float $tu, ?float $den): void
    {
        $query->where(function ($q) use ($cotTu, $cotDen, $tu, $den) {
            if (!is_null($den)) {
                $q->where("p.$cotTu", '<=', $den);
            }
            if (!is_null($tu)) {
                $q->where(function ($qq) use ($cotDen, $cotTu, $tu) {
                    $qq->where("p.$cotDen", '>=', $tu)
                       ->orWhere(function ($q3) use ($cotDen, $cotTu, $tu) {
                           $q3->whereNull("p.$cotDen")->where("p.$cotTu", '>=', $tu);
                       });
                });
            }
        });
    }
}
