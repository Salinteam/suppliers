<?php

namespace App\utils\validation;

use App\core\ErrorCode;
use App\core\ResultHandler;

class InputRequire implements InputValidator
{
    use ResultHandler;

    private array $require_params;

    public function __construct(string ...$require_params)
    {
        foreach ($require_params as $require_param) {
            $this->require_params[] = $require_param;
        }
    }

    public function validate(array $request_params): array
    {
        foreach ($this->require_params as $require_param) {
            if (!array_key_exists($require_param, $request_params)) {
                return $this->throwError(ErrorCode::INPUT_REQUIRE, ["error_input" => $require_param]);
            }
        }
        return $this->success();
    }

}