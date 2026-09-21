<?php

namespace App\Services\V1\Legal;

use App\Repositories\Noxh\LegalDocumentRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lop nay PHAI nam trong App\Services\V1\Legal: cong tac bat/tat hien thi
 * (Ajax\DashboardController::changeStatus) tu ghep duong dan lop tu ten model
 * "LegalDocument" -> tu dau la "Legal". Doi cho khac la cong tac chet lang.
 */
class LegalDocumentService extends BaseService
{
    protected $documentRepository;

    public function __construct(
        LegalDocumentRepository $documentRepository
    ) {
        $this->documentRepository = $documentRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        if ($request->input('doc_type')) {
            $where[] = ['doc_type', '=', $request->input('doc_type')];
        }
        // Tu loc theo tu khoa thay vi dung scope keyword mac dinh - scope do
        // chi do cot `name`, ma bang nay can do title / doc_number / issuer.
        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $where[] = ['title', 'LIKE', '%' . $keyword . '%'];
        }

        $condition = [
            'keyword' => null,
            'publish' => $request->integer('publish'),
            'where' => $where,
        ];

        return $this->documentRepository->pagination(
            ['*'],
            $condition,
            $perPage,
            ['path' => 'legal-document/index'],
            ['effective_date', 'DESC'],
            [],
            []
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
            Log::error('Them legal-document that bai: ' . $e->getMessage());
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
            Log::error('Cap nhat legal-document that bai: ' . $e->getMessage());
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
            Log::error('Xoa legal-document that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['issued_date'] = empty($payload['issued_date']) ? null : $payload['issued_date'];
        $payload['effective_date'] = empty($payload['effective_date']) ? null : $payload['effective_date'];
        // O tich khong duoc gui len khi bo tich, phai tu dat ve 0.
        $payload['is_featured'] = $request->boolean('is_featured') ? 1 : 0;
        $payload['order'] = $request->integer('order');
        $payload['publish'] = $request->integer('publish') ?: 2;

        return $payload;
    }
}
