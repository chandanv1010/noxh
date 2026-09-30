<?php

namespace App\Repositories\Noxh;

use App\Models\EligibilityCriterion;
use App\Repositories\BaseRepository;

class EligibilityCriterionRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        EligibilityCriterion $model
    ) {
        $this->model = $model;
    }
}
