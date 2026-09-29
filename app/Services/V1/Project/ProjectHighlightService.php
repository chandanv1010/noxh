<?php

namespace App\Services\V1\Project;

use App\Repositories\Noxh\ProjectHighlightRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectHighlightService extends BaseService
{
    protected $highlightRepository;

    public function __construct(ProjectHighlightRepository $highlightRepository)
    {
        $this->highlightRepository = $highlightRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;
        $keyword = trim((string) $request->input('keyword'));

        return $this->highlightRepository->phanTrang(
            $request->integer('product_id') ?: null,
            $keyword !== '' ? $keyword : null,
            $perPage,
            in_array($request->input('group'), ['price', 'amenity'], true) ? $request->input('group') : null
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->highlightRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them project/highlight that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->highlightRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat project/highlight that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->highlightRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa project/highlight that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);
        $payload['group'] = in_array($payload['group'] ?? '', ['price', 'amenity'], true) ? $payload['group'] : 'price';
        $payload['order'] = $request->integer('order');

        return $payload;
    }
}
