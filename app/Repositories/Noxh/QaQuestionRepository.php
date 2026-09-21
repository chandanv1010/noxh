<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\QaQuestion;

class QaQuestionRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        QaQuestion $model
    ) {
        $this->model = $model;
    }

    public function phanTrang(?string $keyword, ?string $trangThai, int $perPage)
    {
        $query = $this->model->newQuery()->withCount('answers');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('asker_name', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('asker_phone', 'LIKE', '%' . $keyword . '%');
            });
        }

        if (!empty($trangThai)) {
            $query->where('status', $trangThai);
        }

        return $query->orderBy('id', 'DESC')
            ->paginate($perPage)
            ->withQueryString()
            ->withPath(env('APP_URL') . 'qa/question/index');
    }

    public function demTheoTrangThai(): array
    {
        return $this->model->selectRaw('status, COUNT(*) AS so_cau')
            ->groupBy('status')
            ->pluck('so_cau', 'status')
            ->toArray();
    }
}
