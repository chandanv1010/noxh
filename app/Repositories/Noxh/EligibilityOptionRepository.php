<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\EligibilityOption;

class EligibilityOptionRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        EligibilityOption $model
    ) {
        $this->model = $model;
    }
}
