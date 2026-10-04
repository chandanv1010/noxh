<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Noi lai danh muc cho cac du an NOXH da co trong CSDL.
 *
 * VI SAO CAN SEEDER RIENG:
 * NoxhFrontendContentSeeder tao du an kem dong lien ket trong
 * product_catalogue_product, nhung no bo qua du an da ton tai
 * (`if product_language where canonical exists -> continue`). Ma
 * RemoveLegacyCatalogDataSeeder lai xoa sach product_catalogue_product va
 * ca product_catalogues. Hau qua: CSDL nao da chay seeder danh muc mot lan
 * roi chay lai seeder noi dung thi du an van con, nhung bang lien ket rong.
 *
 * Trang quan tri /product/index lay danh sach bang query co
 * `INNER JOIN product_catalogue_product`, nen bang lien ket rong lam trang
 * do hien ra TRONG - du web ngoai van hien du du an (ProjectQuery chi join
 * product_language). Do la loi da gap that.
 *
 * Seeder nay chay lai duoc nhieu lan va KHONG de len du lieu quan tri da
 * sua tay:
 *   - du an da tro dung vao mot danh muc CON TON TAI  -> giu nguyen
 *   - du an chua co danh muc, hoac dang tro vao id da bi xoa (mo coi)
 *     -> gan vao danh muc dau tien, dung quy uoc cua NoxhFrontendContentSeeder
 *
 * Chay: php artisan db:seed --class=NoxhProjectCatalogueSeeder --force
 */
class NoxhProjectCatalogueSeeder extends Seeder
{
    /**
     * Du an mau, nhan dien bang canonical trong product_language.
     * Trung voi danh sach trong NoxhFrontendContentSeeder::napDuAn().
     */
    private const DU_AN = [
        'noxh-tuc-duyen',
        'noxh-hong-tien',
        'noxh-evergreen-bac-giang',
        'noxh-iec-residences',
        'noxh-phuc-thinh',
        'noxh-song-cong',
    ];

    private const LANG = 1;

    public function run(): void
    {
        $this->command?->newLine();
        $this->command?->info('=== Noi lai danh muc cho du an ===');

        // Cung quy uoc chon danh muc nhu NoxhFrontendContentSeeder: danh muc
        // dau tien theo id. Khong hard-code id vi id khac nhau giua cac may.
        $danhMuc = DB::table('product_catalogues')->orderBy('id')->value('id');

        if (! $danhMuc) {
            $this->command?->warn('  Chua co danh muc nao trong product_catalogues.');
            $this->command?->warn('  Chay NoxhStarterDataSeeder truoc (hoac ca NoxhSeeder).');

            return;
        }

        $tenDanhMuc = DB::table('product_catalogue_language')
            ->where('product_catalogue_id', $danhMuc)
            ->where('language_id', self::LANG)
            ->value('name');

        $this->command?->line(sprintf('  danh muc dich        : #%d %s', $danhMuc, $tenDanhMuc));

        $idsDanhMucConTonTai = DB::table('product_catalogues')->pluck('id')->all();

        $daNoi = 0;
        $giuNguyen = 0;
        $suaMoCoi = 0;
        $khongThay = 0;

        foreach (self::DU_AN as $canonical) {
            $productId = DB::table('product_language')
                ->where('canonical', $canonical)
                ->where('language_id', self::LANG)
                ->value('product_id');

            if (! $productId) {
                $khongThay++;
                $this->command?->line(sprintf('    thieu du an        : %s', $canonical));

                continue;
            }

            $hienTai = DB::table('products')->where('id', $productId)->value('product_catalogue_id');
            $hopLe = in_array((int) $hienTai, array_map('intval', $idsDanhMucConTonTai), true);

            if ($hopLe && (int) $hienTai !== (int) $danhMuc) {
                // Quan tri da chu dong doi danh muc - khong dung vao.
                $giuNguyen++;
            } elseif (! $hopLe) {
                DB::table('products')
                    ->where('id', $productId)
                    ->update(['product_catalogue_id' => $danhMuc, 'updated_at' => now()]);

                if ($hienTai === null) {
                    $daNoi++;
                } else {
                    $suaMoCoi++;
                    $this->command?->line(sprintf(
                        '    id danh muc mo coi : #%d tro vao %s -> %d',
                        $productId,
                        var_export($hienTai, true),
                        $danhMuc
                    ));
                }
            }

            // Dong lien ket la thu trang quan tri dung de liet ke. Chi them khi
            // chua co, de khong tao ban trung.
            $coLienKet = DB::table('product_catalogue_product')
                ->where('product_id', $productId)
                ->where('product_catalogue_id', $danhMuc)
                ->exists();

            if (! $coLienKet) {
                DB::table('product_catalogue_product')->insert([
                    'product_id' => $productId,
                    'product_catalogue_id' => $danhMuc,
                ]);
            }
        }

        $tongLienKet = DB::table('product_catalogue_product')->count();

        $this->command?->line(sprintf('  du an gan danh muc   : %d', $daNoi));
        $this->command?->line(sprintf('  sua id mo coi        : %d', $suaMoCoi));
        $this->command?->line(sprintf('  giu nguyen (da dung) : %d', $giuNguyen));
        $this->command?->line(sprintf('  khong tim thay       : %d', $khongThay));
        $this->command?->line(sprintf('  tong dong lien ket   : %d', $tongLienKet));
    }
}
