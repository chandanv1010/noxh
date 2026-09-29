<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Toa do tam cua 34 tinh/thanh sau sap xep hanh chinh nam 2025.
 *
 * Khoi "Ban do du an" cham ghim theo hai so nay. API
 * provinces.open-api.vn/api/v2 chi tra ve ten va ma, khong co toa do, nen
 * phan nay phai nap rieng.
 *
 * Diem lay la TRUNG TAM HANH CHINH cua tinh sau sap nhap (khong phai trong
 * tam hinh hoc) - ghim tren ban do teaser can roi vao dung thanh pho tinh ly
 * ma nguoi xem nhan ra, khong phai giua rung nui.
 *
 * Chi ghi vao dong nao dang trong: quan tri sua tay toa do roi thi giu nguyen.
 * Muon nap de len thi chay voi --force va bien moi truong NOXH_TOA_DO_GHI_DE=1.
 *
 * Chay:  php artisan db:seed --class=NoxhProvinceCoordSeeder --force
 */
class NoxhProvinceCoordSeeder extends Seeder
{
    /** ma tinh => [vi do, kinh do] */
    private const TOA_DO = [
        '01' => [21.028500, 105.854200],  // Ha Noi
        '04' => [22.665600, 106.257800],  // Cao Bang
        '08' => [21.823200, 105.214000],  // Tuyen Quang (+ Ha Giang)
        '11' => [21.386400, 103.017500],  // Dien Bien
        '12' => [22.396500, 103.458800],  // Lai Chau
        '14' => [21.327300, 103.914100],  // Son La
        '15' => [21.705000, 104.870000],  // Lao Cai (+ Yen Bai)
        '19' => [21.594200, 105.848100],  // Thai Nguyen (+ Bac Kan)
        '20' => [21.853500, 106.761300],  // Lang Son
        '22' => [21.006400, 107.292500],  // Quang Ninh
        '24' => [21.181000, 106.070000],  // Bac Ninh (+ Bac Giang)
        '25' => [21.323000, 105.402000],  // Phu Tho (+ Vinh Phuc, Hoa Binh)
        '31' => [20.864800, 106.683800],  // Hai Phong (+ Hai Duong)
        '33' => [20.646400, 106.051100],  // Hung Yen (+ Thai Binh)
        '37' => [20.254100, 105.974800],  // Ninh Binh (+ Ha Nam, Nam Dinh)
        '38' => [19.806700, 105.776400],  // Thanh Hoa
        '40' => [18.679100, 105.681400],  // Nghe An
        '42' => [18.342800, 105.905700],  // Ha Tinh
        '44' => [17.468000, 106.622000],  // Quang Tri (+ Quang Binh)
        '46' => [16.463400, 107.590900],  // Hue
        '48' => [16.047900, 108.206200],  // Da Nang (+ Quang Nam)
        '51' => [15.120500, 108.804400],  // Quang Ngai (+ Kon Tum)
        '52' => [13.782000, 109.219000],  // Gia Lai (+ Binh Dinh) - TT Quy Nhon
        '56' => [12.238800, 109.196700],  // Khanh Hoa (+ Ninh Thuan)
        '66' => [12.710000, 108.237800],  // Dak Lak (+ Phu Yen)
        '68' => [11.940400, 108.458300],  // Lam Dong (+ Dak Nong, Binh Thuan)
        '75' => [10.957300, 106.842600],  // Dong Nai (+ Binh Phuoc)
        '79' => [10.776000, 106.700800],  // TP Ho Chi Minh (+ Binh Duong, BR-VT)
        '80' => [11.310500, 106.098000],  // Tay Ninh (+ Long An)
        '82' => [10.455900, 105.633600],  // Dong Thap (+ Tien Giang)
        '86' => [10.253700, 105.972200],  // Vinh Long (+ Ben Tre, Tra Vinh)
        '91' => [10.386000, 105.436000],  // An Giang (+ Kien Giang)
        '92' => [10.045200, 105.746900],  // Can Tho (+ Soc Trang, Hau Giang)
        '96' => [9.176800, 105.150500],   // Ca Mau (+ Bac Lieu)
    ];

    public function run(): void
    {
        $ghiDe = env('NOXH_TOA_DO_GHI_DE') == 1;
        $dem = 0;

        foreach (self::TOA_DO as $ma => [$lat, $lng]) {
            $q = DB::table('vn_provinces')->where('code', $ma);

            if (!$ghiDe) {
                $q->where(function ($w) {
                    $w->whereNull('lat')->orWhereNull('lng');
                });
            }

            $dem += $q->update(['lat' => $lat, 'lng' => $lng]);
        }

        $thieu = DB::table('vn_provinces')->whereNull('lat')->count();

        $this->command?->info("Da nap toa do cho {$dem} tinh/thanh.");

        if ($thieu > 0) {
            $this->command?->warn("Con {$thieu} tinh/thanh chua co toa do - ghim se khong hien tren ban do.");
        }
    }
}
