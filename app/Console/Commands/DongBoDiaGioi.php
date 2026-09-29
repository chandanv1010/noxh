<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Nap don vi hanh chinh Viet Nam tu API chinh thuc vao hai bang
 * vn_provinces va vn_wards.
 *
 * Tu 01/07/2025 Viet Nam bo cap quan/huyen, chi con HAI cap:
 *     tinh / thanh pho  ->  phuong / xa / dac khu
 * nen API v2 khong con endpoint quan/huyen nua.
 *
 * Nguon: https://provinces.open-api.vn/api/v2/  (tai lieu: .../redoc)
 *
 *     php artisan noxh:dia-gioi              # nap, chi them va cap nhat
 *     php artisan noxh:dia-gioi --xoa-thua   # xoa luon don vi khong con
 *     php artisan noxh:dia-gioi --thu        # chi xem se doi gi, khong ghi
 *
 * Ma vung trong API la SO (1, 4, 24). Trong CSDL luu thanh chuoi co so 0 o
 * dau (01, 04, 24 / 00004) dung chuan cua Tong cuc Thong ke, vi bang
 * products va cac bang cu deu dang luu theo dang do.
 */
class DongBoDiaGioi extends Command
{
    protected $signature = 'noxh:dia-gioi
                            {--nguon=https://provinces.open-api.vn/api/v2/ : Dia chi API}
                            {--xoa-thua : Xoa don vi khong con trong API}
                            {--thu : Chi in ra se doi gi, khong ghi vao CSDL}';

    protected $description = 'Nap tinh/thanh va phuong/xa tu API provinces.open-api.vn';

    public function handle(): int
    {
        $nguon = rtrim((string) $this->option('nguon'), '/') . '/?depth=2';
        $thu = (bool) $this->option('thu');

        $this->info('Dang tai ' . $nguon);

        try {
            // depth=2 tra ve ca phuong/xa long trong tung tinh: mot lan goi
            // thay cho 35 lan. Goi mat khoang mot giay, file chung 1 MB.
            $phanHoi = Http::timeout(120)->acceptJson()->get($nguon);
        } catch (\Throwable $e) {
            $this->error('Khong goi duoc API: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (!$phanHoi->successful()) {
            $this->error('API tra ve ma ' . $phanHoi->status());
            return self::FAILURE;
        }

        $duLieu = $phanHoi->json();

        if (!is_array($duLieu) || count($duLieu) < 30) {
            // Ca nuoc co 34 tinh/thanh. Nhan ve it hon nhieu tuc la API loi
            // hoac doi dinh dang - dung lai chu khong ghi de len du lieu tot.
            $this->error('API tra ve ' . (is_array($duLieu) ? count($duLieu) : 0)
                . ' tinh/thanh, khong dung. Da dung lai, CSDL giu nguyen.');
            return self::FAILURE;
        }

        [$tinh, $xa] = $this->doc($duLieu);

        $this->line(sprintf('Nhan duoc %d tinh/thanh va %d phuong/xa.', count($tinh), count($xa)));

        if ($thu) {
            $this->soSanh($tinh, $xa);
            $this->warn('Che do --thu: khong ghi gi vao CSDL.');
            return self::SUCCESS;
        }

        $this->ghi($tinh, $xa);

        if ($this->option('xoa-thua')) {
            $this->xoaThua($tinh, $xa);
        } else {
            $this->baoThua($tinh, $xa);
        }

        $this->info('Xong.');

        return self::SUCCESS;
    }

    /** Doi JSON cua API thanh hai mang san sang ghi vao bang. */
    private function doc(array $duLieu): array
    {
        $tinh = [];
        $xa = [];
        $thuTuTinh = 0;

        foreach ($duLieu as $t) {
            $maTinh = $this->ma($t['code'] ?? null, 2);

            if ($maTinh === null || empty($t['name'])) {
                continue;
            }

            $tinh[$maTinh] = [
                'code' => $maTinh,
                'name' => $t['name'],
                'division_type' => $t['division_type'] ?? null,
                'codename' => $t['codename'] ?? null,
                'phone_code' => isset($t['phone_code']) ? (string) $t['phone_code'] : null,
                'order' => $thuTuTinh++,
            ];

            $thuTuXa = 0;

            foreach ($t['wards'] ?? [] as $x) {
                $maXa = $this->ma($x['code'] ?? null, 5);

                if ($maXa === null || empty($x['name'])) {
                    continue;
                }

                $xa[$maXa] = [
                    'code' => $maXa,
                    'name' => $x['name'],
                    'division_type' => $x['division_type'] ?? null,
                    'codename' => $x['codename'] ?? null,
                    'province_code' => $maTinh,
                    'order' => $thuTuXa++,
                ];
            }
        }

        return [$tinh, $xa];
    }

    /** Ma so cua API -> chuoi co so 0 o dau, dung chuan Tong cuc Thong ke. */
    private function ma($gpt, int $doDai): ?string
    {
        if ($gpt === null || $gpt === '' || !is_numeric($gpt)) {
            return null;
        }

        return str_pad((string) (int) $gpt, $doDai, '0', STR_PAD_LEFT);
    }

    private function ghi(array $tinh, array $xa): void
    {
        // Ghi tinh TRUOC: vn_wards.province_code co khoa ngoai tro sang
        // vn_provinces, ghi xa truoc se bi tu choi.
        DB::transaction(function () use ($tinh, $xa) {
            foreach (array_chunk($tinh, 100) as $lo) {
                DB::table('vn_provinces')->upsert(
                    array_map(fn ($d) => $d + ['updated_at' => now(), 'created_at' => now()], $lo),
                    ['code'],
                    ['name', 'division_type', 'codename', 'phone_code', 'order', 'updated_at']
                );
            }

            foreach (array_chunk($xa, 500) as $lo) {
                DB::table('vn_wards')->upsert(
                    array_map(fn ($d) => $d + ['updated_at' => now(), 'created_at' => now()], $lo),
                    ['code'],
                    ['name', 'division_type', 'codename', 'province_code', 'order', 'updated_at']
                );
            }
        });

        $this->line('Da ghi xong tinh/thanh va phuong/xa.');
    }

    /** Don vi con trong CSDL nhung khong con trong API. */
    private function thua(array $tinh, array $xa): array
    {
        return [
            DB::table('vn_provinces')->whereNotIn('code', array_keys($tinh))->pluck('name', 'code'),
            DB::table('vn_wards')->whereNotIn('code', array_keys($xa))->pluck('name', 'code'),
        ];
    }

    private function baoThua(array $tinh, array $xa): void
    {
        [$tinhThua, $xaThua] = $this->thua($tinh, $xa);

        if ($tinhThua->isEmpty() && $xaThua->isEmpty()) {
            return;
        }

        $this->warn(sprintf(
            'Con %d tinh/thanh va %d phuong/xa khong co trong API. Chay lai voi --xoa-thua de xoa.',
            $tinhThua->count(),
            $xaThua->count()
        ));
    }

    private function xoaThua(array $tinh, array $xa): void
    {
        [$tinhThua, $xaThua] = $this->thua($tinh, $xa);

        // Khong xoa don vi dang co du an tro toi: xoa di la du an mat dia chi.
        $dangDung = DB::table('products')->whereNotNull('ward_code')->distinct()->pluck('ward_code')->all();
        $giuLai = $xaThua->keys()->intersect($dangDung);

        if ($giuLai->isNotEmpty()) {
            $this->warn('Giu lai ' . $giuLai->count() . ' phuong/xa vi dang co du an tro toi: '
                . $giuLai->implode(', '));
        }

        $xoaXa = $xaThua->keys()->diff($dangDung);

        if ($xoaXa->isNotEmpty()) {
            DB::table('vn_wards')->whereIn('code', $xoaXa)->delete();
            $this->line('Da xoa ' . $xoaXa->count() . ' phuong/xa cu.');
        }

        $dangDungTinh = DB::table('products')->whereNotNull('province_code')->distinct()->pluck('province_code')->all();
        $xoaTinh = $tinhThua->keys()->diff($dangDungTinh);

        if ($xoaTinh->isNotEmpty()) {
            DB::table('vn_provinces')->whereIn('code', $xoaTinh)->delete();
            $this->line('Da xoa ' . $xoaTinh->count() . ' tinh/thanh cu.');
        }
    }

    private function soSanh(array $tinh, array $xa): void
    {
        $tinhCu = DB::table('vn_provinces')->pluck('name', 'code');
        $xaCu = DB::table('vn_wards')->pluck('name', 'code');

        $them = collect($tinh)->keys()->diff($tinhCu->keys());
        $doiTen = collect($tinh)->filter(fn ($t, $ma) => isset($tinhCu[$ma]) && $tinhCu[$ma] !== $t['name']);

        $this->line(sprintf('Tinh/thanh: them %d, doi ten %d, thua %d',
            $them->count(), $doiTen->count(), $tinhCu->keys()->diff(collect($tinh)->keys())->count()));

        foreach ($doiTen as $ma => $t) {
            $this->line(sprintf('   %s: "%s" -> "%s"', $ma, $tinhCu[$ma], $t['name']));
        }

        $this->line(sprintf('Phuong/xa: them %d, thua %d',
            collect($xa)->keys()->diff($xaCu->keys())->count(),
            $xaCu->keys()->diff(collect($xa)->keys())->count()));
    }
}
