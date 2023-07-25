<?php

namespace App\api\repository;

use App\api\model\FormMeta;
use App\core\BaseRepository;

class FormMetaRepository extends BaseRepository
{
    private array $validate_form_meta_table = [
        "fmeta_id",
        "form_id",
        "meta_key",
        "meta_value",
        "created_at",
        "updated_at"
    ];

    public function __construct()
    {
        $this->model = FormMeta::class;
        $this->validate_select = $this->validate_form_meta_table;
    }

}