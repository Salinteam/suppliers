<?php

namespace App\api\controller;

use App\core\ApiController;

defined('ABSPATH') || die();

class DefaultController extends ApiController
{

    public function index()
    {
        require_once PROJECT_PATH . "vue.php";
    }

}