<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Đưa nội dung khối "Ưu đãi" ở trang chi tiết sản phẩm (trước đây hard code trong
 * blade) vào bảng systems để admin tự sửa được tại Cấu hình hệ thống.
 */
class SharedOfferSystemSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'homepage_shared_offer_title' => 'ƯU ĐÃI TỪ TRUC GPS',
            'homepage_shared_offer' => '<ul>'
                . '<li>Tặng 03 tháng dịch vụ cho gói 1 năm</li>'
                . '<li>Giảm thêm 100.000đ khi không lắp Relay</li>'
                . '<li>Miễn phí lắp đặt nội thành Hà Nội, Đà Nẵng &amp; TPHCM</li>'
                . '</ul>',
        ];

        $userId = DB::table('users')->whereNull('deleted_at')->min('id')
            ?? DB::table('users')->min('id')
            ?? 1;

        foreach (DB::table('languages')->pluck('id') as $languageId) {
            foreach ($defaults as $keyword => $content) {
                $existing = DB::table('systems')
                    ->where('keyword', $keyword)
                    ->where('language_id', $languageId)
                    ->first();

                // Không ghi đè nội dung admin đã nhập.
                if ($existing && trim((string) $existing->content) !== '') {
                    continue;
                }

                if ($existing) {
                    DB::table('systems')
                        ->where('keyword', $keyword)
                        ->where('language_id', $languageId)
                        ->update(['content' => $content]);
                } else {
                    DB::table('systems')->insert([
                        'keyword' => $keyword,
                        'content' => $content,
                        'language_id' => $languageId,
                        'user_id' => $userId,
                    ]);
                }
            }
        }
    }
}
