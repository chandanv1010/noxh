<?php

namespace App\Services\V1\Eligibility;

use App\Repositories\Noxh\EligibilityOptionGroupRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lop nay PHAI nam trong App\Services\V1\Eligibility: cong tac bat/tat hien thi
 * tu ghep duong dan lop tu ten model "EligibilityOptionGroup" -> tu dau la
 * "Eligibility". Doi cho khac la cong tac chet lang.
 */
class EligibilityOptionGroupService extends BaseService
{
    protected $groupRepository;

    public function __construct(
        EligibilityOptionGroupRepository $groupRepository
    ) {
        $this->groupRepository = $groupRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        if ($request->integer('eligibility_question_id') > 0) {
            $where[] = ['eligibility_question_id', '=', $request->integer('eligibility_question_id')];
        }

        // Tu loc theo tu khoa thay vi dung scope keyword mac dinh - scope do
        // chi do cot `name`, ma bang nay can do label.
        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $where[] = ['label', 'LIKE', '%' . $keyword . '%'];
        }

        $condition = [
            'keyword' => null,
            'publish' => $request->integer('publish'),
            'where' => $where,
        ];

        return $this->groupRepository->pagination(
            ['*'],
            $condition,
            $perPage,
            ['path' => 'eligibility/group/index'],
            ['order', 'ASC'],
            [],
            []
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->groupRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them eligibility option group that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->groupRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat eligibility option group that bai: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xoa mot tam thi cac dap an trong tam do KHONG bi xoa theo - chi go
     * lien ket. Dap an con giu diem va ket luan, quan tri xep lai sang tam
     * khac duoc; xoa nham mot tam ma mat luon nam dap an thi khong cuu lai
     * duoc.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            DB::table('eligibility_options')
                ->where('eligibility_option_group_id', $id)
                ->update(['eligibility_option_group_id' => null]);

            $this->groupRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa eligibility option group that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['order'] = !isset($payload['order']) || $payload['order'] === '' ? 0 : $payload['order'];

        return $payload;
    }
}
