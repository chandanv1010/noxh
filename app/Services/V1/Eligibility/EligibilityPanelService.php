<?php

namespace App\Services\V1\Eligibility;

use App\Repositories\Noxh\EligibilityPanelRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lop nay PHAI nam trong App\Services\V1\Eligibility: cong tac bat/tat hien thi
 * tu ghep duong dan lop tu ten model "EligibilityPanel" -> tu dau la
 * "Eligibility". Doi cho khac la cong tac chet lang.
 */
class EligibilityPanelService extends BaseService
{
    protected $panelRepository;

    public function __construct(
        EligibilityPanelRepository $panelRepository
    ) {
        $this->panelRepository = $panelRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        if ($request->integer('eligibility_question_id') > 0) {
            $where[] = ['eligibility_question_id', '=', $request->integer('eligibility_question_id')];
        }

        // Tu loc theo tu khoa thay vi dung scope keyword mac dinh - scope do
        // chi do cot `name`, ma bang nay can do heading.
        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $where[] = ['heading', 'LIKE', '%' . $keyword . '%'];
        }

        return $this->panelRepository->pagination(
            ['*'],
            ['keyword' => null, 'publish' => $request->integer('publish'), 'where' => $where],
            $perPage,
            ['path' => 'eligibility/panel/index'],
            ['order', 'ASC'],
            [],
            []
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->panelRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them eligibility panel that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->panelRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat eligibility panel that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->panelRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa eligibility panel that bai: ' . $e->getMessage());
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
