<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * O "Thẻ (tags)" trong form bai viet cua khu quan tri.
 *
 * Hai man hinh sua/them bai co tham so id nen AdminSmokeTest bo qua - phai
 * co bai kiem tra rieng o day.
 */
class NoxhPostTagAdminTest extends TestCase
{
    private ?User $quanTri = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quanTri = User::whereHas('user_catalogues.permissions')
            ->where('publish', 2)
            ->first();
    }

    public function test_man_hinh_them_va_sua_bai_mo_duoc(): void
    {
        $id = DB::table('posts')->whereNull('deleted_at')->value('id');
        $this->assertNotNull($id, 'Chua co bai viet nao de mo man hinh sua');

        $this->actingAs($this->quanTri)->get('/post/create')->assertOk();

        $html = $this->actingAs($this->quanTri)->get("/post/{$id}/edit")
            ->assertOk()->getContent();

        $this->assertStringContainsString('name="tags"', $html);

        // O phai in san cac the bai dang mang, khong thi luu lai la mat het.
        foreach (Post::find($id)->tags as $t) {
            $this->assertStringContainsString(e($t->name), $html);
        }
    }

    public function test_luu_bai_thi_the_duoc_dong_bo(): void
    {
        $bai = Post::whereNull('deleted_at')->where('publish', 2)->first();
        $this->assertNotNull($bai, 'Chua co bai viet nao de kiem tra');

        $ngon = $bai->languages()->wherePivot('language_id', 1)->first();
        $this->assertNotNull($ngon, 'Bai viet chua co ban tieng Viet');

        $cu = $bai->tags->pluck('name')->implode(', ');

        $form = [
            'post_catalogue_id' => $bai->post_catalogue_id,
            'name' => $ngon->pivot->name,
            'canonical' => $ngon->pivot->canonical,
            'description' => $ngon->pivot->description,
            'content' => $ngon->pivot->content,
            'meta_title' => $ngon->pivot->meta_title,
            'meta_keyword' => $ngon->pivot->meta_keyword,
            'meta_description' => $ngon->pivot->meta_description,
            'publish' => $bai->publish,
            'image' => $bai->image,
            'image_caption' => $bai->image_caption,
        ];

        try {
            $this->actingAs($this->quanTri)
                ->post("/post/{$bai->id}/update", $form + ['tags' => 'The Kiem Tra A, the kiem tra b'])
                ->assertRedirect();

            $sau = Post::find($bai->id)->tags->pluck('canonical')->sort()->values()->all();
            $this->assertSame(['the-kiem-tra-a', 'the-kiem-tra-b'], $sau);

            // Bo bot mot the thi the do phai roi khoi bai.
            $this->actingAs($this->quanTri)
                ->post("/post/{$bai->id}/update", $form + ['tags' => 'The Kiem Tra A'])
                ->assertRedirect();

            $this->assertSame(
                ['the-kiem-tra-a'],
                Post::find($bai->id)->tags->pluck('canonical')->all()
            );
        } finally {
            $this->actingAs($this->quanTri)
                ->post("/post/{$bai->id}/update", $form + ['tags' => $cu]);

            Tag::whereIn('canonical', ['the-kiem-tra-a', 'the-kiem-tra-b'])->delete();
        }
    }
}
