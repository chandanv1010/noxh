<?php

namespace App\Services\V1\Project;

use App\Repositories\Noxh\ProjectMilestoneRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Namespace phai la App\Services\V1\Project de cong tac bat/tat hien thi
 * tim thay lop nay: no ghep duong dan tu ten model "ProjectMilestone" -> tu dau "Project".
 */
class ProjectMilestoneService extends BaseService
{
    protected $milestoneRepository;

    public function __construct(
        ProjectMilestoneRepository $milestoneRepository
    ) {
        $this->milestoneRepository = $milestoneRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;
        $keyword = trim((string) $request->input('keyword'));

        return $this->milestoneRepository->phanTrang(
            $request->integer('product_id') ?: null,
            $keyword !== '' ? $keyword : null,
            $perPage
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->milestoneRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them project/milestone that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->milestoneRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat project/milestone that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->milestoneRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa project/milestone that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['sort_date'] = empty($payload['sort_date']) ? null : $payload['sort_date'];
        $payload['order'] = $request->integer('order');

        return $payload;
    }
}
