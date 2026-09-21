<?php

namespace App\Services\V1\Eligibility;

use App\Repositories\Noxh\EligibilityQuestionRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lop nay PHAI nam trong App\Services\V1\Eligibility: cong tac bat/tat hien thi
 * (Ajax\DashboardController::changeStatus) tu ghep duong dan lop tu ten model
 * "EligibilityQuestion" -> tu dau la "Eligibility". Doi cho khac la cong tac chet lang.
 */
class EligibilityQuestionService extends BaseService
{
    protected $questionRepository;

    public function __construct(
        EligibilityQuestionRepository $questionRepository
    ) {
        $this->questionRepository = $questionRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        if ($request->input('group')) {
            $where[] = ['group', '=', $request->input('group')];
        }
        // Tu loc theo tu khoa thay vi dung scope keyword mac dinh - scope do
        // chi do cot `name`, ma bang nay can do question / criteria_label.
        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $where[] = ['question', 'LIKE', '%' . $keyword . '%'];
        }

        $condition = [
            'keyword' => null,
            'publish' => $request->integer('publish'),
            'where' => $where,
        ];

        return $this->questionRepository->pagination(
            ['*'],
            $condition,
            $perPage,
            ['path' => 'eligibility/question/index'],
            ['order', 'ASC'],
            [],
            ['options']
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->questionRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them eligibility question that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->questionRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat eligibility question that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->questionRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa eligibility question that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['weight'] = !isset($payload['weight']) || $payload['weight'] === '' ? null : $payload['weight'];
        // O tich khong duoc gui len khi bo tich, phai tu dat ve 0.
        $payload['required'] = $request->boolean('required') ? 1 : 0;
        $payload['order'] = $request->integer('order');
        $payload['publish'] = $request->integer('publish') ?: 2;

        return $payload;
    }
}
