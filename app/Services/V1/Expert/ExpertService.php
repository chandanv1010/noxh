<?php

namespace App\Services\V1\Expert;

use App\Repositories\Noxh\ExpertRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lop nay PHAI nam trong App\Services\V1\Expert: cong tac bat/tat hien thi
 * (Ajax\DashboardController::changeStatus) tu ghep duong dan lop tu ten model
 * "Expert" -> tu dau la "Expert". Doi cho khac la cong tac chet lang.
 */
class ExpertService extends BaseService
{
    protected $expertRepository;

    public function __construct(
        ExpertRepository $expertRepository
    ) {
        $this->expertRepository = $expertRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        // Tu loc theo tu khoa thay vi dung scope keyword mac dinh - scope do
        // chi do cot `name`, ma bang nay can do name / title / phone.
        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $where[] = ['name', 'LIKE', '%' . $keyword . '%'];
        }

        $condition = [
            'keyword' => null,
            'publish' => $request->integer('publish'),
            'where' => $where,
        ];

        return $this->expertRepository->pagination(
            ['*'],
            $condition,
            $perPage,
            ['path' => 'expert/index'],
            ['order', 'ASC'],
            [],
            []
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->expertRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them expert that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->expertRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat expert that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->expertRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa expert that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        // O tich khong duoc gui len khi bo tich, phai tu dat ve 0.
        $payload['is_default'] = $request->boolean('is_default') ? 1 : 0;
        $payload['order'] = $request->integer('order');
        $payload['publish'] = $request->integer('publish') ?: 2;

        return $payload;
    }
}
