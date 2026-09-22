<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Don sach du lieu con sot lai cua trang GPS truoc khi clone thanh NOXH.vn.
 *
 * Lenh nay XOA du lieu, khong co duong lui - hay sao luu CSDL truoc khi chay.
 * Chay lai nhieu lan khong sao: moi buoc deu chi dong vao thu dung dia chi.
 */
class DonDuLieuCu extends Command
{
    protected $signature = 'noxh:don-du-lieu-cu {--that-su : Thuc su xoa. Khong co co nay thi chi liet ke se xoa nhung gi}';

    protected $description = 'Xóa dữ liệu còn sót của website cũ (bài viết, widget, slide, cấu hình không dùng) và dựng lại menu';

    /**
     * Cac o cau hinh NOXH.vn thuc su doc toi. Nhung khoa khac trong bang
     * systems deu la cua ban clone truc, xoa di khong anh huong gi.
     */
    private const CAU_HINH_GIU = [
        'homepage_company', 'homepage_brand', 'homepage_slogan', 'homepage_logo',
        'homepage_logo_mobile', 'homepage_favicon', 'homepage_copyright',
        'contact_address', 'contact_office', 'contact_email', 'contact_hotline',
        'contact_map', 'contact_working_hours',
        'seo_meta_title', 'seo_meta_description', 'seo_meta_keyword', 'seo_meta_images',
        'social_facebook', 'social_youtube', 'social_zalo', 'social_tiktok',
        'sale_post_approval', 'sale_project_approval',
        'telegram_bot_token', 'telegram_chat_id',
    ];

    /** Bai viet cua NOXH - moi thu khac trong bang posts la cua trang cu. */
    private const BAI_GIU = [18, 19, 20, 21];

    /**
     * Chuyen muc tin tuc theo ban thiet ke moi.
     * [canonical => ten]
     */
    private const CHUYEN_MUC = [
        'chinh-sach' => 'Chính sách',
        'thi-truong' => 'Thị trường',
        'huong-dan-ho-so' => 'Hướng dẫn hồ sơ',
        'kinh-nghiem' => 'Kinh nghiệm',
        'cau-chuyen-an-cu' => 'Câu chuyện an cư',
        'tin-dia-phuong' => 'Tin địa phương',
    ];

    /**
     * Thanh dieu huong tren dau, dung theo ban thiet ke.
     * [ten, canonical, [con...]]
     *
     * Moi canonical o day deu tro toi mot route co that - danh sach cu co bon
     * muc con tro vao trang khong ton tai (phap-ly-noxh/doi-tuong, /dieu-kien,
     * /chinh-sach, /hoi-dap).
     */
    private const MENU_CHINH = [
        ['Trang chủ', '', []],
        ['Dự án', 'du-an', [
            ['Danh sách dự án', 'du-an'],
            ['Theo tỉnh/thành', 'du-an/tinh-thanh'],
        ]],
        ['Pháp lý NOXH', 'phap-ly-noxh', [
            ['Tổng quan pháp lý', 'phap-ly-noxh'],
            ['Văn bản pháp luật', 'phap-ly-noxh/van-ban'],
        ]],
        ['Kiểm tra điều kiện', 'kiem-tra-dieu-kien', []],
        ['Hồ sơ', 'ho-so/can-chuan-bi', [
            ['Hồ sơ cần chuẩn bị', 'ho-so/can-chuan-bi'],
            ['Mẫu đơn', 'ho-so/mau-don'],
            ['Checklist', 'ho-so/checklist'],
        ]],
        ['Hỏi đáp', 'hoi-dap', []],
        ['Tin tức', 'tin-tuc', []],
        ['Tư vấn', 'cong-hoa/tu-van', [
            ['Đăng ký tư vấn', 'cong-hoa/tu-van'],
            ['Tính khoản vay', 'tai-chinh/tinh-khoan-vay'],
            ['Khả năng tài chính', 'tai-chinh/kha-nang-tai-chinh'],
            ['Liên hệ', 'lien-he'],
        ]],
    ];

    private const MENU_CHAN = [
        ['Giới thiệu', 'gioi-thieu'],
        ['Kiểm tra điều kiện', 'kiem-tra-dieu-kien'],
        ['Hồ sơ cần chuẩn bị', 'ho-so/can-chuan-bi'],
        ['Câu hỏi thường gặp', 'hoi-dap'],
        ['Văn bản pháp luật', 'phap-ly-noxh/van-ban'],
        ['Chính sách bảo mật', 'chinh-sach-bao-mat'],
        ['Điều khoản sử dụng', 'dieu-khoan-su-dung'],
        ['Liên hệ', 'lien-he'],
    ];

    private const ANH_MAC_DINH = '/uploads/noxh/du-an-mac-dinh-the.jpg';

    private bool $thatSu = false;

    public function handle(): int
    {
        $this->thatSu = (bool) $this->option('that-su');

        if (!$this->thatSu) {
            $this->warn('CHẠY THỬ - chưa xóa gì cả. Thêm --that-su để thực hiện.');
            $this->newLine();
        }

        $this->baiViet();
        $this->chuyenMuc();
        $this->widgetVaSlide();
        $this->cauHinh();
        $this->anhDuAn();
        $this->menu();

        $this->newLine();
        $this->info($this->thatSu ? 'Đã dọn xong.' : 'Hết phần chạy thử.');

        return self::SUCCESS;
    }

    private function baiViet(): void
    {
        $xoa = DB::table('posts')->whereNotIn('id', self::BAI_GIU)->pluck('id')->all();

        $this->dong('Bài viết của trang cũ', count($xoa));

        if (!$this->thatSu || !$xoa) {
            return;
        }

        DB::table('post_language')->whereIn('post_id', $xoa)->delete();
        DB::table('post_catalogue_post')->whereIn('post_id', $xoa)->delete();
        DB::table('routers')->whereIn('module_id', $xoa)
            ->where('controllers', 'App\Http\Controllers\Frontend\PostController')->delete();
        DB::table('posts')->whereIn('id', $xoa)->delete();
    }

    private function chuyenMuc(): void
    {
        $this->dong('Chuyên mục tin tức dựng lại', count(self::CHUYEN_MUC));

        if (!$this->thatSu) {
            return;
        }

        $cu = DB::table('post_catalogues')->pluck('id')->all();

        DB::table('post_catalogue_language')->whereIn('post_catalogue_id', $cu)->delete();
        DB::table('post_catalogue_post')->whereIn('post_catalogue_id', $cu)->delete();
        DB::table('routers')->whereIn('module_id', $cu)
            ->where('controllers', 'App\Http\Controllers\Frontend\PostCatalogueController')->delete();
        DB::table('post_catalogues')->whereIn('id', $cu)->delete();

        $thuTu = 0;
        $dauTien = null;

        foreach (self::CHUYEN_MUC as $canonical => $ten) {
            $id = DB::table('post_catalogues')->insertGetId([
                // user_id la NOT NULL va co khoa ngoai sang bang users.
                'user_id' => $this->nguoiTao(),
                'parent_id' => 0,
                'lft' => ++$thuTu * 2 - 1,
                'rgt' => $thuTu * 2,
                'level' => 1,
                'order' => $thuTu,
                'publish' => 2,
                'follow' => 2,
                'image' => self::ANH_MAC_DINH,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('post_catalogue_language')->insert([
                'post_catalogue_id' => $id,
                'language_id' => 1,
                'name' => $ten,
                'canonical' => $canonical,
                'meta_title' => $ten,
                'meta_description' => $ten . ' - NOXH.vn',
            ]);

            DB::table('routers')->insert([
                'module_id' => $id,
                'language_id' => 1,
                'canonical' => $canonical,
                'controllers' => 'App\Http\Controllers\Frontend\PostCatalogueController',
            ]);

            $dauTien = $dauTien ?? $id;
        }

        // Bai NOXH con lai deu ve chuyen muc dau tien de khong bi mo coi.
        foreach (self::BAI_GIU as $baiId) {
            if (!DB::table('posts')->where('id', $baiId)->exists()) {
                continue;
            }

            DB::table('posts')->where('id', $baiId)->update(['post_catalogue_id' => $dauTien]);
            DB::table('post_catalogue_post')->updateOrInsert(
                ['post_id' => $baiId, 'post_catalogue_id' => $dauTien],
                []
            );
        }
    }

    private function widgetVaSlide(): void
    {
        $widget = DB::table('widgets')->count();
        $slide = DB::table('slides')->count();

        $this->dong('Widget của trang cũ', $widget);
        $this->dong('Slide của trang cũ', $slide);

        if (!$this->thatSu) {
            return;
        }

        // Giao dien NOXH khong doc bang nao trong hai bang nay: khong con view
        // nao goi toi chung sau dot go frontend ke thua.
        DB::table('widgets')->delete();
        DB::table('slides')->delete();
    }

    private function cauHinh(): void
    {
        $xoa = DB::table('systems')->whereNotIn('keyword', self::CAU_HINH_GIU)->count();

        $this->dong('Ô cấu hình không dùng', $xoa);

        if ($this->thatSu) {
            DB::table('systems')->whereNotIn('keyword', self::CAU_HINH_GIU)->delete();
        }
    }

    private function anhDuAn(): void
    {
        $thieu = DB::table('products')
            ->where(function ($q) {
                $q->whereNull('image')->orWhere('image', '');
            })
            ->count();

        $this->dong('Dự án chưa có ảnh, gán ảnh mặc định', $thieu);

        if ($this->thatSu && $thieu) {
            DB::table('products')
                ->where(function ($q) {
                    $q->whereNull('image')->orWhere('image', '');
                })
                ->update(['image' => self::ANH_MAC_DINH]);
        }
    }

    private function menu(): void
    {
        $so = count(self::MENU_CHINH) + count(self::MENU_CHAN);
        foreach (self::MENU_CHINH as [, , $con]) {
            $so += count($con);
        }

        $this->dong('Mục menu dựng lại', $so);

        if (!$this->thatSu) {
            return;
        }

        DB::table('menu_language')->delete();
        DB::table('menus')->delete();

        $this->napMenu($this->nhomMenu('main-menu', 'Menu chính'), self::MENU_CHINH);
        $this->napMenu(
            $this->nhomMenu('footer-menu', 'Menu chân trang'),
            array_map(fn ($m) => [$m[0], $m[1], []], self::MENU_CHAN)
        );
    }

    private function nhomMenu(string $keyword, string $ten): int
    {
        $nhom = DB::table('menu_catalogues')->where('keyword', $keyword)->first();

        if ($nhom) {
            return $nhom->id;
        }

        $id = DB::table('menu_catalogues')->insertGetId([
            'name' => $ten,
            'keyword' => $keyword,
            'publish' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menu_catalogue_language')->insert([
            'menu_catalogue_id' => $id,
            'language_id' => 1,
            'name' => $ten,
        ]);

        return $id;
    }

    /**
     * Ghi cac muc menu.
     *
     * Bang menus sap xep theo cot lft, nen phai danh so lft tang dan theo dung
     * thu tu muon hien: cha, roi cac con cua no, roi moi den cha tiep theo.
     */
    private function napMenu(int $nhomId, array $muc): void
    {
        $lft = 0;

        foreach ($muc as $thuTu => [$ten, $canonical, $con]) {
            $chaId = $this->themMuc($nhomId, 0, $ten, $canonical, $thuTu, ++$lft);

            foreach ($con as $thuTuCon => [$tenCon, $canonicalCon]) {
                $this->themMuc($nhomId, $chaId, $tenCon, $canonicalCon, $thuTuCon, ++$lft);
            }
        }
    }

    private function themMuc(int $nhomId, int $chaId, string $ten, string $canonical, int $thuTu, int $lft): int
    {
        $id = DB::table('menus')->insertGetId([
            'user_id' => $this->nguoiTao(),
            'menu_catalogue_id' => $nhomId,
            'parent_id' => $chaId,
            'order' => $thuTu,
            'lft' => $lft,
            'rgt' => $lft,
            'level' => $chaId === 0 ? 1 : 2,
            'publish' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menu_language')->insert([
            'menu_id' => $id,
            'language_id' => 1,
            'name' => $ten,
            'canonical' => $canonical,
        ]);

        return $id;
    }

    /** Nguoi dung dung lam nguoi tao cho cac ban ghi lenh nay sinh ra. */
    private function nguoiTao(): int
    {
        return (int) DB::table('users')->orderBy('id')->value('id');
    }

    private function dong(string $ten, int $so): void
    {
        $this->line(sprintf('  %-42s %s', $ten, $so));
    }
}
