<?php

namespace App\api\model;

use App\api\model\contract\BaseModel;

class UserMeta extends BaseModel
{
    protected $table = "user_meta";
    protected $primaryKey = "umeta_id";
}