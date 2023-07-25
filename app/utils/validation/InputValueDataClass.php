<?php

namespace App\utils\validation;

class InputValueDataClass
{

    private string $name_param;
    private InputValueMethod|string $validation_method;
    private array $valid_values;

    public function __construct(string $name_param, InputValueMethod|string $method, ...$valid_values)
    {
        $this->name_param = $name_param;
        $this->validation_method = $method;
        foreach ($valid_values as $value) {
            $this->valid_values[] = $value;
        }
    }

    public function getNameParam(): string
    {
        return $this->name_param;
    }

    public function getValidationMethod(): InputValueMethod|string
    {
        return $this->validation_method;
    }

    public function getValidValues(): array
    {
        return $this->valid_values;
    }

}