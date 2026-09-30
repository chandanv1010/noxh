<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * The (tag) cua bai viet.
 *
 * Quan tri go ten the o form bai viet, cach nhau dau phay; the chua co thi
 * duoc tao ngay luc do. Duong dan cua the sinh tu ten, nen hai bai go
 * "Nhà ở xã hội" va "nhà ở xã hội" van ve chung mot the.
 */
class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = [
        'language_id',
        'name',
        'canonical',
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag', 'tag_id', 'post_id');
    }

    /**
     * Doi mot dong quan tri go ("nhà ở xã hội, chính sách") thanh danh sach
     * id the, tao moi nhung the chua co.
     *
     * Tra ve mang id de goi thang vao $post->tags()->sync(...).
     */
    public static function tuChuoi(?string $chuoi, int $languageId = 1): array
    {
        $id = [];

        foreach (explode(',', (string) $chuoi) as $ten) {
            $ten = trim(preg_replace('/\s+/u', ' ', $ten));
            $duong = Str::slug($ten);

            // Ten chi gom ky tu la (vi du "***") thi slug ra rong - bo qua,
            // khong the tao mot the khong co duong dan.
            if ($ten === '' || $duong === '') {
                continue;
            }

            $the = static::firstOrCreate(
                ['language_id' => $languageId, 'canonical' => $duong],
                ['name' => $ten]
            );

            $id[$the->id] = $the->id;
        }

        return array_values($id);
    }
}
