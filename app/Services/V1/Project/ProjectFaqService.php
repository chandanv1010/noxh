<?php

namespace App\Services\V1\Project;

use App\Repositories\Noxh\ProjectFaqRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Namespace phai la App\Services\V1\Project de cong tac bat/tat hien thi
 * tim thay lop nay: no ghep duong dan tu ten model "ProjectFaq" -> tu dau "Project".
 */
class ProjectFaqService extends BaseService
{
    protected $faqRepository;

    public function __construct(
        ProjectFaqRepository $faqRepository
    ) {
        $this->faqRepository = $faqRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;
        $keyword = trim((string) $request->input('keyword'));

        return $this->faqRepository->phanTrang(
            $request->integer('product_id') ?: null,
            $keyword !== '' ? $keyword : null,
            $perPage
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->faqRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them project/faq that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->faqRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat project/faq that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->faqRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa project/faq that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['order'] = $request->integer('order');
        $payload['publish'] = $request->integer('publish') ?: 2;

        return $payload;
    }
}
