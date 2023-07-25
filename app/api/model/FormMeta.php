<?php

namespace App\api\model;

use App\api\model\contract\BaseModel;

class FormMeta extends BaseModel
{
    protected $table = "form_meta";
    protected $primaryKey = "fmeta_id";
}