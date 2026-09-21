<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\DossierItem;

class DossierItemRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        DossierItem $model
    ) {
        $this->model = $model;
    }
}
