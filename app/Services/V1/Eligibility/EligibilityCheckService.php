<?php

namespace App\Services\V1\Eligibility;

use App\Models\EligibilityCheck;
use App\Repositories\Noxh\EligibilityCheckRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EligibilityCheckService extends BaseService
{
    protected $checkRepository;

    public function __construct(
        EligibilityCheckRepository $checkRepository
    ) {
        $this->checkRepository = $checkRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $keyword = trim((string) $request->input('keyword'));
        $muc = $request->input('result_level');

        if (!array_key_exists((string) $muc, EligibilityCheck::MUC)) {
            $muc = null;
        }

        return $this->checkRepository->phanTrang($keyword !== '' ? $keyword : null, $muc, $perPage);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->checkRepository->forceDelete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa luot kiem tra dieu kien that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function demTheoMuc(): array
    {
        return $this->checkRepository->demTheoMuc();
    }
}
