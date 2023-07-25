<?php

namespace App\utils\validation;

enum InputValueRegex
{
    const SANITIZE_EMPTY = "sanitize_empty";
    const SANITIZE_EMAIL = "sanitize_email";
    const SANITIZE_MOBILE_NUMBER = "sanitize_mobile_number";
}
