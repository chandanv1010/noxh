<?php

namespace App\Repositories\Noxh;

use App\Models\EligibilityPanel;
use App\Repositories\BaseRepository;

class EligibilityPanelRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        EligibilityPanel $model
    ) {
        $this->model = $model;
    }
}
