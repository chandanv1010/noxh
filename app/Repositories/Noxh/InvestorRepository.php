<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\Investor;

class InvestorRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        Investor $model
    ) {
        $this->model = $model;
    }
}
