<?php

namespace App\api\repository;

use App\api\model\UserMeta;
use App\core\BaseRepository;

class UserMetaRepository extends BaseRepository
{

    private array $validate_user_meta_table = [
        "umeta_id",
        "user_id",
        "meta_key",
        "meta_value",
        "created_at",
        "updated_at"
    ];

    public function __construct()
    {
        $this->model = UserMeta::class;
        $this->validate_select = $this->validate_user_meta_table;
    }

}