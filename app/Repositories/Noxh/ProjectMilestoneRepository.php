<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\ProjectMilestone;

class ProjectMilestoneRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        ProjectMilestone $model
    ) {
        $this->model = $model;
    }

    /**
     * Danh sach luon gom theo du an. Khong dung BaseRepository::pagination()
     * vi ham do goi scope keyword() do cot `name` - ba bang nay khong co cot
     * do, va ta con can nap san quan he du an de khoi sinh N+1.
     */
    public function phanTrang(?int $duAnId, ?string $keyword, int $perPage)
    {
        $query = $this->model->newQuery()->with('project');

        if ($duAnId) {
            $query->where('product_id', $duAnId);
        }

        if (!empty($keyword)) {
            $query->where('title', 'LIKE', '%' . $keyword . '%');
        }

        return $query->orderBy('product_id')
            ->orderBy('order')
            ->paginate($perPage)
            ->withQueryString()
            ->withPath(env('APP_URL') . 'project/milestone/index');
    }
}
