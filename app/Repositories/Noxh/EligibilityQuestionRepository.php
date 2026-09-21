<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\EligibilityQuestion;

class EligibilityQuestionRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        EligibilityQuestion $model
    ) {
        $this->model = $model;
    }
}
