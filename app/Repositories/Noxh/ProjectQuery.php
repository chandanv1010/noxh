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
        'pr.name as province_name', 'vw.name as ward_name',
    ];

    /** Khung truy van chung: chi du an dang hien, kem ten va ten tinh. */
    public function co()
    {
        return DB::table('products as p')
            ->join('product_language as pl', function ($join) {
                $join->on('pl.product_id', '=', 'p.id')->where('pl.language_id', '=', 1);
            })
            // vn_provinces la bang 34 tinh/thanh theo co cau hanh chinh moi
            // (tu 01/07/2025), nap tu API bang lenh `php artisan noxh:dia-gioi`.
            // Bang `provinces` cu 63 tinh khong con dung nua.
            ->leftJoin('vn_provinces as pr', 'pr.code', '=', 'p.province_code')
            ->leftJoin('vn_wards as vw', 'vw.code', '=', 'p.ward_code')
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
     * $loc: province_code, ward_code, status[], price (khoang), area (khoang), keyword
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

    /**
     * Du an de ve len ban do - /du-an/ban-do.
     *
     * Khac danh sach o cho: KHONG phan trang (ban do phai ve het mot luot,
     * cat bot la mat ghim) va BAT BUOC co toa do - khong co lat/lng thi
     * khong biet ve vao dau. So du an chua co toa do duoc dem rieng de bao
     * cho nguoi xem biet con bao nhieu dung ngoai ban do.
     */
    public function choBanDo(array $loc, int $gioiHan = 500)
    {
        $query = $this->co();
        $this->apDungLoc($query, $loc);

        return $query->whereNotNull('p.latitude')
            ->whereNotNull('p.longitude')
            ->orderByDesc('p.is_featured')
            ->orderByDesc('p.id')
            ->limit($gioiHan)
            ->get(array_merge(self::COT, [
                'p.latitude', 'p.longitude', 'p.address', 'p.ward_code',
            ]));
    }

    /** Dem du an KHOP BO LOC nhung chua co toa do nen khong ve len ban do. */
    public function demThieuToaDo(array $loc): int
    {
        $query = $this->co();
        $this->apDungLoc($query, $loc);

        return $query->where(function ($q) {
            $q->whereNull('p.latitude')->orWhereNull('p.longitude');
        })->count();
    }

    /**
     * Phuong/xa dang co du an trong mot tinh, kem so luong - cho o loc thu
     * hai cua trang ban do.
     *
     * Chi do ra phuong/xa CO du an: o loc nay de thu nho vung dang xem, do
     * ca 3321 phuong/xa ra thi cuon mai khong het ma phan lon chon vao se
     * ra trang trong.
     */
    public function xaCoDuAn(?string $maTinh)
    {
        if (!$maTinh) {
            return collect();
        }

        return $this->co()
            ->where('p.province_code', $maTinh)
            ->whereNotNull('p.ward_code')
            ->groupBy('p.ward_code', 'vw.name', 'vw.lat', 'vw.lng')
            ->orderBy('vw.name')
            ->get([
                'p.ward_code', 'vw.name as ward_name', 'vw.lat', 'vw.lng',
                DB::raw('COUNT(*) as so_du_an'),
            ]);
    }

    /** Cac tinh dang co du an, kem so luong - dung cho khoi chip o cot phai. */
    public function tinhCoDuAn(int $soLuong = 8)
    {
        return $this->co()
            ->whereNotNull('p.province_code')
            ->groupBy('p.province_code', 'pr.name', 'pr.lat', 'pr.lng')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit($soLuong)
            ->get([
                'p.province_code', 'pr.name as province_name',
                'pr.lat', 'pr.lng', DB::raw('COUNT(*) as so_du_an'),
            ]);
    }

    /**
     * TAT CA tinh/thanh, kem so du an cua tung tinh (0 neu chua co).
     *
     * Dung cho o chon trong thanh tim kiem: chi do ra tinh dang co du an thi
     * nguoi dung khong tim duoc tinh cua minh, ma khong tim duoc thi khong
     * biet la chua co du an hay trang bi loi. Cho chon het roi tra ve trang
     * "khong tim thay" ro rang hon.
     *
     * Danh sach lay tu vn_provinces - bo 34 tinh/thanh nap tu API bang lenh
     * `php artisan noxh:dia-gioi`.
     */
    public function moiTinhThanh()
    {
        $dem = $this->co()
            ->whereNotNull('p.province_code')
            ->groupBy('p.province_code')
            ->pluck(DB::raw('COUNT(*)'), 'p.province_code');

        return DB::table('vn_provinces')
            ->orderBy('order')
            ->orderBy('name')
            ->get(['code as province_code', 'name as province_name'])
            ->each(fn ($t) => $t->so_du_an = (int) ($dem[$t->province_code] ?? 0));
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
                // Anh va lien ket rieng cua trang chi tiet.
                'p.video_url', 'p.site_plan_image', 'p.progress_image',
                'p.progress_url', 'p.map_image', 'p.map_url',
                'pl.content', 'pl.meta_title', 'pl.meta_description',
                'iv.name as investor_name', 'iv.hotline as investor_hotline',
                'iv.email as investor_email', 'iv.website as investor_website',
                'iv.address as investor_address',
            ]));
    }

    /** Du an cung tinh, bo chinh du an dang xem. */
    /**
     * Du an tuong tu.
     *
     * Uu tien danh sach quan tri tu chon o form du an (bang product_related).
     * Chua chon thi moi tu doc ra du an cung tinh - de mot du an vua tao xong
     * cung co khoi nay chu khong trong tron.
     */
    public function tuongTu($duAn, int $soLuong = 3)
    {
        $chon = \Illuminate\Support\Facades\DB::table('product_related')
            ->where('product_id', $duAn->id)
            ->orderBy('order')
            ->pluck('related_id')
            ->all();

        if (count($chon)) {
            $dong = $this->co()
                ->whereIn('p.id', $chon)
                ->limit($soLuong)
                ->get(self::COT);

            // Giu dung thu tu quan tri da xep chu khong theo thu tu id.
            $thuTu = array_flip($chon);

            return $dong->sortBy(fn ($d) => $thuTu[$d->id] ?? 999)->values();
        }

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

        // Co cau hanh chinh moi chi con hai cap: tinh/thanh -> phuong/xa.
        // Khong con quan/huyen de loc qua nua.
        if (!empty($loc['ward_code'])) {
            $query->where('p.ward_code', $loc['ward_code']);
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
