<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Cach viet so tien.
 *
 * Du an bat dong san co con so rat lon, viet day du chu so thi nguoi doc phai
 * dem hang moi biet la bao nhieu.
 */
class NoxhTienVietTest extends TestCase
{
    public static function soTien(): array
    {
        return [
            'hai muoi trieu' => [20000000, '20 triệu'],
            'hai lam trieu ruoi' => [25500000, '25,5 triệu'],
            'mot ty' => [1000000000, '1 tỷ'],
            'mot phay hai lam ty' => [1250000000, '1,25 tỷ'],
            'mot phay bon ty' => [1400000000, '1,4 tỷ'],
            'tram trieu' => [100000000, '100 triệu'],
            // Truong hop de sai: cat so 0 cuoi cua PHAN NGUYEN.
            'tam tram nam muoi nghin' => [850000, '850 nghìn'],
            'nam tram dong' => [500, '500 đồng'],
            'khong' => [0, '0 đồng'],
            'so am' => [-20000000, '-20 triệu'],
        ];
    }

    /** @dataProvider soTien */
    public function test_viet_so_tien_ra_chu($dong, string $mongDoi): void
    {
        $this->assertSame($mongDoi, tien_viet($dong));
    }

    public function test_khong_co_gia_tri_thi_tra_ve_chuoi_rong(): void
    {
        $this->assertSame('', tien_viet(null));
        $this->assertSame('', tien_viet(''));
    }

    public function test_dang_viet_tat(): void
    {
        $this->assertSame('25tr', tien_viet(25000000, true));
        $this->assertSame('1,25ty', tien_viet(1250000000, true));

        // "dong" khong co dang viet tat nen van giu khoang trang.
        $this->assertSame('500 đồng', tien_viet(500, true));
    }

    public function test_khoang_tien_cung_don_vi_chi_in_don_vi_mot_lan(): void
    {
        $this->assertSame('1,08 - 1,18 tỷ', khoang_tien(1075000000, 1180000000));
    }

    public function test_khoang_tien_khac_don_vi_thi_in_ca_hai(): void
    {
        $this->assertSame('950 triệu - 1,18 tỷ', khoang_tien(950000000, 1180000000));
    }

    public function test_khoang_tien_thieu_mot_dau(): void
    {
        $this->assertSame('20 triệu', khoang_tien(20000000, null));
        $this->assertSame('20 triệu', khoang_tien(null, 20000000));
        $this->assertSame('', khoang_tien(null, null));
    }

    public function test_hai_dau_bang_nhau_thi_khong_in_dau_gach(): void
    {
        $this->assertSame('20 triệu', khoang_tien(20000000, 20000000));
    }
}
