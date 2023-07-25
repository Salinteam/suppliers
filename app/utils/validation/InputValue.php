<?php

namespace App\utils\validation;

use App\core\ErrorCode;
use App\core\ResultHandler;
use App\utils\conversion\Conversion;

class InputValue implements InputValidator
{
    use ResultHandler;

    private array $valid_inputs_values;

    public function __construct(InputValueDataClass...$input_value_dataclass)
    {
        foreach ($input_value_dataclass as $index => $param) {
            $this->valid_inputs_values[$param->getNameParam()][$index]["method"] = $param->getValidationMethod();
            $this->valid_inputs_values[$param->getNameParam()][$index]["values"] = $param->getValidValues();
            $this->valid_inputs_values[$param->getNameParam()] = array_values($this->valid_inputs_values[$param->getNameParam()]);
        }
    }

    private function sanitizeEmailRegex(string $email): bool
    {
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        if (preg_match($pattern, $email)) {
            return true;
        }
        return false;
    }

    private function sanitizeMobileNumberRegex(string $mobile_number): bool
    {
        $pattern = '/^09\d{9}$/';
        if (preg_match($pattern, $mobile_number)) {
            return true;
        }
        return false;
    }

    private function sanitizeEmptyRegex(string $input_value): bool
    {
        $pattern = '/\S/';
        if (preg_match($pattern, $input_value)) {
            return true;
        }
        return false;
    }

    private function sanitizeEmptyArray(array $input_value): bool
    {
        if (empty($input_value)) {
            return false;
        }
        return true;
    }

    private function sanitizeIntegerArray(array $input_value): bool
    {
        foreach ($input_value as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }
        return true;
    }

    private function sanitizeStringArray(array $input_value): bool
    {
        foreach ($input_value as $value) {
            if (!is_string($value)) {
                return false;
            }
        }
        return true;
    }

    private function sanitizeFileHealthy(array $file): bool
    {
        if (is_numeric($file["error"])) {
            if ($file["error"]) {
                return false;
            }
        } else {
            foreach ($file["error"] as $error) {
                if ($error) {
                    return false;
                }
            }
        }
        return true;
    }

    private function sanitizeFileMime(array $file, array $valid_file_mimes): bool
    {
        if (is_string($file["type"])) {
            if (!in_array($file["type"], $valid_file_mimes)) {
                return false;
            }
        } else {
            foreach ($file["type"] as $type) {
                if (!in_array($type, $valid_file_mimes)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function sanitizeFileSize(array $file, int $valid_size): bool
    {
        if (is_numeric($file["size"])) {
            if ($file["size"] > $valid_size) {
                return false;
            }
        } else {
            foreach ($file["size"] as $size) {
                if ($size > $valid_size) {
                    return false;
                }
            }
        }
        return true;
    }

    private function sanitizeFileCount(array $file, int $valid_count): bool
    {
        if (is_array($file["name"]) && count($file["name"]) > $valid_count) {
            return false;
        }
        return true;
    }

    public function validate(array $request_params): array
    {
        foreach ($this->valid_inputs_values as $_name_param => $properties) {
            foreach ($request_params as $name_param => $value_param) {
                if ($_name_param === $name_param) {
                    foreach ($properties as $property) {
                        if ($property["method"] === InputValueMethod::INCLUDE && !in_array($value_param, $property["values"])) {
                            return $this->throwError(ErrorCode::INPUT_VALUE, [
                                "error_input" => $name_param,
                                "valid_input_values" => implode(",", $property["values"])
                            ]);
                        }
                        if ($property["method"] === InputValueMethod::REGEX) {
                            foreach ($property["values"] as $regex) {
                                switch ($regex) {
                                    case InputValueRegex::SANITIZE_EMAIL :
                                        $result = $this->sanitizeEmailRegex($value_param);
                                        if (!$result) {
                                            return $this->throwError(ErrorCode::EMAIL_NOT_VALID, [
                                                "error_input" => $name_param
                                            ]);
                                        }
                                        break;
                                    case InputValueRegex::SANITIZE_MOBILE_NUMBER :
                                        $result = $this->sanitizeMobileNumberRegex($value_param);
                                        if (!$result) {
                                            return $this->throwError(ErrorCode::MOBILE_NUMBER_NOT_VALID, [
                                                "error_input" => $name_param
                                            ]);
                                        }
                                        break;
                                    case InputValueRegex::SANITIZE_EMPTY:
                                        $result = $this->sanitizeEmptyRegex($value_param);
                                        if (!$result) {
                                            return $this->throwError(ErrorCode::INPUT_VALUE_IS_EMPTY, [
                                                "error_input" => $name_param
                                            ]);
                                        }
                                        break;
                                }
                            }
                        }
                        if ($property["method"] === InputValueMethod::OPERATION) {
                            foreach ($property["values"] as $operation) {
                                switch ($operation) {
                                    case InputValueOperation::SANITIZE_EMPTY_ARRAY :
                                        $result = $this->sanitizeEmptyArray($value_param);
                                        if (!$result) {
                                            return $this->throwError(ErrorCode::ARRAY_MUST_NOT_EMPTY, [
                                                "error_input" => $name_param
                                            ]);
                                        }
                                        break;
                                    case InputValueOperation::SANITIZE_INTEGER_ARRAY :
                                        $result = $this->sanitizeIntegerArray($value_param);
                                        if (!$result) {
                                            return $this->throwError(ErrorCode::ARRAY_MUST_TYPE_INTEGER, [
                                                "error_input" => $name_param
                                            ]);
                                        }
                                        break;
                                    case InputValueOperation::SANITIZE_STRING_ARRAY:
                                        $result = $this->sanitizeStringArray($value_param);
                                        if (!$result) {
                                            return $this->throwError(ErrorCode::ARRAY_MUST_TYPE_STRING, [
                                                "error_input" => $name_param
                                            ]);
                                        }
                                        break;
                                }
                            }
                        }
                        if ($property["method"] === InputValueMethod::FILE_MIME) {
                            if (!$this->sanitizeFileHealthy($value_param)) {
                                return $this->throwError(ErrorCode::FILE_IS_CORRUPTED, [
                                    "error_input" => $name_param
                                ]);
                            }
                            if (!$this->sanitizeFileMime($value_param, $property["values"])) {
                                return $this->throwError(ErrorCode::FILE_MIME_NOT_VALID, [
                                    "error_input" => $name_param,
                                    "valid_file_mimes" => implode(",", $property["values"])
                                ]);
                            }
                        }
                        if ($property["method"] === InputValueMethod::FILE_SIZE) {
                            if (!$this->sanitizeFileHealthy($value_param)) {
                                return $this->throwError(ErrorCode::FILE_IS_CORRUPTED, [
                                    "error_input" => $name_param
                                ]);
                            }
                            if (!$this->sanitizeFileSize($value_param, $property["values"][0])) {
                                return $this->throwError(ErrorCode::FILE_SIZE_NOT_VALID, [
                                    "error_input" => $name_param,
                                    "valid_file_size" => Conversion::bytesToMegabytes($property["values"][0]) . "Mb"
                                ]);
                            }
                        }
                        if ($property["method"] === InputValueMethod::FILE_COUNT) {
                            if (!$this->sanitizeFileHealthy($value_param)) {
                                return $this->throwError(ErrorCode::FILE_IS_CORRUPTED, [
                                    "error_input" => $name_param
                                ]);
                            }
                            if (!$this->sanitizeFileCount($value_param, $property["values"][0])) {
                                return $this->throwError(ErrorCode::FILE_COUNT_NOT_VALID, [
                                    "error_input" => $name_param,
                                    "valid_file_count" => $property["values"][0]
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return $this->success();
    }

}
