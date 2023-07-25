<?php

namespace App\api\model;

use App\api\model\contract\BaseModel;

class MediaMeta extends BaseModel
{
    protected $table = "media_meta";
    protected $primaryKey = "mmeta_id";
}