<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Du lieu khoi tao cho NOXH: loai du an, nhom thuoc tinh loc, va bo cau hoi
 * kiem tra dieu kien.
 *
 * Day la bo khung de bat dau, khong phai du lieu cuoi cung - nguoi quan tri
 * sua lai trong admin. Muc dich la de cac module co du lieu that ma chay thu
 * ngay, thay vi doi nhap tay 30 dong.
 *
 * Bo cau hoi lay tu man hinh "Kiem tra dieu kien" trong bo anh mo phong.
 * Nguong thu nhap lay theo Nghi dinh 136/2026 (hieu luc 07/04/2026) nhu ban
 * tin trong chinh bo anh do: doc than <= 25 trieu, doc than nuoi con <= 35
 * trieu, hai vo chong tong <= 50 trieu.
 *
 * Chay: php artisan db:seed --class=NoxhStarterDataSeeder --force
 * Chay lai se xoa va nap lai dung ba nhom du lieu nay, khong dung den cai khac.
 */
class NoxhStarterDataSeeder extends Seeder
{
    private const LANG = 1; // Tieng Viet

    /**
     * Nguoi tao cac ban ghi seed.
     *
     * product_catalogues / attributes / attribute_catalogues deu co khoa ngoai
     * user_id -> users NOT NULL, nen bat buoc phai co mot user that. Lay
     * Superadmin (id nho nhat) chu khong hard-code 1, phong khi may chu khac
     * danh so khac.
     */
    private int $userId = 1;

    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('=== Nap du lieu khoi tao NOXH ===');

        $this->userId = (int) (DB::table('users')->min('id') ?? 1);

        DB::transaction(function () {
            $this->seedProjectTypes();
            $this->seedFilterAttributes();
            $this->seedEligibilityQuestions();
        });

        $this->command->newLine();
    }

    /**
     * Loai du an = product_catalogues, dung nhu da chot.
     * Hien o truong "Loai hinh" tren man hinh chi tiet du an.
     */
    private function seedProjectTypes(): void
    {
        $types = [
            'Nhà ở xã hội',
            'Nhà ở công nhân',
            'Nhà ở thu nhập thấp',
            'Nhà ở cho lực lượng vũ trang',
            'Cải tạo chung cư cũ',
        ];

        DB::table('product_catalogue_language')->delete();
        DB::table('product_catalogues')->delete();

        $now = now();
        $lft = 1;

        foreach ($types as $i => $name) {
            $id = DB::table('product_catalogues')->insertGetId([
                'parent_id' => 0,
                // Cay phang: moi muc chiem mot cap lft/rgt lien nhau.
                'lft' => $lft,
                'rgt' => $lft + 1,
                'level' => 1,
                'publish' => 2,
                'follow' => 2,
                'order' => $i,
                'user_id' => $this->userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $lft += 2;

            DB::table('product_catalogue_language')->insert([
                'product_catalogue_id' => $id,
                'language_id' => self::LANG,
                'name' => $name,
                'canonical' => Str::slug($name),
                'meta_title' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->line(sprintf('  loai du an          : %d', count($types)));
    }

    /**
     * Nhom thuoc tinh dung cho bo loc.
     *
     * Bon khoi loc trong anh mo phong (tinh / trang thai / gia / dien tich) deu
     * la COT cua bang products, khong phai thuoc tinh. Cac nhom o day la de loc
     * sau nay, dung nhu yeu cau "cho chon thuoc tinh de sau con loc du lieu".
     */
    private function seedFilterAttributes(): void
    {
        // [ten nhom, kieu loc, so dong hien truoc khi thu gon, [cac gia tri]]
        $groups = [
            ['Loại căn hộ', 'checkbox', 0, [
                'Studio', '1 phòng ngủ', '2 phòng ngủ', '3 phòng ngủ', 'Từ 4 phòng ngủ',
            ]],
            ['Tiện ích dự án', 'checkbox', 6, [
                'Bể bơi', 'Công viên cây xanh', 'Trường học', 'Siêu thị / TTTM',
                'Phòng gym', 'Sân chơi trẻ em', 'Hầm để xe', 'An ninh 24/7',
                'Thang máy', 'PCCC tự động', 'Trạm y tế', 'Khu BBQ',
            ]],
            ['Hình thức sở hữu', 'radio', 0, [
                'Sở hữu lâu dài', 'Sở hữu 50 năm', 'Sở hữu 70 năm',
            ]],
            ['Nội thất bàn giao', 'radio', 0, [
                'Bàn giao thô', 'Hoàn thiện cơ bản', 'Full nội thất',
            ]],
            ['Hướng ban công', 'checkbox', 4, [
                'Đông', 'Tây', 'Nam', 'Bắc',
                'Đông Nam', 'Đông Bắc', 'Tây Nam', 'Tây Bắc',
            ]],
        ];

        DB::table('attribute_catalogue_attribute')->delete();
        DB::table('attribute_language')->delete();
        DB::table('attributes')->delete();
        DB::table('attribute_catalogue_language')->delete();
        DB::table('attribute_catalogues')->delete();

        $now = now();
        $lft = 1;
        $soThuocTinh = 0;

        foreach ($groups as $gi => [$groupName, $filterType, $visible, $values]) {
            $catId = DB::table('attribute_catalogues')->insertGetId([
                'parent_id' => 0,
                'lft' => $lft,
                'rgt' => $lft + 1,
                'level' => 1,
                'publish' => 2,
                'follow' => 2,
                'order' => $gi,
                'filterable' => true,
                'filter_type' => $filterType,
                'filter_order' => $gi,
                'filter_visible_count' => $visible,
                'user_id' => $this->userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $lft += 2;

            DB::table('attribute_catalogue_language')->insert([
                'attribute_catalogue_id' => $catId,
                'language_id' => self::LANG,
                'name' => $groupName,
                'canonical' => Str::slug($groupName),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($values as $ai => $value) {
                $attrId = DB::table('attributes')->insertGetId([
                    'attribute_catalogue_id' => $catId,
                    'publish' => 2,
                    'follow' => 2,
                    'order' => $ai,
                    'user_id' => $this->userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('attribute_language')->insert([
                    'attribute_id' => $attrId,
                    'language_id' => self::LANG,
                    'name' => $value,
                    'canonical' => Str::slug($value),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('attribute_catalogue_attribute')->insert([
                    'attribute_catalogue_id' => $catId,
                    'attribute_id' => $attrId,
                ]);

                $soThuocTinh++;
            }
        }

        $this->command->line(sprintf('  nhom thuoc tinh loc : %d  (%d gia tri)', count($groups), $soThuocTinh));
    }

    /**
     * Bo 8 cau hoi kiem tra dieu kien, lay tu man hinh mo phong.
     *
     * verdict:  pass    = dat tieu chi
     *           unclear = can kiem tra them (hien "Can kiem tra" o bang ket qua)
     *           fail    = khong dat - day la dieu kien loai tru
     */
    private function seedEligibilityQuestions(): void
    {
        $questions = [
            [
                'q' => 'Bạn hoặc vợ/chồng đã có nhà ở thuộc sở hữu của mình tại nơi có dự án NOXH chưa?',
                'label' => 'Chưa có nhà ở thuộc sở hữu của mình tại nơi có dự án NOXH',
                'group' => 'housing', 'type' => 'boolean', 'weight' => 2,
                'options' => [
                    ['Chưa có', 'no', 'pass', 2, 'Đáp ứng điều kiện'],
                    ['Đã có', 'yes', 'fail', 0, 'Không đáp ứng điều kiện'],
                ],
            ],
            [
                'q' => 'Bạn hoặc vợ/chồng đã được Nhà nước hỗ trợ nhà ở dưới mọi hình thức chưa?',
                'label' => 'Chưa được Nhà nước hỗ trợ nhà ở dưới mọi hình thức',
                'group' => 'housing', 'type' => 'boolean', 'weight' => 2,
                'options' => [
                    ['Chưa được hỗ trợ', 'no', 'pass', 2, 'Đáp ứng điều kiện'],
                    ['Đã được hỗ trợ', 'yes', 'fail', 0, 'Không đáp ứng điều kiện'],
                ],
            ],
            [
                'q' => 'Số thành viên trong hộ gia đình của bạn là bao nhiêu?',
                'label' => 'Số thành viên trong hộ gia đình',
                'group' => 'other', 'type' => 'select', 'weight' => 1,
                'options' => [
                    ['1 thành viên', '1', 'pass', 1, 'Phù hợp quy định'],
                    ['2 thành viên', '2', 'pass', 1, 'Phù hợp quy định'],
                    ['3 thành viên', '3', 'pass', 1, 'Phù hợp quy định'],
                    ['4 thành viên', '4', 'pass', 1, 'Phù hợp quy định'],
                    ['Từ 5 thành viên trở lên', '5+', 'pass', 1, 'Phù hợp quy định'],
                ],
            ],
            [
                'q' => 'Bạn đang cư trú, làm việc tại tỉnh/thành phố nơi có dự án NOXH?',
                'label' => 'Cư trú, làm việc tại tỉnh/thành phố có dự án NOXH',
                'group' => 'other', 'type' => 'boolean', 'weight' => 2,
                'options' => [
                    ['Có', 'yes', 'pass', 2, 'Đáp ứng điều kiện'],
                    ['Không', 'no', 'unclear', 0, 'Vui lòng cung cấp thêm'],
                ],
            ],
            [
                'q' => 'Tổng thu nhập bình quân hàng tháng của hộ gia đình bạn là bao nhiêu?',
                'label' => 'Tổng thu nhập bình quân hàng tháng của hộ gia đình',
                'group' => 'income', 'type' => 'select', 'weight' => 3,
                'options' => [
                    ['Dưới 15 triệu đồng', 'lt15', 'pass', 3, 'Trong mức quy định'],
                    ['Từ 15 đến 25 triệu đồng', '15-25', 'pass', 3, 'Trong mức quy định'],
                    ['Từ 25 đến 35 triệu đồng', '25-35', 'unclear', 1, 'Tuỳ nhóm đối tượng'],
                    ['Từ 35 đến 50 triệu đồng', '35-50', 'unclear', 1, 'Chỉ áp dụng với hai vợ chồng'],
                    ['Trên 50 triệu đồng', 'gt50', 'fail', 0, 'Vượt mức quy định'],
                ],
            ],
            [
                'q' => 'Bạn thuộc nhóm đối tượng nào dưới đây?',
                'label' => 'Đối tượng được mua NOXH',
                'group' => 'subject', 'type' => 'select', 'weight' => 3,
                'options' => [
                    ['Người có công với cách mạng', 'cach-mang', 'pass', 3, 'Đáp ứng điều kiện'],
                    ['Hộ nghèo, cận nghèo', 'ho-ngheo', 'pass', 3, 'Đáp ứng điều kiện'],
                    ['Người thu nhập thấp tại đô thị', 'thu-nhap-thap', 'pass', 3, 'Đáp ứng điều kiện'],
                    ['Công nhân khu công nghiệp', 'cong-nhan', 'pass', 3, 'Đáp ứng điều kiện'],
                    ['Cán bộ, công chức, viên chức', 'can-bo', 'pass', 3, 'Đáp ứng điều kiện'],
                    ['Lực lượng vũ trang', 'luc-luong-vu-trang', 'pass', 3, 'Đáp ứng điều kiện'],
                    ['Không thuộc nhóm nào ở trên', 'khong', 'fail', 0, 'Không đáp ứng điều kiện'],
                ],
            ],
            [
                'q' => 'Bạn có đang nộp thuế thu nhập cá nhân thường xuyên không?',
                'label' => 'Tình trạng nộp thuế thu nhập cá nhân',
                'group' => 'income', 'type' => 'boolean', 'weight' => 1,
                'options' => [
                    ['Không', 'no', 'pass', 1, 'Đáp ứng điều kiện'],
                    ['Có', 'yes', 'unclear', 0, 'Cần đối chiếu mức thu nhập'],
                ],
            ],
            [
                'q' => 'Bạn đã kết hôn chưa?',
                'label' => 'Tình trạng hôn nhân',
                'group' => 'other', 'type' => 'boolean', 'weight' => 1,
                'options' => [
                    ['Chưa kết hôn', 'single', 'pass', 1, 'Áp dụng ngưỡng thu nhập người độc thân'],
                    ['Đã kết hôn', 'married', 'pass', 1, 'Áp dụng ngưỡng thu nhập hai vợ chồng'],
                ],
            ],
        ];

        DB::table('eligibility_options')->delete();
        DB::table('eligibility_questions')->delete();

        $now = now();
        $soDapAn = 0;

        foreach ($questions as $i => $q) {
            $qid = DB::table('eligibility_questions')->insertGetId([
                'question' => $q['q'],
                'group' => $q['group'],
                'input_type' => $q['type'],
                'criteria_label' => $q['label'],
                'weight' => $q['weight'],
                'required' => true,
                'publish' => 2,
                'order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($q['options'] as $oi => [$label, $value, $verdict, $score, $note]) {
                DB::table('eligibility_options')->insert([
                    'eligibility_question_id' => $qid,
                    'label' => $label,
                    'value' => $value,
                    'verdict' => $verdict,
                    'score' => $score,
                    'note' => $note,
                    'order' => $oi,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $soDapAn++;
            }
        }

        $this->command->line(sprintf('  cau hoi dieu kien   : %d  (%d dap an)', count($questions), $soDapAn));
    }
}
