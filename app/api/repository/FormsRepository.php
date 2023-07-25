<?php

namespace App\api\repository;

use App\api\model\Forms;
use App\core\BaseRepository;

class FormsRepository extends BaseRepository
{
    private array $validate_forms_table = [
        "form_id",
        "user_id",
        "form_status",
        "created_at",
        "updated_at"
    ];

    public function __construct()
    {
        $this->model = Forms::class;
        $this->validate_select = $this->validate_forms_table;
    }

}