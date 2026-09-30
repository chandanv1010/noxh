<?php

namespace App\Services\V1\Eligibility;

use App\Classes\NoxhKhoangCach;
use App\Http\ViewComposers\NoxhComposer;
use App\Models\EligibilityCriterion;

/**
 * Cham mot luot kiem tra dieu kien theo SAU TIEU CHI.
 *
 * Cong thuc: file noxh_image/cong-thuc.jpg. Ba ban ve ket qua in mot bang
 * tieu chi va mot con so dang "n/6" chu khong in lai tung cau hoi, nen cham
 * diem cung phai tinh theo tieu chi.
 *
 * Quy tac ket luan chung (doc thang tu ba ban ve):
 *   - con mot tieu chi "Chua dap ung"  -> that bai  (muc low)
 *   - khong co cai nao chua dap ung nhung con "Can xac minh" -> luu y (medium)
 *   - dat het -> thanh cong (muc high)
 *
 * Diem = SO TIEU CHI DAT, khong phai tong diem co trong so: ban ve ghi
 * "6 / 6", "4 / 6", "3 / 6".
 */
class EligibilityScoreService
{
    /** Nguong mac dinh cua luat khoang cach, km (quan tri sua duoc). */
    private const NHA_CACH_VIEC_TOI_THIEU = 30;
    private const VIEC_CACH_DU_AN_TOI_DA = 30;

    /**
     * @param  \Illuminate\Support\Collection  $cauHoi  cac buoc dang phat hanh
     * @param  array<int,string>  $traLoi  cauHoiId => gia tri da chon
     * @param  array<int,int>  $duongDi  cac buoc nguoi dung that su di qua
     * @param  array  $diaChi  khoi dia chi o buoc Nha o
     */
    public function cham($cauHoi, array $traLoi, array $duongDi, array $diaChi): array
    {
        $km = $this->khoangCach($diaChi);
        $tieuChi = [];
        $dat = $chuaRo = $khongDat = 0;

        foreach ($this->danhSachTieuChi() as $tc) {
            $ketLuan = $this->ketLuanCua($tc, $cauHoi, $traLoi, $duongDi, $km);

            $tieuChi[] = [
                'label' => $tc->label,
                'verdict' => $ketLuan,
                'text' => $tc->moTa($ketLuan),
            ];

            match ($ketLuan) {
                'pass' => $dat++,
                'fail' => $khongDat++,
                default => $chuaRo++,
            };
        }

        $tong = count($tieuChi);

        return [
            'tieuChi' => $tieuChi,
            'tong' => $tong,
            'dat' => $dat,
            'chuaRo' => $chuaRo,
            'khongDat' => $khongDat,
            'phanTram' => $tong > 0 ? (int) round($dat / $tong * 100) : 0,
            'muc' => $khongDat > 0 ? 'low' : ($chuaRo > 0 ? 'medium' : 'high'),
            'khoangCach' => $km,
        ];
    }

    /** Cac tieu chi dang phat hanh, dung thu tu tren ban ve. */
    public function danhSachTieuChi()
    {
        return EligibilityCriterion::where('publish', 2)
            ->orderBy('order')->orderBy('id')->get();
    }

    // -------------------------------------------------------------------------

    /**
     * Hai khoang cach cua luat muc 3.
     *
     * @return array{nhaViec: ?float, viecDuAn: ?float}
     */
    public function khoangCach(array $diaChi): array
    {
        $nha = NoxhKhoangCach::toaDo($diaChi['province_code'] ?? null, $diaChi['ward_code'] ?? null);
        $viec = NoxhKhoangCach::toaDo($diaChi['work_province_code'] ?? null, $diaChi['work_ward_code'] ?? null);

        return [
            'nhaViec' => NoxhKhoangCach::km($nha, $viec),
            'viecDuAn' => NoxhKhoangCach::ganNhat($viec, (array) ($diaChi['project_ids'] ?? [])),
        ];
    }

    private function ketLuanCua($tc, $cauHoi, array $traLoi, array $duongDi, array $km): string
    {
        if ($tc->source === 'fixed') {
            return 'pass';
        }

        if ($tc->source === 'area') {
            return $this->ketLuanKhuVuc($km);
        }

        $cau = $cauHoi->firstWhere('id', $tc->eligibility_question_id);

        if (!$cau) {
            return 'unclear';
        }

        // Buoc bi bo qua thi nguoi dung chua bao gio nhin thay cau hoi do -
        // khong the ket luan ho khong dat.
        $so = $cauHoi->search(fn ($c) => $c->id === $cau->id) + 1;

        if ($duongDi && !in_array($so, $duongDi, true)) {
            return 'unclear';
        }

        $gia = $traLoi[$cau->id] ?? null;

        if ($gia === null || $gia === '') {
            return 'unclear';
        }

        $dapAn = $cau->options->firstWhere('value', $gia);

        if (!$dapAn) {
            return 'unclear';
        }

        // Dap an tich "Xet theo khoang cach": muc 3 cua cong thuc - dang co
        // nha o VAN duoc mua neu nha cach noi lam viec >= 30km va noi lam
        // viec cach du an <= 30km.
        if ($dapAn->needs_distance) {
            return $this->ketLuanTheoKhoangCach($km);
        }

        return (string) $dapAn->verdict;
    }

    private function ketLuanKhuVuc(array $km): string
    {
        if ($km['viecDuAn'] === null) {
            return 'unclear';
        }

        return $km['viecDuAn'] <= $this->nguong('dist_work_project_max', self::VIEC_CACH_DU_AN_TOI_DA)
            ? 'pass'
            : 'fail';
    }

    private function ketLuanTheoKhoangCach(array $km): string
    {
        // Thieu mot trong hai so do thi khong ket luan duoc - bao can xac
        // minh chu khong danh truot nguoi ta vi ho chua khai dia chi.
        if ($km['nhaViec'] === null || $km['viecDuAn'] === null) {
            return 'unclear';
        }

        $du = $km['nhaViec'] >= $this->nguong('dist_home_work_min', self::NHA_CACH_VIEC_TOI_THIEU)
            && $km['viecDuAn'] <= $this->nguong('dist_work_project_max', self::VIEC_CACH_DU_AN_TOI_DA);

        return $du ? 'pass' : 'fail';
    }

    /** Nguong km lay tu o cau hinh, khong co thi dung so mac dinh. */
    private function nguong(string $o, float $mac): float
    {
        $gia = trim((string) (NoxhComposer::intro()['wizard_' . $o] ?? ''));

        return is_numeric($gia) ? (float) $gia : $mac;
    }
}
