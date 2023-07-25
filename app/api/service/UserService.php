<?php

namespace App\api\service;

use App\api\repository\UserMetaRepository;
use App\api\repository\UsersRepository;
use App\core\ErrorCode;
use App\core\ResultHandler;
use App\utils\users\UserCapabilities;
use App\utils\users\UserMeta;
use App\utils\users\UserRoles;
use App\utils\users\Users;

class UserService
{
    use ResultHandler;

    private UsersRepository $usersRepository;
    private UserMetaRepository $userMetaRepository;

    public function __construct()
    {
        $this->usersRepository = new UsersRepository();
        $this->userMetaRepository = new UserMetaRepository();
    }

    public function checkUserExist(string $field_user, string|int $value): bool
    {
        $user_exist = $this->usersRepository->fetchByConditional(["user_id"], [$field_user => $value]);
        if (is_null($user_exist)) {
            return false;
        }
        return true;
    }

    public function insertUser(string $user_login, string $user_email, string $user_nicename, UserRoles|string $user_role, string|UserCapabilities ...$user_capabilities): array
    {
        /**
         * check user login exist.
         */
        $user_login_exist = $this->checkUserExist("user_login", $user_login);
        if ($user_login_exist) {
            return $this->throwError(ErrorCode::USER_LOGIN_EXIST);
        }

        /**
         * check user email exist.
         */
        $user_email_exist = $this->checkUserExist("user_email", $user_email);
        if ($user_email_exist) {
            return $this->throwError(ErrorCode::USER_EMAIL_EXIST);
        }

        /**
         * user created but not active.
         */
        $user_id = $this->usersRepository->insertGetId([
            "user_login" => $user_login,
            "user_nicename" => $user_nicename,
            "user_email" => $user_email,
            "user_pass" => Users::hashedPassword(rand(100000, 999999))
        ]);
        if (!$user_id) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }

        /**
         * set user role and capability.
         */
        $_user_capabilities[$user_role] = [];
        foreach ($user_capabilities as $capability) {
            $_user_capabilities[$user_role][] = $capability;
        }
        $insert_user_meta = $this->userMetaRepository->insertGetId([
            "user_id" => $user_id,
            "meta_key" => UserMeta::Capabilities,
            "meta_value" => serialize($_user_capabilities)
        ]);
        if (!$insert_user_meta) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }

        return $this->success(["user_id" => $user_id]);
    }

    public function setUserMeta(int $user_id, UserMeta|string $meta_key, string|array $meta_value): array
    {

        /**
         * check exist user_id.
         */
        $exist_user_id = $this->checkUserExist("user_id", $user_id);
        if (!$exist_user_id) {
            return $this->throwError(ErrorCode::USER_ID_NOT_EXIST);
        }

        /**
         * check exist meta_key for user_id.
         */
        $exist_meta_key = $this->userMetaRepository->fetchByConditional(["user_id"], ["user_id" => $user_id, "meta_key" => $meta_key]);
        if (is_null($exist_meta_key)) {
            $insert_user_meta = $this->userMetaRepository->insertGetId([
                "user_id" => $user_id,
                "meta_key" => $meta_key,
                "meta_value" => is_string($meta_value) ? $meta_value : serialize($meta_value)
            ]);
            if (!$insert_user_meta) {
                return $this->throwError(ErrorCode::SERVER_ERROR);
            }
        } else {
            $exist_meta_value = $this->userMetaRepository->fetchByConditional(["umeta_id"], ["user_id" => $user_id, "meta_key" => $meta_key, "meta_value" => $meta_value]);
            if (!is_null($exist_meta_value)) {
                return $this->throwError(ErrorCode::INPUT_ALREADY_UPDATED);
            }
            $this->userMetaRepository->update(
                [
                    "user_id" => $user_id,
                    "meta_key" => $meta_key
                ],
                [
                    "meta_value" => is_string($meta_value) ? $meta_value : serialize($meta_value)
                ]);
        }

        return $this->success();
    }

    public function getUserMeta(int $user_id, UserMeta|string $meta_key): array
    {
        $get_user_meta = $this->userMetaRepository->fetchByConditional(["meta_value"], ["user_id" => $user_id, "meta_key" => $meta_key]);
        if (is_null($get_user_meta)) {
            return $this->throwError(ErrorCode::META_VALUE_NOT_EXIST);
        }
        if (!unserialize($get_user_meta->meta_value)) {
            $form_meta = $get_user_meta->meta_value;
        } else {
            $form_meta = unserialize($get_user_meta->meta_value);
        }
        return $this->success(["meta_value" => $form_meta]);
    }

    public function userCan(int $user_id, UserRoles|UserCapabilities|string ...$capabilities): array
    {
        /**
         * capabilities param.
         */
        $capabilities_param = [];
        foreach ($capabilities as $capability) {
            $capabilities_param[] = $capability;
        }

        /**
         * get user capabilities.
         */
        $user_capabilities = $this->getUserMeta($user_id, UserMeta::Capabilities);
        if ($user_capabilities["error"]) {
            return $this->throwError(ErrorCode::PERMISSION_ERROR);
        }

        /**
         * check user can.
         */
        foreach ($user_capabilities["meta_value"] as $_role => $_capabilities) {
            if (!in_array($_role, $capabilities_param)) {
                return $this->throwError(ErrorCode::PERMISSION_ERROR);
            }
            foreach ($_capabilities as $_capability) {
                if (!in_array($_capability, $capabilities_param)) {
                    return $this->throwError(ErrorCode::PERMISSION_ERROR);
                }
            }
        }

        return $this->success();
    }

}