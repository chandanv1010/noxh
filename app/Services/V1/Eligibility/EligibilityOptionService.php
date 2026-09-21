<?php

namespace App\Services\V1\Eligibility;

use App\Repositories\Noxh\EligibilityOptionRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lop nay PHAI nam trong App\Services\V1\Eligibility: cong tac bat/tat hien thi
 * (Ajax\DashboardController::changeStatus) tu ghep duong dan lop tu ten model
 * "EligibilityOption" -> tu dau la "Eligibility". Doi cho khac la cong tac chet lang.
 */
class EligibilityOptionService extends BaseService
{
    protected $optionRepository;

    public function __construct(
        EligibilityOptionRepository $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        if ($request->integer('eligibility_question_id') > 0) {
            $where[] = ['eligibility_question_id', '=', $request->integer('eligibility_question_id')];
        }
        // Tu loc theo tu khoa thay vi dung scope keyword mac dinh - scope do
        // chi do cot `name`, ma bang nay can do label / value.
        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $where[] = ['label', 'LIKE', '%' . $keyword . '%'];
        }

        $condition = [
            'keyword' => null,
            'publish' => $request->integer('publish'),
            'where' => $where,
        ];

        return $this->optionRepository->pagination(
            ['*'],
            $condition,
            $perPage,
            ['path' => 'eligibility/option/index'],
            ['order', 'ASC'],
            [],
            []
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->optionRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them eligibility option that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->optionRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat eligibility option that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->optionRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa eligibility option that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['score'] = !isset($payload['score']) || $payload['score'] === '' ? null : $payload['score'];
        $payload['order'] = !isset($payload['order']) || $payload['order'] === '' ? null : $payload['order'];

        return $payload;
    }
}
