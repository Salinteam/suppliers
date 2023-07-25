<?php

namespace App\core;

class Routing
{

    public array $routes = [
        [
            "route" => "auth",
            "module" => "api",
            "version" => "v1",
            "controller" => "AuthController"
        ],
        [
            'route' => "media",
            'module' => 'api',
            'version' => 'v1',
            'controller' => 'MediaController'
        ],
        [
            'route' => "forms",
            'module' => 'api',
            'version' => 'v1',
            'controller' => 'FormController'
        ]
    ];

    public function __construct()
    {
        return $this->routes;
    }

}