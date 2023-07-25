<?php

namespace App\api\model;

use App\api\model\contract\BaseModel;

class Media extends BaseModel
{
    protected $table = "media";
    protected $primaryKey = "media_id";
}