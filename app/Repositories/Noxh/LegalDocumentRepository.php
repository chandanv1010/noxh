<?php

namespace App\Repositories\Noxh;

use App\Repositories\BaseRepository;
use App\Models\LegalDocument;

class LegalDocumentRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        LegalDocument $model
    ) {
        $this->model = $model;
    }
}
