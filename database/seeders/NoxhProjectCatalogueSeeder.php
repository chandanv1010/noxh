<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Noi lai danh muc cho MOI du an trong CSDL.
 *
 * VI SAO CAN:
 * Trang quan tri /product/index lay danh sach bang query co
 * `INNER JOIN product_catalogue_product`. Du an nao khong co dong lien ket
 * trong bang do thi KHONG BAO GIO hien ra o trang quan tri - du web ngoai van
 * hien binh thuong, vi ProjectQuery chi join product_language. Do la mot loi da
 * gap that, va no rat de gay hieu nham: "bai nay khong thay trong admin".
 *
 * BAN CU CUA SEEDER NAY CHI XU LY 6 DU AN MAU:
 * no liet ke cung 6 canonical trong mot mang hang so. Du an nao them sau - do
 * quan tri tu tao, hoac do ban dung CSDL khac - khong nam trong mang do nen
 * KHONG BAO GIO duoc noi danh muc. Dung loi nay khi mot du an that (vi du
 * noxh-machino-elite-phu-xuan) bien mat khoi trang quan tri.
 * Ban hien tai bo han mang hang so: quet TAT CA san pham.
 *
 * Seeder nay chay lai duoc nhieu lan va KHONG de len du lieu quan tri da sua tay:
 *   - san pham da tro dung vao mot danh muc CON TON TAI  -> giu nguyen
 *   - san pham chua co danh muc, hoac tro vao id da bi xoa (mo coi)
 *     -> gan vao danh muc dau tien
 *   - dong lien ket da co -> khong them ban trung
 *
 * Chay: php artisan db:seed --class=NoxhProjectCatalogueSeeder --force
 */
class NoxhProjectCatalogueSeeder extends Seeder
{
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

        $idsDanhMucConTonTai = array_map('intval', DB::table('product_catalogues')->pluck('id')->all());

        // Ten hien thi de bao cao cho de hieu, khong phai id kho khan.
        $tenTheoSanPham = DB::table('product_language')
            ->where('language_id', self::LANG)
            ->pluck('name', 'product_id');

        $sanPham = DB::table('products')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'product_catalogue_id']);

        $ganMoi = 0;
        $suaMoCoi = 0;
        $themLienKet = 0;

        foreach ($sanPham as $sp) {
            $hienTai = $sp->product_catalogue_id;
            $hopLe = in_array((int) $hienTai, $idsDanhMucConTonTai, true);

            if (! $hopLe) {
                DB::table('products')
                    ->where('id', $sp->id)
                    ->update(['product_catalogue_id' => $danhMuc, 'updated_at' => now()]);

                $ten = $tenTheoSanPham[$sp->id] ?? ('#' . $sp->id);

                // Cot products.product_catalogue_id la int NOT NULL default 0, nen
                // "chua gan" hien ra la so 0 chu khong phai NULL. Van de phong
                // truong hop NULL o ban CSDL khac.
                if ($hienTai === null || (int) $hienTai === 0) {
                    $ganMoi++;
                    $this->command?->line(sprintf('    chua gan danh muc   : %s', $ten));
                } else {
                    $suaMoCoi++;
                    $this->command?->line(sprintf(
                        '    id danh muc mo coi : %s tro vao %s -> %d',
                        $ten,
                        var_export($hienTai, true),
                        $danhMuc
                    ));
                }
            }

            // Dong lien ket la thu trang quan tri dung de liet ke. Chi them khi
            // chua co, de khong tao ban trung.
            $coLienKet = DB::table('product_catalogue_product')
                ->where('product_id', $sp->id)
                ->where('product_catalogue_id', $danhMuc)
                ->exists();

            if (! $coLienKet) {
                DB::table('product_catalogue_product')->insert([
                    'product_id' => $sp->id,
                    'product_catalogue_id' => $danhMuc,
                ]);

                $themLienKet++;
                $this->command?->line(sprintf(
                    '    them lien ket      : %s',
                    $tenTheoSanPham[$sp->id] ?? ('#' . $sp->id)
                ));
            }
        }

        // Bao cao them: san pham nao khong nam trong danh muc nao. Con so nay
        // phai bang 0 sau khi chay - khac 0 nghia la trang quan tri van se thieu.
        $khongThuocDanhMuc = DB::table('products')
            ->leftJoin('product_catalogue_product as pcp', 'pcp.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->whereNull('pcp.product_id')
            ->count();

        $tongSanPham = $sanPham->count();
        $tongLienKet = DB::table('product_catalogue_product')->count();

        $this->command?->line(sprintf('  tong du an           : %d', $tongSanPham));
        $this->command?->line(sprintf('  gan danh muc moi     : %d', $ganMoi));
        $this->command?->line(sprintf('  sua id mo coi        : %d', $suaMoCoi));
        $this->command?->line(sprintf('  them dong lien ket   : %d', $themLienKet));
        $this->command?->line(sprintf('  tong dong lien ket   : %d', $tongLienKet));
        $this->command?->line(sprintf(
            '  con sot (phai = 0)   : %d %s',
            $khongThuocDanhMuc,
            $khongThuocDanhMuc === 0 ? '' : '<-- se khong hien o trang quan tri'
        ));
    }
}
