<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\EligibilityCheck;

class EligibilityCheckRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        EligibilityCheck $model
    ) {
        $this->model = $model;
    }

    /**
     * O tim kiem phai do ca ma tra cuu, ho ten LAN so dien thoai - khach goi
     * len thuong chi doc mot trong ba thu do.
     */
    public function phanTrang(?string $keyword, ?string $muc, int $perPage)
    {
        $query = $this->model->newQuery();

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('name', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('phone', 'LIKE', '%' . $keyword . '%');
            });
        }

        if (!empty($muc)) {
            $query->where('result_level', $muc);
        }

        return $query->orderBy('id', 'DESC')
            ->paginate($perPage)
            ->withQueryString()
            ->withPath(env('APP_URL') . 'eligibility/check/index');
    }

    public function demTheoMuc(): array
    {
        return $this->model->selectRaw('result_level, COUNT(*) AS so_luot')
            ->groupBy('result_level')
            ->pluck('so_luot', 'result_level')
            ->toArray();
    }
}
