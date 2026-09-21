<?php

namespace App\Services\V1\Project;

use App\Repositories\Noxh\ProjectDocumentRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Namespace phai la App\Services\V1\Project de cong tac bat/tat hien thi
 * tim thay lop nay: no ghep duong dan tu ten model "ProjectDocument" -> tu dau "Project".
 */
class ProjectDocumentService extends BaseService
{
    protected $documentRepository;

    public function __construct(
        ProjectDocumentRepository $documentRepository
    ) {
        $this->documentRepository = $documentRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;
        $keyword = trim((string) $request->input('keyword'));

        return $this->documentRepository->phanTrang(
            $request->integer('product_id') ?: null,
            $keyword !== '' ? $keyword : null,
            $perPage
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->documentRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them project/document that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->documentRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat project/document that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->documentRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa project/document that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['issued_date'] = empty($payload['issued_date']) ? null : $payload['issued_date'];
        $payload['order'] = $request->integer('order');
        $payload['publish'] = $request->integer('publish') ?: 2;

        return $payload;
    }
}
