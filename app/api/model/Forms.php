<?php

namespace App\api\model;

use App\api\model\contract\BaseModel;

class Forms extends BaseModel
{
    protected $table = "forms";
    protected $primaryKey = "form_id";
}