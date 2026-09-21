<?php

namespace App\Services\V1\Dossier;

use App\Repositories\Noxh\DossierItemRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lop nay PHAI nam trong App\Services\V1\Dossier: cong tac bat/tat hien thi
 * (Ajax\DashboardController::changeStatus) tu ghep duong dan lop tu ten model
 * "DossierItem" -> tu dau la "Dossier". Doi cho khac la cong tac chet lang.
 */
class DossierItemService extends BaseService
{
    protected $itemRepository;

    public function __construct(
        DossierItemRepository $itemRepository
    ) {
        $this->itemRepository = $itemRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        if ($request->integer('dossier_set_id') > 0) {
            $where[] = ['dossier_set_id', '=', $request->integer('dossier_set_id')];
        }
        // Tu loc theo tu khoa thay vi dung scope keyword mac dinh - scope do
        // chi do cot `name`, ma bang nay can do title / issued_by.
        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $where[] = ['title', 'LIKE', '%' . $keyword . '%'];
        }

        $condition = [
            'keyword' => null,
            'publish' => $request->integer('publish'),
            'where' => $where,
        ];

        return $this->itemRepository->pagination(
            ['*'],
            $condition,
            $perPage,
            ['path' => 'dossier/item/index'],
            ['order', 'ASC'],
            [],
            []
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->itemRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them dossier item that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->itemRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat dossier item that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->itemRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa dossier item that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['copies'] = !isset($payload['copies']) || $payload['copies'] === '' ? null : $payload['copies'];
        // O tich khong duoc gui len khi bo tich, phai tu dat ve 0.
        $payload['is_required'] = $request->boolean('is_required') ? 1 : 0;
        $payload['order'] = $request->integer('order');
        $payload['publish'] = $request->integer('publish') ?: 2;

        return $payload;
    }
}
