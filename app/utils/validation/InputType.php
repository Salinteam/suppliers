<?php

namespace App\utils\validation;

use App\core\ErrorCode;
use App\core\ResultHandler;

class InputType implements InputValidator
{
    use ResultHandler;

    private array $valid_inputs_types;

    public function __construct(InputTypeDataClass...$input_type_dataclass)
    {
        foreach ($input_type_dataclass as $param) {
            $this->valid_inputs_types[$param->getNameParam()] = $param->getValidTypes();
        }
    }

    public function validate($request_params): array
    {
        foreach ($this->valid_inputs_types as $_name_param => $valid_types) {

            foreach ($request_params as $name_param => $value_param) {

                if ($_name_param === $name_param && !in_array(gettype($value_param), $valid_types)) {
                    return $this->throwError(ErrorCode::INPUT_TYPE, ["error_input" => $name_param, "valid_input_types" => implode(",", $valid_types)]
                    );
                }

            }

        }

        return $this->success();
    }

}