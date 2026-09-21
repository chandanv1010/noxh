<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\LoanPackage;

class LoanPackageRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        LoanPackage $model
    ) {
        $this->model = $model;
    }
}
