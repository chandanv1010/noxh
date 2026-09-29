<?php

namespace App\Services\V1\Project;

use App\Repositories\Noxh\ProjectUnitRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Namespace phai la App\Services\V1\Project de cong tac bat/tat hien thi tim
 * thay lop nay: no ghep duong dan tu ten model "ProjectUnit" -> tu dau "Project".
 */
class ProjectUnitService extends BaseService
{
    protected $unitRepository;

    public function __construct(ProjectUnitRepository $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;
        $keyword = trim((string) $request->input('keyword'));

        return $this->unitRepository->phanTrang(
            $request->integer('product_id') ?: null,
            $keyword !== '' ? $keyword : null,
            $perPage
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->unitRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them project/unit that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->unitRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat project/unit that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->unitRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa project/unit that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        // O so de trong phai thanh NULL chu khong phai 0: "0 m2" hien ra
        // ngoai trang la sai, con NULL thi trang tu bo dong do.
        foreach (['area_from', 'area_to', 'price_from', 'price_to'] as $o) {
            $payload[$o] = ($payload[$o] ?? '') === '' ? null : $payload[$o];
        }

        $payload['price_unit'] = trim((string) ($payload['price_unit'] ?? '')) ?: 'tỷ';
        $payload['publish'] = $request->integer('publish') ?: 2;
        $payload['order'] = $request->integer('order');

        return $payload;
    }
}
