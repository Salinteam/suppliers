<?php

namespace App\utils\validation;

class InputTypeDataClass
{

    private string $name_param;
    private array $valid_types;

    public function __construct(string $name_param, string ...$valid_type)
    {
        $this->name_param = $name_param;
        foreach ($valid_type as $type) {
            $this->valid_types[] = $type;
        }
    }

    public function getNameParam(): string
    {
        return $this->name_param;
    }

    public function getValidTypes(): array
    {
        return $this->valid_types;
    }

}