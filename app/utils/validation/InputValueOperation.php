<?php

namespace App\utils\validation;

enum InputValueOperation
{
    const SANITIZE_EMPTY_ARRAY = "sanitize_empty_array";
    const SANITIZE_INTEGER_ARRAY = "sanitize_integer_array";
    const SANITIZE_STRING_ARRAY = "sanitize_string_array";
}
