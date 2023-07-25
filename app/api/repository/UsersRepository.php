<?php

namespace App\api\repository;

use App\api\model\Users;
use App\core\BaseRepository;

class UsersRepository extends BaseRepository
{

    private array $validate_users_table = [
        "user_id",
        "user_login",
        "user_pass",
        "user_email",
        "user_nicename",
        "user_status",
        "mobile_verify",
        "email_verify",
        "email_token",
        "mobile_token",
        "mobile_token_exp",
        "mobile_token_attempts",
        "created_at",
        "updated_at"
    ];

    public function __construct()
    {
        $this->model = Users::class;
        $this->validate_select = $this->validate_users_table;
    }

}