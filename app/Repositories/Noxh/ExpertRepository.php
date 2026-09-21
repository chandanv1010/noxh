<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\Expert;

class ExpertRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        Expert $model
    ) {
        $this->model = $model;
    }
}
