<?php

namespace App\api\service;

use App\api\repository\UserMetaRepository;
use App\api\repository\UsersRepository;
use App\core\ErrorCode;
use App\core\ResultHandler;
use App\utils\users\UserMeta;
use Exception;
use Firebase\JWT\JWT;

class AuthService
{
    use ResultHandler;

    private UsersRepository $usersRepository;
    private UserMetaRepository $userMetaRepository;

    public function __construct()
    {
        $this->usersRepository = new UsersRepository();
        $this->userMetaRepository = new UserMetaRepository();
    }

    private function generateJWTToken(int $user_id, array $capabilities): array
    {
        try {
            $jwt_token = JWT::encode([
                'iat' => time(),
                'iss' => 'https://aftabor.com',
                'exp' => time() + 60 * 60,
                'user_id' => $user_id,
                'role' => $capabilities
            ], JWT_SALT, 'HS256');
            return $this->success(["jwt_token" => $jwt_token]);
        } catch (Exception $e) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }
    }

    public function generateMobileToken(string $user_login): array
    {

        /**
         * generate mobile token.
         */
        $mobile_token = strval(rand(100000, 999999));

        /**
         * generate mobile token expiration.
         */
        $mobile_token_exp = strtotime('+2 minutes', time());

        /**
         * check user login exist.
         */
        $user = $this->usersRepository->fetchByConditional([], ['user_login' => $user_login]);
        if (is_null($user)) {
            return $this->throwError(ErrorCode::USER_LOGIN_NOT_EXIST);
        }

        /**
         * check mobile token expiration.
         */
        if (!is_null($user->mobile_token_exp) && time() < strtotime($user->mobile_token_exp)) {
            return $this->throwError(ErrorCode::SEND_TOKEN_OTP_IS_LIMITED);
        }

        /**
         * generate mobile token.
         */
        $generate_mobile_token = $this->usersRepository->update(['user_login' => $user_login], [
            'mobile_token_attempts' => 3,
            'mobile_token' => $mobile_token,
            'mobile_token_exp' => date('Y-m-d H:i:s', $mobile_token_exp)
        ]);
        if (!$generate_mobile_token) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }

        return $this->success();
    }

    public function verifyMobile(string $user_login, string $token): array
    {
        /**
         * check user exist.
         */
        $user = $this->usersRepository->fetchByConditional([], ["user_login" => $user_login]);
        if (is_null($user)) {
            return $this->throwError(ErrorCode::USER_LOGIN_NOT_EXIST);
        }

        /**
         * check mobile verify.
         */
        if ($user->mobile_verify) {
            return $this->throwError(ErrorCode::MOBILE_IS_ALREADY_CONFIRMED);
        }

        /**
         * check attempts and expiration token.
         */
        if (intval($user->mobile_token_attempts) <= 0 || time() > strtotime($user->mobile_token_exp)) {
            return $this->throwError(ErrorCode::TOKEN_OTP_IS_EXPIRE);
        }

        /**
         * calculate attempts.
         */
        $calculate_attempts = $this->usersRepository->update(
            ['user_login' => $user_login],
            ['mobile_token_attempts' => intval($user->mobile_token_attempts) - 1]
        );
        if (!$calculate_attempts) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }

        /**
         * validation mobile token.
         */
        if ($token !== $user->mobile_token) {
            return $this->throwError(ErrorCode::TOKEN_OTP_NOT_VALID);
        }

        /**
         * mobile verify user login.
         */
        $mobile_verify = $this->usersRepository->update(["user_id" => $user->user_id], ["mobile_verify" => 1]);
        if (!$mobile_verify) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }

        /**
         * generate jwt token.
         */
        $capabilities = $this->userMetaRepository->fetchByConditional(["meta_value"], ["user_id" => $user->user_id, "meta_key" => UserMeta::Capabilities]);
        $generate_token = $this->generateJWTToken($user->user_id, unserialize($capabilities->meta_value));
        if ($generate_token["error"]) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }

        return $this->success($generate_token);
    }

    public function signInWithToken(string $user_login, string $token): array
    {

        /**
         * check user login exist.
         */
        $user = $this->usersRepository->fetchByConditional([], ["user_login" => $user_login]);
        if (is_null($user)) {
            return $this->throwError(ErrorCode::USER_LOGIN_NOT_EXIST);
        }

        /**
         * check mobile verify.
         */
        if (!$user->mobile_verify) {
            return $this->throwError(ErrorCode::MOBILE_IS_NOT_CONFIRMED);
        }

        /**
         * check attempts and expiration token.
         */
        if (intval($user->mobile_token_attempts) <= 0 || time() > strtotime($user->mobile_token_exp)) {
            return $this->throwError(ErrorCode::TOKEN_OTP_IS_EXPIRE);
        }

        /**
         * calculate attempts.
         */
        $calculate_attempts = $this->usersRepository->update(
            ['user_login' => $user_login],
            ['mobile_token_attempts' => intval($user->mobile_token_attempts) - 1]
        );
        if (!$calculate_attempts) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }

        /**
         * validation mobile token.
         */
        if ($token !== $user->mobile_token) {
            return $this->throwError(ErrorCode::TOKEN_OTP_NOT_VALID);
        }

        /**
         * generate jwt token.
         */
        $capabilities = $this->userMetaRepository->fetchByConditional(["meta_value"], ["user_id" => $user->user_id, "meta_key" => UserMeta::Capabilities]);
        $generate_token = $this->generateJWTToken($user->user_id, unserialize($capabilities->meta_value));
        if ($generate_token["error"]) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }

        return $this->success($generate_token);

    }

}