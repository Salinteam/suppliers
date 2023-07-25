<?php

namespace App\core;

trait ResultHandler
{

    public function throwError(ErrorCode|string $error_code, array $args = []): array
    {
        $result = [
            "error" => true,
            "error_code" => $error_code
        ];
        if (!empty($args)) {
            foreach ($args as $key => $arg) {
                $result[$key] = $arg;
            }
        }
        return $result;
    }

    public function success(array $args = []): array
    {
        $result = [
            "error" => false
        ];
        if (!empty($args)) {
            foreach ($args as $key => $arg) {
                $result[$key] = $arg;
            }
        }
        return $result;
    }

}