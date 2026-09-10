<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Xoa noi dung danh muc cua du an truoc, don cho NOXH.
 *
 * PHAM VI XOA - chi noi dung san pham va thuoc tinh:
 *   products (69 - camera / phu kien o to) + product_language
 *   product_catalogue_product, product_attribute
 *   product_catalogues (7) + product_catalogue_language
 *   attributes (24 - Giong nho, Nong do con... con sot tu ma nguon ruou vang)
 *     + attribute_language, attribute_catalogue_attribute
 *   attribute_catalogues (7) + attribute_catalogue_language
 *   routers tro toi ProductController / ProductCatalogueController
 *
 * GIU LAI lam mau, dung nhu yeu cau:
 *   posts (17) + post_catalogues (3) - cau truc tin tuc
 *   menus, systems, widgets, slides
 *   users, user_catalogues, permissions
 *   vn_provinces, vn_wards
 *
 * KHONG dung soft delete ma xoa han: day la du lieu cua mot website khac,
 * giu lai chi lam nhieu du lieu va lam sai cac phep dem trong admin.
 *
 * SAO LUU TRUOC KHI CHAY:
 *   mysqldump -uroot -p sql_noxh > noxh_backup.sql
 *
 * Chay: php artisan db:seed --class=RemoveLegacyCatalogDataSeeder --force
 * Chay lai nhieu lan khong sao: lan hai se bao khong con gi de xoa.
 */
class RemoveLegacyCatalogDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('=== Xoa noi dung danh muc cu ===');

        $before = $this->counts();

        if (array_sum($before) === 0) {
            $this->command->line('  Khong con gi de xoa.');
            $this->command->newLine();
            return;
        }

        $this->command->newLine();
        $this->command->line('  Truoc khi xoa:');
        foreach ($before as $table => $n) {
            $this->command->line(sprintf('    %-32s %6d', $table, $n));
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::transaction(function () {
            // Bang noi truoc, bang chinh sau.
            DB::table('product_attribute')->delete();
            DB::table('product_catalogue_product')->delete();
            DB::table('product_variant_attribute')->delete();
            DB::table('product_variant_language')->delete();
            DB::table('product_variants')->delete();
            DB::table('product_language')->delete();
            DB::table('products')->delete();

            DB::table('product_catalogue_language')->delete();
            DB::table('product_catalogues')->delete();

            DB::table('attribute_catalogue_attribute')->delete();
            DB::table('attribute_language')->delete();
            DB::table('attributes')->delete();
            DB::table('attribute_catalogue_language')->delete();
            DB::table('attribute_catalogues')->delete();

            // Router tro toi cac module vua xoa, de lai se thanh lien ket chet.
            DB::table('routers')
                ->whereIn('controllers', [
                    'App\\Http\\Controllers\\Frontend\\ProductController',
                    'App\\Http\\Controllers\\Frontend\\ProductCatalogueController',
                ])
                ->delete();
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $after = $this->counts();

        $this->command->newLine();
        $this->command->line('  Sau khi xoa:');
        $conlai = 0;
        foreach ($after as $table => $n) {
            $conlai += $n;
            if ($n > 0) {
                $this->command->warn(sprintf('    %-32s %6d  <- con sot', $table, $n));
            }
        }

        if ($conlai === 0) {
            $this->command->info('    Tat ca ve 0.');
        }

        // Doi chieu phan GIU LAI de chac chan khong xoa nham.
        $this->command->newLine();
        $this->command->line('  Giu lai (phai khac 0):');
        foreach ([
            'posts' => 'posts',
            'post_catalogues' => 'post_catalogues',
            'menus' => 'menus',
            'systems' => 'systems',
            'widgets' => 'widgets',
            'users' => 'users',
            'vn_provinces' => 'vn_provinces',
            'vn_wards' => 'vn_wards',
        ] as $label => $table) {
            $n = DB::table($table)->count();
            $this->command->line(sprintf('    %-32s %6d%s', $label, $n, $n === 0 ? '   <- CANH BAO' : ''));
        }

        $this->command->newLine();
    }

    /** @return array<string,int> */
    private function counts(): array
    {
        $tables = [
            'products', 'product_language', 'product_catalogues',
            'product_catalogue_language', 'product_catalogue_product',
            'product_attribute', 'product_variants',
            'attributes', 'attribute_language', 'attribute_catalogues',
            'attribute_catalogue_language', 'attribute_catalogue_attribute',
        ];

        $out = [];
        foreach ($tables as $t) {
            $out[$t] = DB::table($t)->count();
        }

        $out['routers (san pham)'] = DB::table('routers')
            ->whereIn('controllers', [
                'App\\Http\\Controllers\\Frontend\\ProductController',
                'App\\Http\\Controllers\\Frontend\\ProductCatalogueController',
            ])
            ->count();

        return $out;
    }
}
