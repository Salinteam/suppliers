<?php

namespace App\utils\validation;

interface InputValidator
{
    public function validate(array $request_params): array;
}