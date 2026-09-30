<?php

namespace App\Services\V1\Eligibility;

use App\Repositories\Noxh\EligibilityCriterionRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lop nay PHAI nam trong App\Services\V1\Eligibility: cong tac bat/tat hien
 * thi tu ghep duong dan lop tu ten model "EligibilityCriterion".
 */
class EligibilityCriterionService extends BaseService
{
    protected $criterionRepository;

    public function __construct(
        EligibilityCriterionRepository $criterionRepository
    ) {
        $this->criterionRepository = $criterionRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $where[] = ['label', 'LIKE', '%' . $keyword . '%'];
        }

        return $this->criterionRepository->pagination(
            ['*'],
            ['keyword' => null, 'publish' => $request->integer('publish'), 'where' => $where],
            $perPage,
            ['path' => 'eligibility/criterion/index'],
            ['order', 'ASC'],
            [],
            []
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->criterionRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them eligibility criterion that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->criterionRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat eligibility criterion that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->criterionRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa eligibility criterion that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['order'] = !isset($payload['order']) || $payload['order'] === '' ? 0 : $payload['order'];

        // Tieu chi khong lay tu cau hoi thi bo han lien ket, de lai la sau
        // nay doi nguon ma quen xoa, nhin bang danh sach thay lung tung.
        if (($payload['source'] ?? '') !== 'question') {
            $payload['eligibility_question_id'] = null;
        }

        return $payload;
    }
}
