<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\DossierSet;

class DossierSetRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        DossierSet $model
    ) {
        $this->model = $model;
    }
}
