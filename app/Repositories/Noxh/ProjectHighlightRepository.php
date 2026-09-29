<?php

namespace App\Repositories\Noxh;

use App\Models\ProjectHighlight;
use App\Repositories\BaseRepository;

class ProjectHighlightRepository extends BaseRepository
{
    protected $model;

    public function __construct(ProjectHighlight $model)
    {
        $this->model = $model;
    }

    public function phanTrang(?int $duAnId, ?string $keyword, int $perPage, ?string $nhom = null)
    {
        $query = $this->model->newQuery()->with('project');

        if ($duAnId) {
            $query->where('product_id', $duAnId);
        }

        if ($nhom) {
            $query->where('group', $nhom);
        }

        if (!empty($keyword)) {
            $query->where('title', 'LIKE', '%' . $keyword . '%');
        }

        return $query->orderBy('product_id')
            ->orderBy('group')
            ->orderBy('order')
            ->paginate($perPage)
            ->withQueryString()
            ->withPath(env('APP_URL') . 'project/highlight/index');
    }
}
