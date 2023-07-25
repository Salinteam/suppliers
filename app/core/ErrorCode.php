<?php

namespace App\core;

enum ErrorCode
{
    const INPUT_REQUIRE = "input_require";
    const INPUT_TYPE = "input_type";
    const INPUT_VALUE = "input_value";
    const USER_EMAIL_EXIST = "user_email_exist";
    const USER_LOGIN_EXIST = "user_login_exist";
    const USER_LOGIN_NOT_EXIST = "user_login_not_exist";
    const EMAIL_NOT_VALID = "email_not_valid";
    const MOBILE_NUMBER_NOT_VALID = "mobile_number_not_valid";
    const INPUT_VALUE_IS_EMPTY = "input_value_is_empty";
    const SERVER_ERROR = "server_error";
    const TOKEN_OTP_IS_EXPIRE = "token_otp_is_expire";
    const TOKEN_OTP_NOT_VALID = "token_otp_not_valid";
    const PERMISSION_ERROR = "permission_error";
    const SEND_TOKEN_OTP_IS_LIMITED = "send_token_otp_is_limited";
    const MOBILE_IS_ALREADY_CONFIRMED = "mobile_is_already_confirmed";
    const MOBILE_IS_NOT_CONFIRMED = "mobile_is_not_confirmed";
    const NOT_FOUND = "not_found";
    const FILE_IS_CORRUPTED = "file_is_corrupted";
    const FILE_MIME_NOT_VALID = "file_mime_not_valid";
    const FILE_SIZE_NOT_VALID = "file_size_not_valid";
    const FILE_COUNT_NOT_VALID = "file_count_not_valid";
    const MEDIA_ID_NOT_EXIST = "media_id_not_exist";
    const FORM_ID_NOT_EXIST = "form_id_not_exist";
    const META_VALUE_NOT_EXIST = "meta_value_not_exist";
    const INPUT_ALREADY_UPDATED = "input_already_updated";
    const ARRAY_MUST_NOT_EMPTY = "array_must_not_empty";
    const ARRAY_MUST_TYPE_INTEGER = "array_must_type_integer";
    const ARRAY_MUST_TYPE_STRING = "array_must_type_string";
    const USER_ID_NOT_EXIST = "user_id_not_exist";
}
