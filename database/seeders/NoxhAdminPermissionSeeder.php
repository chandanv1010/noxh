<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Nap quyen cho toan bo module quan tri moi cua NOXH roi gan cho nhom quan tri.
 *
 * Gate 'modules' chan theo cot canonical cua bang permissions - thieu dong o
 * day la mo menu ra bi bao 403 du code da day du.
 *
 * Chay lai nhieu lan khong sinh ban ghi trung.
 */
class NoxhAdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $quyen = [
            'investor.index' => 'Xem danh sách chủ đầu tư',
            'investor.create' => 'Thêm mới chủ đầu tư',
            'investor.update' => 'Cập nhật chủ đầu tư',
            'investor.destroy' => 'Xóa chủ đầu tư',
            'legal.document.index' => 'Xem danh sách văn bản pháp luật',
            'legal.document.create' => 'Thêm mới văn bản pháp luật',
            'legal.document.update' => 'Cập nhật văn bản pháp luật',
            'legal.document.destroy' => 'Xóa văn bản pháp luật',
            'expert.index' => 'Xem danh sách chuyên gia',
            'expert.create' => 'Thêm mới chuyên gia',
            'expert.update' => 'Cập nhật chuyên gia',
            'expert.destroy' => 'Xóa chuyên gia',
            'loan.package.index' => 'Xem danh sách gói vay',
            'loan.package.create' => 'Thêm mới gói vay',
            'loan.package.update' => 'Cập nhật gói vay',
            'loan.package.destroy' => 'Xóa gói vay',
            'dossier.set.index' => 'Xem danh sách bộ hồ sơ',
            'dossier.set.create' => 'Thêm mới bộ hồ sơ',
            'dossier.set.update' => 'Cập nhật bộ hồ sơ',
            'dossier.set.destroy' => 'Xóa bộ hồ sơ',
            'dossier.item.index' => 'Xem danh sách giấy tờ',
            'dossier.item.create' => 'Thêm mới giấy tờ',
            'dossier.item.update' => 'Cập nhật giấy tờ',
            'dossier.item.destroy' => 'Xóa giấy tờ',
            'eligibility.question.index' => 'Xem danh sách câu hỏi điều kiện',
            'eligibility.question.create' => 'Thêm mới câu hỏi điều kiện',
            'eligibility.question.update' => 'Cập nhật câu hỏi điều kiện',
            'eligibility.question.destroy' => 'Xóa câu hỏi điều kiện',
            'eligibility.option.index' => 'Xem danh sách đáp án',
            'eligibility.option.create' => 'Thêm mới đáp án',
            'eligibility.option.update' => 'Cập nhật đáp án',
            'eligibility.option.destroy' => 'Xóa đáp án',
            'eligibility.check.index' => 'Xem kết quả kiểm tra điều kiện',
            'eligibility.check.destroy' => 'Xóa lượt kiểm tra điều kiện',
            'qa.question.index' => 'Xem danh sách hỏi đáp',
            'qa.question.update' => 'Trả lời và duyệt câu hỏi',
            'qa.question.destroy' => 'Xóa câu hỏi hỏi đáp',
            'project.milestone.index' => 'Xem danh sách mốc tiến độ dự án',
            'project.milestone.create' => 'Thêm mới mốc tiến độ dự án',
            'project.milestone.update' => 'Cập nhật mốc tiến độ dự án',
            'project.milestone.destroy' => 'Xóa mốc tiến độ dự án',
            'project.document.index' => 'Xem danh sách hồ sơ pháp lý dự án',
            'project.document.create' => 'Thêm mới hồ sơ pháp lý dự án',
            'project.document.update' => 'Cập nhật hồ sơ pháp lý dự án',
            'project.document.destroy' => 'Xóa hồ sơ pháp lý dự án',
            'project.faq.index' => 'Xem danh sách câu hỏi thường gặp của dự án',
            'project.faq.create' => 'Thêm mới câu hỏi thường gặp của dự án',
            'project.faq.update' => 'Cập nhật câu hỏi thường gặp của dự án',
            'project.faq.destroy' => 'Xóa câu hỏi thường gặp của dự án',
        ];

        $now = now();
        $ids = [];

        foreach ($quyen as $canonical => $ten) {
            $dong = DB::table('permissions')->where('canonical', $canonical)->first();

            if ($dong) {
                $ids[] = $dong->id;
                continue;
            }

            $ids[] = DB::table('permissions')->insertGetId([
                'name' => $ten,
                'canonical' => $canonical,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $nhomQuanTri = DB::table('user_catalogues')->orderBy('id')->value('id');

        if (!$nhomQuanTri) {
            $this->command->warn('Chua co nhom nguoi dung nao - bo qua buoc gan quyen.');
            return;
        }

        $daCo = DB::table('user_catalogue_permission')
            ->where('user_catalogue_id', $nhomQuanTri)
            ->pluck('permission_id')
            ->toArray();

        $canThem = array_diff($ids, $daCo);

        if (count($canThem)) {
            DB::table('user_catalogue_permission')->insert(
                array_map(fn($id) => [
                    'user_catalogue_id' => $nhomQuanTri,
                    'permission_id' => $id,
                ], $canThem)
            );
        }

        $this->command->info(sprintf(
            'Quyen NOXH: %d dong, gan them %d quyen cho nhom nguoi dung #%d.',
            count($ids),
            count($canThem),
            $nhomQuanTri
        ));
    }
}
