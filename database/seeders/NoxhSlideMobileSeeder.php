<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Slide banner doc cho dien thoai - nhom 'mobile-slide'.
 *
 * VI SAO CO BANG slides MA KHONG DUNG O CHU:
 * Anh banner may tinh nam trong Cau hinh -> Gioi thieu (khoa hero_image), hop
 * voi MOT anh co dinh. Anh banner dien thoai can thay bang anh khac, co the
 * la nhieu anh mai ve sau, nen de trong bang slides theo dung quy uoc cua du
 * an: cot keyword phan nhom, moi ban ghi la mot anh.
 *
 * Trang chu doc theo thu tu: slide nhom nay -> roi moi den o hero_image_mobile
 * trong Gioi thieu -> cuoi cung la anh may tinh. Nho vay doi anh o dau cung
 * duoc, va xoa ban ghi nay thi trang van chay binh thuong.
 *
 * Chay lai duoc nhieu lan: da co ban ghi cung nhom thi cap nhat lai, khong them.
 *
 * Chay: php artisan db:seed --class=NoxhSlideMobileSeeder --force
 */
class NoxhSlideMobileSeeder extends Seeder
{
    private const LANG = 1;

    /**
     * Cap anh banner cho trang chu, sinh bang tools/lam-anh-banner.py.
     *
     *   hero_image        - anh ngang cho man hinh rong
     *   hero_image_mobile - anh doc cho dien thoai
     *
     * Anh cu (du-an-mac-dinh.jpg 1200x324 va du-an-mac-dinh-doc.jpg 900x1900)
     * van con trong public/uploads/noxh de lui lai neu can.
     */
    private const ANH_PC = '/uploads/noxh/banner-pc.jpg';
    private const ANH_MOBILE = '/uploads/noxh/banner-mobile.jpg';

    public function run(): void
    {
        $this->command?->newLine();
        $this->command?->info('=== Slide banner dien thoai ===');

        $this->ganAnhBanner();
        $this->taoSlide();
    }

    /** Gan lai anh banner cho ca may tinh lan dien thoai. */
    private function ganAnhBanner(): void
    {
        foreach (['hero_image' => self::ANH_PC, 'hero_image_mobile' => self::ANH_MOBILE] as $khoa => $anh) {
            if (! is_file(public_path(ltrim($anh, '/')))) {
                $this->command?->warn('  Khong thay anh: ' . $anh . ' - bo qua ' . $khoa);

                continue;
            }

            $co = DB::table('introduces')->where('keyword', $khoa)->where('language_id', self::LANG)->exists();

            if ($co) {
                DB::table('introduces')->where('keyword', $khoa)->where('language_id', self::LANG)
                    ->update(['content' => $anh, 'updated_at' => now()]);
            } else {
                DB::table('introduces')->insert([
                    'keyword' => $khoa,
                    'language_id' => self::LANG,
                    'content' => $anh,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command?->line(sprintf('  %-18s = %s', $khoa, $anh));
        }
    }

    private function taoSlide(): void
    {
        $duongDan = public_path(ltrim(self::ANH_MOBILE, '/'));

        if (! is_file($duongDan)) {
            $this->command?->warn('  Khong thay anh slide: ' . $duongDan);

            return;
        }

        // Cau truc item giong SlideService: item[language][so thu tu] => cac cot.
        $item = [
            self::LANG => [
                [
                    'name' => 'Banner dien thoai',
                    'description' => 'Anh doc cho man hinh nho (toi da 1024px)',
                    'canonical' => '',
                    'alt' => 'Nha o xa hoi',
                    'image' => self::ANH_MOBILE,
                    'window' => '',
                ],
            ],
        ];

        $daCo = DB::table('slides')->where('keyword', 'mobile-slide')->first();

        if ($daCo) {
            DB::table('slides')->where('id', $daCo->id)->update([
                'name' => 'Banner dien thoai',
                'description' => 'Anh doc 900x1600 hien o dau trang chu tren man hinh toi da 1024px.',
                'item' => json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'publish' => 2,
                'updated_at' => now(),
            ]);

            $this->command?->line('  da cap nhat ban ghi #' . $daCo->id);

            return;
        }

        $id = DB::table('slides')->insertGetId([
            'name' => 'Banner dien thoai',
            'keyword' => 'mobile-slide',
            'description' => 'Anh doc 900x1600 hien o dau trang chu tren man hinh toi da 1024px.',
            'item' => json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'setting' => '',
            'publish' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command?->line('  da tao ban ghi #' . $id . ' voi tu khoa mobile-slide');
    }
}
