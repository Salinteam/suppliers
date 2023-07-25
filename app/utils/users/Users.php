<?php

namespace App\utils\users;

class Users
{

    public static function hashedPassword($password): string
    {
        return md5(sha1($password . PASSWORD_SALT));
    }

}