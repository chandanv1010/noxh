<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Noi dung ban dau cho TRANG DANH MUC TIN va TRANG CHI TIET TIN, chep tung
 * chu tu ban thiet ke noxh_image/tin-tuc-fix.webp.
 *
 * Gom ba phan:
 *   1. Cac o chu quan tri sua duoc (bang introduces).
 *   2. Hinh cua tung chuyen muc tin - ban ve ve mot hinh truoc moi ten muc.
 *   3. Anh minh hoa cho cac bai viet mau, do tools/tach-anh-ban-ve.py cat ra
 *      tu chinh ban ve.
 *
 * Seeder CHI THEM o con trong / con chua co: chay lai khong de len thu quan
 * tri da sua.
 *
 *     php artisan db:seed --class=NoxhNewsPageSeeder --force
 */
class NoxhNewsPageSeeder extends Seeder
{
    /**
     * Hinh mac dinh cua tung chuyen muc, tra theo duong dan (canonical) chu
     * khong theo id: id sinh ra khac nhau tren tung may.
     */
    private const HINH_CHUYEN_MUC = [
        'chinh-sach' => 'scale-line',
        'thi-truong' => 'trend',
        'huong-dan-ho-so' => 'doc-line',
        'kinh-nghiem' => 'bulb-line',
        'cau-chuyen-an-cu' => 'people',
        'tin-dia-phuong' => 'pin-line',
    ];

    /**
     * Anh minh hoa cho cac bai viet mau, tra theo duong dan bai.
     *
     * [duong dan anh, dong chu thich duoi anh]
     */
    private const ANH_BAI = [
        'bon-nhom-doi-tuong-moi-duoc-mua-noxh' => [
            '/uploads/noxh/bai-viet-lon.jpg',
            'Việc mở rộng nhóm đối tượng giúp nhiều người dân tiếp cận nhà ở xã hội hơn. (Ảnh minh hoạ)',
        ],
        'lai-suat-vay-mua-noxh-thang-5-2026' => [
            '/uploads/noxh/bai-viet-5.jpg',
            'Lãi suất vay mua nhà ở xã hội được các ngân hàng cập nhật hằng tháng. (Ảnh minh hoạ)',
        ],
        'huong-dan-thu-tuc-ho-so-mua-noxh-2026' => [
            '/uploads/noxh/bai-viet-2.jpg',
            'Chuẩn bị đủ giấy tờ ngay từ đầu giúp hồ sơ được duyệt nhanh hơn. (Ảnh minh hoạ)',
        ],
        'dieu-kien-moi-nhat-de-duoc-mua-noxh' => [
            '/uploads/noxh/bai-viet-3.jpg',
            'Điều kiện về nhà ở, thu nhập và cư trú là ba nhóm điều kiện chính. (Ảnh minh hoạ)',
        ],
        'thai-nguyen-sap-mo-ban-hon-1000-can-noxh-tuc-duyen' => [
            '/uploads/noxh/bai-viet-4.jpg',
            'Khu nhà ở xã hội tại phường Túc Duyên, TP. Thái Nguyên. (Ảnh minh hoạ)',
        ],
    ];

    /**
     * Cac o da co san tu dot seed truoc nhung ban ve moi viet khac.
     *
     * Chi doi khi noi dung van DUNG BANG gia tri cu do seeder truoc dat -
     * quan tri da tu sua thi giu nguyen y cua ho.
     */
    private const DOI_NEU_CON_CU = [
        'news_heading' => ['Tin tức nhà ở xã hội', 'Tin tức'],
        'news_description' => [
            'Chính sách, tiến độ dự án và hướng dẫn thủ tục mới nhất.',
            'Cập nhật nhanh chóng các chính sách, quy định và thông tin mới nhất về nhà ở xã hội',
        ],
    ];

    public function run(): void
    {
        $this->napChu();
        $this->doiChuCu();
        $this->napHinhChuyenMuc();
        $this->napAnhBai();
        $this->napNoiDung();
        $this->napThe();
        $this->napAnhChuyenGia();
    }

    private function doiChuCu(): void
    {
        $doi = 0;

        foreach (self::DOI_NEU_CON_CU as $khoa => [$cu, $moi]) {
            $doi += DB::table('introduces')
                ->where('keyword', $khoa)->where('language_id', 1)
                ->where('content', $cu)
                ->update(['content' => $moi, 'updated_at' => now()]);
        }

        $this->command?->info("Da doi {$doi} o chu theo ban ve moi.");
    }

    /**
     * Anh nguoi tu van trong the chuyen gia o cot phai.
     *
     * Dung lai buc anh da tach tu ban ve trang Phap ly (cung mot nguoi, da
     * xoa nen) - du an chua co anh chup that.
     */
    private function napAnhChuyenGia(): void
    {
        $sua = DB::table('experts')
            ->where(fn ($q) => $q->whereNull('image')->orWhere('image', ''))
            ->update(['image' => '/uploads/noxh/tu-van-vien.png', 'updated_at' => now()]);

        $this->command?->info("Da gan anh cho {$sua} chuyen gia.");
    }

    private function napChu(): void
    {
        $chu = [
            // --- dai anh dau cac trang trong -----------------------------------
            // Dung chung voi trang chi tiet du an: doi anh o day la ca hai
            // trang doi theo.
            'pagehead_image' => '/uploads/noxh/bang-ron-trang-trong.png',

            // --- dai dau trang --------------------------------------------------
            'news_heading' => 'Tin tức',
            'news_description' => 'Cập nhật nhanh chóng các chính sách, quy định và thông tin mới nhất về nhà ở xã hội',

            // --- cot trai --------------------------------------------------------
            'news_cat_heading' => 'Danh mục tin tức',
            'news_cat_all_text' => 'Tất cả tin tức',
            'news_cat_all_icon' => 'news-all',
            'news_help_heading' => 'Cần tư vấn thêm?',
            'news_help_description' => 'Đội ngũ chuyên gia của NOXH.vn sẵn sàng hỗ trợ bạn 24/7.',
            'news_help_icon' => 'phone',
            'news_help_button' => 'ĐĂNG KÝ TƯ VẤN NGAY',
            'news_help_link' => '/cong-hoa/tu-van',

            // --- cot phai --------------------------------------------------------
            'news_related_heading' => 'Bài viết liên quan',
            'news_related_all_text' => 'Xem tất cả',

            // --- giua trang -------------------------------------------------------
            'news_count_text' => '{so} bài viết',
            'news_empty_text' => 'Chưa có bài viết nào trong mục này.',
            'news_view_text' => 'lượt xem',
            'news_share_label' => 'Chia sẻ:',
            'news_copy_done_text' => 'Đã chép đường dẫn bài viết',
            'news_expand_text' => 'Xem thêm nội dung',
            'news_collapse_text' => 'Thu gọn nội dung',
            'news_tag_heading' => '',
            'news_tag_title' => '#{the}',

            // --- the chuyen gia o cot phai (dung chung nhieu trang) ------------
            'expert_heading' => 'TƯ VẤN CÙNG CHUYÊN GIA',
            'expert_button' => 'ĐĂNG KÝ TƯ VẤN NGAY',
            'expert_button_link' => '/cong-hoa/tu-van',
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

        $this->command?->info("Da them {$them} o noi dung cho trang tin tuc.");
    }

    /**
     * Noi dung day du va tu khoa cho cac bai viet mau.
     *
     * CANH BAO: day la chu MINH HOA chep theo ban thiet ke, khong phai van
     * ban phap luat that - so hieu nghi dinh va cac muc thu nhap trong do la
     * vi du. Truoc khi chay that phai thay bang bai viet cua bien tap vien.
     *
     * Chi ghi de khi bai con dang rat ngan (duoi 400 ky tu) - tuc la van con
     * la doan chu tam cua dot seed dau tien.
     */
    private const NOI_DUNG_BAI = [
        'bon-nhom-doi-tuong-moi-duoc-mua-noxh' => [
            'nhà ở xã hội, Nghị định 136/2026, chính sách, thu nhập, an sinh xã hội',
            <<<'HTML'
<p>Chính phủ ban hành Nghị định 136/2026/NĐ-CP quy định về mức thu nhập được mua nhà ở xã hội, chính thức có hiệu lực từ ngày 07/04/2026. Theo đó, mức thu nhập được nâng lên nhằm mở rộng đối tượng, tạo điều kiện cho nhiều người dân có cơ hội tiếp cận nhà ở xã hội.</p>

<h3>1. Mức thu nhập được mua NOXH từ 07/04/2026</h3>
<ul>
    <li>Người độc thân: không quá 25 triệu đồng/tháng</li>
    <li>Người độc thân nuôi con dưới tuổi thành niên: không quá 35 triệu đồng/tháng</li>
    <li>Trường hợp hai vợ chồng: tổng thu nhập không quá 50 triệu đồng/tháng</li>
</ul>

<h3>2. Đối tượng áp dụng</h3>
<p>Các nhóm đối tượng được quy định tại Luật Nhà ở và Nghị định 136/2026/NĐ-CP, bao gồm:</p>
<ul>
    <li>Người có công với cách mạng và thân nhân liệt sĩ</li>
    <li>Hộ gia đình nghèo, cận nghèo tại khu vực nông thôn và đô thị</li>
    <li>Người thu nhập thấp tại khu vực đô thị</li>
    <li>Công nhân, người lao động đang làm việc tại khu công nghiệp</li>
</ul>

<h3>3. Điều kiện về nhà ở và cư trú</h3>
<p>Người mua phải chưa có nhà ở thuộc sở hữu của mình tại tỉnh, thành phố nơi có dự án, hoặc có nhà ở nhưng diện tích bình quân đầu người thấp hơn mức tối thiểu do Chính phủ quy định. Điều kiện về cư trú đã được đơn giản hoá: không còn yêu cầu đăng ký thường trú tại địa phương có dự án.</p>

<h3>4. Hồ sơ cần chuẩn bị</h3>
<ul>
    <li>Đơn đăng ký mua nhà ở xã hội theo mẫu của chủ đầu tư</li>
    <li>Giấy tờ chứng minh đối tượng được hưởng chính sách</li>
    <li>Giấy tờ chứng minh điều kiện về nhà ở và thu nhập</li>
    <li>Giấy tờ tuỳ thân của các thành viên trong hộ gia đình</li>
</ul>

<h3>5. Lưu ý khi nộp hồ sơ</h3>
<p>Hồ sơ nộp trực tiếp cho chủ đầu tư dự án, không nộp qua trung gian. Người mua nên giữ lại một bản sao toàn bộ hồ sơ và giấy biên nhận để đối chiếu khi cần. Trường hợp hồ sơ bị trả lại, chủ đầu tư phải nêu rõ lý do bằng văn bản.</p>
HTML,
        ],

        'dieu-kien-moi-nhat-de-duoc-mua-noxh' => [
            'điều kiện mua NOXH, nhà ở xã hội, thu nhập, cư trú',
            <<<'HTML'
<p>Ba nhóm điều kiện phải cùng lúc đáp ứng khi đăng ký mua nhà ở xã hội là điều kiện về đối tượng, điều kiện về nhà ở và điều kiện về thu nhập.</p>

<h3>Điều kiện về đối tượng</h3>
<p>Người đăng ký phải thuộc một trong các nhóm được hưởng chính sách hỗ trợ nhà ở xã hội theo Luật Nhà ở, kèm giấy tờ chứng minh do cơ quan có thẩm quyền cấp.</p>

<h3>Điều kiện về nhà ở</h3>
<ul>
    <li>Chưa có nhà ở thuộc sở hữu của mình tại tỉnh, thành phố nơi có dự án</li>
    <li>Hoặc có nhà ở nhưng diện tích bình quân đầu người thấp hơn mức tối thiểu</li>
    <li>Chưa từng được mua, thuê mua nhà ở xã hội trước đó</li>
</ul>

<h3>Điều kiện về thu nhập</h3>
<p>Thu nhập không vượt mức trần do Chính phủ quy định, xác nhận bởi đơn vị đang làm việc hoặc uỷ ban nhân dân cấp xã nơi cư trú đối với lao động tự do.</p>
HTML,
        ],

        'huong-dan-thu-tuc-ho-so-mua-noxh-2026' => [
            'hồ sơ mua NOXH, thủ tục, hướng dẫn, nhà ở xã hội',
            <<<'HTML'
<p>Bộ hồ sơ mua nhà ở xã hội gồm bốn nhóm giấy tờ. Chuẩn bị đủ ngay từ đầu giúp rút ngắn thời gian xét duyệt xuống còn khoảng 15 – 30 ngày làm việc.</p>

<h3>Bước 1: Chuẩn bị giấy tờ</h3>
<ul>
    <li>Đơn đăng ký mua nhà ở xã hội theo mẫu</li>
    <li>Giấy tờ chứng minh đối tượng</li>
    <li>Giấy xác nhận về nhà ở và thu nhập</li>
    <li>Bản sao giấy tờ tuỳ thân của các thành viên</li>
</ul>

<h3>Bước 2: Nộp hồ sơ cho chủ đầu tư</h3>
<p>Nộp trực tiếp tại văn phòng bán hàng của dự án và lấy giấy biên nhận. Không nộp hồ sơ hay đặt cọc qua bên thứ ba.</p>

<h3>Bước 3: Xét duyệt và bốc thăm</h3>
<p>Chủ đầu tư lập danh sách, gửi sở xây dựng thẩm định. Nếu số hồ sơ hợp lệ nhiều hơn số căn, việc lựa chọn được thực hiện bằng hình thức bốc thăm công khai.</p>

<h3>Bước 4: Ký hợp đồng mua bán</h3>
<p>Sau khi trúng tuyển, người mua ký hợp đồng và thanh toán theo tiến độ. Đọc kỹ điều khoản về thời điểm bàn giao và phí bảo trì trước khi ký.</p>
HTML,
        ],

        'lai-suat-vay-mua-noxh-thang-5-2026' => [
            'lãi suất, vay mua NOXH, ngân hàng, gói tín dụng',
            <<<'HTML'
<p>Lãi suất cho vay mua nhà ở xã hội được các ngân hàng cập nhật hằng tháng theo mức trần của Ngân hàng Nhà nước. Người vay nên so sánh cả lãi suất, thời hạn vay và các khoản phí đi kèm.</p>

<h3>Những điểm cần so sánh</h3>
<ul>
    <li>Lãi suất ưu đãi và thời gian giữ mức ưu đãi</li>
    <li>Cách tính lãi sau thời gian ưu đãi</li>
    <li>Thời hạn vay tối đa và tỷ lệ cho vay trên giá trị căn hộ</li>
    <li>Phí trả nợ trước hạn</li>
</ul>

<h3>Lưu ý khi tính khả năng trả nợ</h3>
<p>Khoản trả nợ hằng tháng nên giữ dưới 40% thu nhập của hộ gia đình. Công cụ tính khoản vay trên NOXH.vn giúp bạn ước lượng nhanh số tiền phải trả mỗi tháng theo từng gói.</p>
HTML,
        ],

        'thai-nguyen-sap-mo-ban-hon-1000-can-noxh-tuc-duyen' => [
            'Thái Nguyên, Túc Duyên, mở bán, dự án NOXH',
            <<<'HTML'
<p>Dự án nhà ở xã hội tại phường Túc Duyên, TP. Thái Nguyên dự kiến nhận hồ sơ từ quý IV/2026 với hơn 1.000 căn hộ thuộc nhiều loại diện tích.</p>

<h3>Thông tin dự án</h3>
<ul>
    <li>Vị trí: phường Túc Duyên, TP. Thái Nguyên, tỉnh Thái Nguyên</li>
    <li>Quy mô: hơn 1.000 căn hộ, loại hình 1PN, 2PN và 3PN</li>
    <li>Thời điểm nhận hồ sơ dự kiến: quý IV/2026</li>
</ul>

<h3>Chuẩn bị trước khi nhận hồ sơ</h3>
<p>Người quan tâm nên kiểm tra điều kiện và chuẩn bị giấy tờ trước, vì thời gian nhận hồ sơ của mỗi đợt mở bán thường chỉ kéo dài vài tuần.</p>
HTML,
        ],
    ];

    private function napNoiDung(): void
    {
        $sua = 0;

        foreach (self::NOI_DUNG_BAI as $canonical => [$tuKhoa, $noiDung]) {
            $sua += DB::table('post_language')
                ->where('canonical', $canonical)->where('language_id', 1)
                ->whereRaw('CHAR_LENGTH(COALESCE(content, \'\')) < 400')
                ->update([
                    'content' => $noiDung,
                    'meta_keyword' => $tuKhoa,
                    'updated_at' => now(),
                ]);
        }

        $this->command?->info("Da bo sung noi dung cho {$sua} bai viet mau.");
    }

    /**
     * The (tag) cua cac bai viet mau.
     *
     * Dung lai chinh chuoi tu khoa khai bao o NOI_DUNG_BAI - ban ve ve hang
     * the duoi bai bang dung nhung chu do. Bai nao da co the roi thi bo qua.
     */
    private function napThe(): void
    {
        $them = 0;

        foreach (self::NOI_DUNG_BAI as $canonical => [$tuKhoa, $noiDung]) {
            $id = DB::table('post_language')
                ->where('canonical', $canonical)->where('language_id', 1)
                ->value('post_id');

            if (!$id) {
                continue;
            }

            $bai = \App\Models\Post::find($id);

            if (!$bai || $bai->tags()->count() > 0) {
                continue;
            }

            $bai->tags()->sync(\App\Models\Tag::tuChuoi($tuKhoa));
            $them++;
        }

        $this->command?->info("Da gan the cho {$them} bai viet.");
    }

    private function napHinhChuyenMuc(): void
    {
        $sua = 0;

        foreach (self::HINH_CHUYEN_MUC as $canonical => $hinh) {
            $id = DB::table('post_catalogue_language')
                ->where('canonical', $canonical)->where('language_id', 1)
                ->value('post_catalogue_id');

            if (!$id) {
                continue;
            }

            // Chi dat cho muc CHUA chon hinh - quan tri doi roi thi giu nguyen.
            $sua += DB::table('post_catalogues')
                ->where('id', $id)
                ->where(fn ($q) => $q->whereNull('icon')->orWhere('icon', ''))
                ->update(['icon' => $hinh, 'updated_at' => now()]);
        }

        $this->command?->info("Da dat hinh cho {$sua} chuyen muc tin.");
    }

    private function napAnhBai(): void
    {
        $sua = 0;

        foreach (self::ANH_BAI as $canonical => [$anh, $chuThich]) {
            $id = DB::table('post_language')
                ->where('canonical', $canonical)->where('language_id', 1)
                ->value('post_id');

            if (!$id) {
                continue;
            }

            $sua += DB::table('posts')
                ->where('id', $id)
                ->where(fn ($q) => $q->whereNull('image')->orWhere('image', ''))
                ->update(['image' => $anh, 'image_caption' => $chuThich, 'updated_at' => now()]);
        }

        $this->command?->info("Da gan anh minh hoa cho {$sua} bai viet.");
    }
}
