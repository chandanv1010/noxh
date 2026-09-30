<?php

namespace App\Repositories\Noxh;

use App\Models\EligibilityOptionGroup;
use App\Repositories\BaseRepository;

class EligibilityOptionGroupRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        EligibilityOptionGroup $model
    ) {
        $this->model = $model;
    }
}
