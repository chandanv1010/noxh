<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Nap don vi hanh chinh Viet Nam - bo HAI cap moi.
 *
 * Tu 01/07/2025 Viet Nam bo cap quan/huyen. Cau truc gio chi con:
 *   tinh/thanh (34)  ->  xa/phuong/dac khu (3.321)
 *
 * Bo du lieu cu trong CSDL nay (63 tinh, 705 quan huyen, 10.598 phuong xa) vua
 * lac hau vua sai cau truc.
 *
 * Nguon: github.com/ThangLeQuoc/vietnamese-provinces-database
 *        json/vn_only_simplified_json_generated_data_vn_units.json
 *        theo Nghi quyet 30/2026/QH16
 *
 * File JSON duoc luu vao database/data/vn_units.json de seed lai duoc tren may
 * chu ma khong can mang, va de biet chinh xac ban du lieu nao dang dung.
 *
 * Chay:  php artisan db:seed --class=VnAdministrativeUnitSeeder --force
 * Chay lai nhieu lan khong sao: xoa het roi nap lai.
 */
class VnAdministrativeUnitSeeder extends Seeder
{
    private const DATA_FILE = 'data/vn_units.json';

    public function run(): void
    {
        $path = database_path(self::DATA_FILE);

        if (!is_file($path)) {
            $this->command->error('  Khong thay file du lieu: ' . $path);
            return;
        }

        $raw = json_decode(file_get_contents($path), true);
        if (!is_array($raw) || $raw === []) {
            $this->command->error('  File du lieu khong doc duoc hoac rong.');
            return;
        }

        $this->command->newLine();
        $this->command->info('=== Nap don vi hanh chinh Viet Nam (2 cap) ===');

        // Giu lai toa do truoc khi xoa bang.
        //
        // Hai cot lat/lng KHONG co trong file JSON goc: toa do tinh do
        // NoxhProvinceCoordSeeder dat, toa do phuong/xa do
        // `php artisan noxh:toa-do --xa` tra cuu tung cai mot qua
        // Nominatim - mat gan muoi phut cho moi tinh. Xoa trang roi nap lai
        // ma khong giu thi ban do du an va luat "cach noi lam viec >= 30km"
        // deu mat do chinh xac, ma khong ai nhan ra ngay.
        $toaDoTinh = DB::table('vn_provinces')->whereNotNull('lat')
            ->pluck('lng', 'code')->toArray();
        $toaDoTinhLat = DB::table('vn_provinces')->whereNotNull('lat')
            ->pluck('lat', 'code')->toArray();
        $toaDoXa = DB::table('vn_wards')->whereNotNull('lat')
            ->pluck('lng', 'code')->toArray();
        $toaDoXaLat = DB::table('vn_wards')->whereNotNull('lat')
            ->pluck('lat', 'code')->toArray();

        // Tat kiem tra khoa ngoai de xoa duoc bang cha truoc.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('vn_wards')->truncate();
        DB::table('vn_provinces')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        $provinces = [];
        $wards = [];

        foreach (array_values($raw) as $i => $p) {
            $code = (string) ($p['Code'] ?? '');
            if ($code === '') {
                continue;
            }

            $provinces[] = [
                'code' => $code,
                'name' => (string) ($p['FullName'] ?? ''),
                'postal_code_prefix' => $p['PostalCodePrefix'] ?? null,
                'order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach (array_values($p['Wards'] ?? []) as $j => $w) {
                $wCode = (string) ($w['Code'] ?? '');
                if ($wCode === '') {
                    continue;
                }

                $wards[] = [
                    'code' => $wCode,
                    'name' => (string) ($w['FullName'] ?? ''),
                    // Uu tien ProvinceCode cua chinh ban ghi xa, khong suy ra tu
                    // vong lap - de neu bo du lieu co xa gan tinh khac thi van dung.
                    'province_code' => (string) ($w['ProvinceCode'] ?? $code),
                    'postal_code' => $w['PostalCode'] ?? null,
                    'order' => $j,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('vn_provinces')->insert($provinces);

        // Chia lo: 3.321 dong mot lenh INSERT se vuot max_allowed_packet tren
        // nhieu cau hinh MySQL mac dinh.
        foreach (array_chunk($wards, 500) as $chunk) {
            DB::table('vn_wards')->insert($chunk);
        }

        // Tra lai toa do cho nhung ma VAN CON trong ban du lieu moi. Ma nao
        // bi bo (sap xep lai don vi hanh chinh) thi toa do cu cung het nghia.
        $traLai = $this->traToaDo('vn_provinces', $toaDoTinhLat, $toaDoTinh)
            + $this->traToaDo('vn_wards', $toaDoXaLat, $toaDoXa);

        $this->command->line(sprintf('  tinh/thanh    : %d', count($provinces)));
        $this->command->line(sprintf('  xa/phuong     : %d', count($wards)));
        $this->command->line(sprintf('  toa do giu lai: %d', $traLai));

        // Doi chieu lai voi con so trong CSDL, khong tin vao bien dem.
        $pDb = DB::table('vn_provinces')->count();
        $wDb = DB::table('vn_wards')->count();
        $orphan = DB::table('vn_wards as w')
            ->leftJoin('vn_provinces as p', 'p.code', '=', 'w.province_code')
            ->whereNull('p.code')
            ->count();

        $this->command->newLine();
        $this->command->line(sprintf('  trong CSDL    : %d tinh, %d xa', $pDb, $wDb));
        $this->command->line(sprintf('  xa mo coi     : %d', $orphan));

        if ($pDb === 34 && $orphan === 0) {
            $this->command->info('  OK - dung 34 tinh/thanh, khong co xa mo coi.');
        } else {
            $this->command->warn('  CAN KIEM LAI - so lieu khong nhu mong doi.');
        }

        $this->command->newLine();
    }

    /**
     * Ghi lai toa do da luu truoc do cho cac ma con ton tai.
     *
     * @param  array<string,mixed>  $lat  ma => vi do
     * @param  array<string,mixed>  $lng  ma => kinh do
     */
    private function traToaDo(string $bang, array $lat, array $lng): int
    {
        $xong = 0;

        foreach ($lat as $ma => $vi) {
            if (!isset($lng[$ma])) {
                continue;
            }

            $xong += DB::table($bang)->where('code', $ma)
                ->update(['lat' => $vi, 'lng' => $lng[$ma]]);
        }

        return $xong;
    }
}
