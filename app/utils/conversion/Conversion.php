<?php

namespace App\utils\conversion;

class Conversion
{
    public static function bytesToMegabytes(int $bytes): float|int
    {
        return $bytes / 1024 / 1024;
    }

    public static function megabytesToBytes(int $megabytes): float|int
    {
        return $megabytes * 1024 * 1024;
    }
}