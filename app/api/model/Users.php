<?php

namespace App\api\model;

use App\api\model\contract\BaseModel;

class Users extends BaseModel
{
    protected $table = "users";
    protected $primaryKey = "user_id";
}