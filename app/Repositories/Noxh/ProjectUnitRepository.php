<?php

namespace App\Repositories\Noxh;

use App\Models\ProjectUnit;
use App\Repositories\BaseRepository;

class ProjectUnitRepository extends BaseRepository
{
    protected $model;

    public function __construct(ProjectUnit $model)
    {
        $this->model = $model;
    }

    /**
     * Giong cac bang con khac cua du an: luon gom theo du an va nap san
     * quan he du an de khoi sinh N+1 khi ve bang danh sach.
     */
    public function phanTrang(?int $duAnId, ?string $keyword, int $perPage)
    {
        $query = $this->model->newQuery()->with('project');

        if ($duAnId) {
            $query->where('product_id', $duAnId);
        }

        if (!empty($keyword)) {
            $query->where('name', 'LIKE', '%' . $keyword . '%');
        }

        return $query->orderBy('product_id')
            ->orderBy('order')
            ->paginate($perPage)
            ->withQueryString()
            ->withPath(env('APP_URL') . 'project/unit/index');
    }
}
