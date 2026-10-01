<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Trang Ban do du an (/du-an/ban-do).
 *
 * Nap ba thu:
 *   1. Chu cua trang - module Gioi thieu, nhom "Khoi 4c".
 *   2. Cai dat nen ban do - Cau hinh he thong, nhom "Ban do du an". Mac dinh
 *      la OpenStreetMap: mien phi, khong can khai bao gi. O "Google Maps API
 *      key" CO Y de trong - key la cua chu trang, tu vao Google Cloud tao roi
 *      dan vao, khong ai dan ho duoc.
 *   3. Gan phuong/xa cho cac du an mau, de o loc "Phuong/Xa" co cai ma loc.
 *
 * Seeder CHI THEM o con trong: chay lai khong de len thu quan tri da sua.
 *
 *     php artisan db:seed --class=NoxhProjectMapSeeder --force
 */
class NoxhProjectMapSeeder extends Seeder
{
    public function run(): void
    {
        $this->napChu();
        $this->napCaiDat();
        $this->napPhuongXa();
    }

    /** Chu cua trang ban do - module Gioi thieu. */
    private function napChu(): void
    {
        $chu = [
            'projectmap_heading' => 'Bản đồ dự án nhà ở xã hội',
            'projectmap_description' => "Xem vị trí thật của từng dự án trên bản đồ.\nBấm vào ghim để xem nhanh thông tin, bấm tiếp để mở trang chi tiết dự án.",

            'projectmap_filter_province_label' => 'Tỉnh / Thành phố',
            'projectmap_filter_province_all' => 'Toàn quốc',
            'projectmap_filter_ward_label' => 'Phường / Xã',
            'projectmap_filter_ward_all' => 'Tất cả phường / xã',
            'projectmap_filter_ward_empty' => 'Chọn tỉnh/thành trước',
            'projectmap_filter_status_label' => 'Trạng thái',
            'projectmap_filter_status_all' => 'Tất cả trạng thái',
            'projectmap_filter_keyword_label' => 'Từ khoá',
            'projectmap_filter_keyword_placeholder' => 'Tên dự án, địa chỉ…',
            'projectmap_filter_button' => 'Tìm trên bản đồ',
            'projectmap_filter_clear' => 'Xoá lọc',

            'projectmap_list_heading' => '{so} dự án {noi}',
            'projectmap_list_empty' => 'Không có dự án nào khớp với bộ lọc. Thử bỏ bớt một điều kiện xem sao.',
            'projectmap_list_missing' => 'Còn {so} dự án khớp bộ lọc nhưng chưa có toạ độ nên chưa hiện trên bản đồ.',

            'projectmap_card_show_text' => 'Xem trên bản đồ',
            'projectmap_card_detail_text' => 'Xem chi tiết dự án',
            'projectmap_card_price_unit' => 'triệu/m²',
            'projectmap_card_group_text' => '{so} dự án',
            'projectmap_card_area_label' => 'Diện tích',
            'projectmap_card_unit_label' => 'Số căn',

            'projectmap_back_text' => 'Về danh sách dự án',
            'projectmap_note' => 'Vị trí ghim mang tính tham khảo. Vui lòng đối chiếu với hồ sơ của chủ đầu tư trước khi quyết định.',
        ];

        $them = 0;

        foreach ($chu as $khoa => $noiDung) {
            $co = DB::table('introduces')
                ->where('keyword', $khoa)->where('language_id', 1)->exists();

            if ($co) {
                continue;
            }

            DB::table('introduces')->insert([
                'keyword' => $khoa,
                'language_id' => 1,
                'content' => $noiDung,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $them++;
        }

        $this->command?->info("Da them {$them} o noi dung cho trang ban do.");
    }

    /**
     * Cai dat nen ban do.
     *
     * KHONG dat san Google Maps API key: key la cua chu trang, phai tu vao
     * console.cloud.google.com tao va GIOI HAN theo ten mien, neu khong ai
     * cung dung duoc key do va hoa don se do chu trang tra.
     */
    private function napCaiDat(): void
    {
        $cai = [
            'map_provider' => 'osm',
            'map_tile_credit' => '© OpenStreetMap contributors',
            'map_zoom' => '5',
            'map_zoom_tinh' => '11',
            'map_zoom_xa' => '14',
        ];

        $them = 0;

        foreach ($cai as $khoa => $giaTri) {
            if (DB::table('systems')->where('keyword', $khoa)->where('language_id', 1)->exists()) {
                continue;
            }

            DB::table('systems')->insert([
                'keyword' => $khoa,
                'language_id' => 1,
                'user_id' => 1,
                'content' => $giaTri,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $them++;
        }

        $this->command?->info("Da them {$them} o cai dat ban do (nen mac dinh: OpenStreetMap).");
    }

    /**
     * Gan phuong/xa cho du an chua co.
     *
     * Cach lam: doi chieu TEN va DIA CHI du an voi danh sach phuong/xa that
     * cua chinh tinh do (bo dau, bo chu "Phuong"/"Xa"). Du an mau duoc dat
     * ten theo dia danh that nen phan lon khop duoc - "NOXH Song Cong" ra
     * "Phuong Song Cong" cua Thai Nguyen.
     *
     * KHONG khop thi de nguyen null, khong gan dai mot phuong/xa nao do:
     * mot dia chi sai nhin y het mot dia chi dung, nguoi dung khong co cach
     * nao biet. Cho nay quan tri tu chon trong QL Du an.
     */
    private function napPhuongXa(): void
    {
        $duAn = DB::table('products as p')
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'p.id')->where('pl.language_id', '=', 1);
            })
            ->whereNull('p.deleted_at')
            ->whereNotNull('p.province_code')
            ->where(function ($q) {
                $q->whereNull('p.ward_code')->orWhere('p.ward_code', '');
            })
            ->get(['p.id', 'p.province_code', 'p.address', 'pl.name']);

        $gan = 0;
        $chua = [];

        foreach ($duAn as $d) {
            $ma = $this->timXa($d->province_code, $d->name . ' ' . $d->address);

            if (!$ma) {
                $chua[] = $d->name;
                continue;
            }

            DB::table('products')->where('id', $d->id)
                ->update(['ward_code' => $ma, 'updated_at' => now()]);

            $gan++;
        }

        $this->command?->info("Da gan phuong/xa cho {$gan} du an.");

        if ($chua) {
            $this->command?->warn(
                'Chua doan duoc phuong/xa cua: ' . implode(', ', $chua)
                . '. Vao QL Du an NOXH -> sua tung du an de chon phuong/xa va nhap vi do/kinh do that;'
                . ' thieu toa do rieng thi ghim nam o tam tinh.'
            );
        }
    }

    /** Tim phuong/xa cua mot tinh co ten xuat hien trong doan chu cho truoc. */
    private function timXa(string $maTinh, string $doan): ?string
    {
        $doan = $this->gon($doan);

        $xa = DB::table('vn_wards')->where('province_code', $maTinh)->get(['code', 'name']);

        $hop = null;
        $dai = 0;

        foreach ($xa as $x) {
            // Bo "Phuong" / "Xa" / "Thi tran" o dau ten: chuoi so sanh con
            // lai moi la dia danh that.
            $ten = $this->gon(preg_replace('/^(phuong|xa|thi tran)\s+/iu', '', $this->gon($x->name)));

            // Dia danh mot tieng ("Yen", "Dong") de dinh nham vao chu khac
            // trong ten du an, chi nhan tu hai tieng tro len.
            if (mb_strlen($ten) < 5 || !str_contains($ten, ' ')) {
                continue;
            }

            // Khop duoc nhieu ten thi lay ten DAI nhat: "Song Cong" dung hon
            // "Song" khi ca hai cung nam trong ten du an.
            if (str_contains($doan, $ten) && mb_strlen($ten) > $dai) {
                $hop = $x->code;
                $dai = mb_strlen($ten);
            }
        }

        return $hop;
    }

    /** Bo dau, ha chu thuong, go dau gach - de so chu khong vuong dau tieng Viet. */
    private function gon(string $s): string
    {
        return trim(preg_replace('/\s+/', ' ', str_replace('-', ' ', Str::lower(Str::ascii($s)))));
    }
}
