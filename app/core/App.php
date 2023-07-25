<?php

namespace App\core;

use App\api\controller\DefaultController;

defined('ABSPATH') || die();

class App
{

    public function __construct()
    {

        $routing = new Routing;
        $url = $this->parseUrl();
        $controller = new DefaultController();
        $action = "index";
        $params = [];

        /**
         * index = 0 -> application context
         * index = 1 -> version
         * index = 2 -> resource
         * index >= 3 -> parameter
         **/
        if (count($url) >= 3 && $url[0] == "api") {

            $urlPath = $url[0] . '/' . $url[1] . '/' . $url[2];

            foreach ($routing->routes as $route) {
                if ($route['module'] . '/' . $route['version'] . '/' . $route['route'] == $urlPath) {

                    $path_controller = PROJECT_PATH .
                        DIRECTORY_SEPARATOR .
                        "app" .
                        DIRECTORY_SEPARATOR .
                        $route['module'] .
                        DIRECTORY_SEPARATOR .
                        'controller' .
                        DIRECTORY_SEPARATOR .
                        $route['version'] .
                        DIRECTORY_SEPARATOR .
                        $route['controller'] .
                        '.php';

                    if (file_exists($path_controller)) {
                        $dynamicControllerName = "App\\" . $route['module'] . "\\controller\\" . $route['version'] . "\\" . $route['controller'];
                        $controller = new $dynamicControllerName;
                        $action = 'index';
                        unset($url[0], $url[1], $url[2]);
                        break;
                    }

                }
            }

            /** add parameter request in params **/
            if (count($url) > 0) {

                foreach ($url as $index => $value) {
                    if (empty($value)) {
                        unset($url[$index]);
                    }
                }

                $url = array_values($url);

                foreach ($url as $key => $value) {
                    $params['path'][$key] = $value;
                }

            } else {
                $params["path"] = [];
            }

            /** add query request in params **/
            if ($_GET) {
                foreach ($_GET as $key => $value) {
                    $params['query'][$key] = $value;
                }
            } else {
                $params["query"] = [];
            }

        }

        call_user_func([$controller, $action], $params);

        exit();

    }

    private function parseUrl(): array
    {
        $request = trim($_SERVER['REQUEST_URI'], '/');
        $request = strtok($request, '?');
        $request = filter_var($request, FILTER_SANITIZE_URL);
        return explode('/', $request);
    }

}