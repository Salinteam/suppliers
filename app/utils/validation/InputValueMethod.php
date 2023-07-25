<?php

namespace App\utils\validation;

enum InputValueMethod
{
    const INCLUDE = "include";
    const REGEX = "regex";
    const OPERATION = "operation";
    const FILE_MIME = "file_mime";
    const FILE_SIZE = "file_size";
    const FILE_COUNT = "file_count";
}
