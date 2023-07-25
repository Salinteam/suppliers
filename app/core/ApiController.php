<?php

namespace App\core;

use App\utils\users\UserRoles;
use App\utils\validation\InputValidator;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

defined('ABSPATH') || die();

class ApiController
{

    use ResultHandler;

    public string $requestMethod;
    public array $request;
    public array $requestParams;
    public int $currentUserId = 0;
    public array $currentUserCapabilities = [];

    public function __construct()
    {
        // header("Access-Control-Allow-Origin: *");
        // header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Headers: Authorization,Content-Type");

        $this->requestMethod = $_SERVER["REQUEST_METHOD"];
        $this->request = (array)json_decode(file_get_contents('php://input'), true);
    }

    private function computeMaxContent(): int
    {
        $size = intval(ini_get('post_max_size'));
        return $size * 1000000;
    }

    private function getBearerJWTToken(): array
    {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $this->success(["bearer" => $matches[1]]);
            }
        }
        return $this->throwError(ErrorCode::PERMISSION_ERROR);
    }

    private function verifyJWTToken(string $bearer): array
    {
        try {
            $jwt_decode = (array)JWT::decode($bearer, new Key(JWT_SALT, 'HS256'));
            return $this->success(
                [
                    "user_id" => $jwt_decode["user_id"],
                    "role" => $jwt_decode["role"]
                ]);
        } catch (Exception $e) {
            return $this->throwError(ErrorCode::PERMISSION_ERROR);
        }
    }

    public function validateRequestBody(): void
    {
        if (!isset($_SERVER["CONTENT_TYPE"]) || $_SERVER["CONTENT_TYPE"] !== 'application/json') {
            $this->returnResponse(403, [], ["error_message" => 'The only acceptable content type is application/json.']);
        }
        if (!isset($this->request['request_params']) || !is_array($this->request['request_params'])) {
            $this->returnResponse(400, [], ["error_message" => 'The parameters of the request are not defined.']);
        } else {
            $this->requestParams = $this->request['request_params'];
        }
    }

    public function validateRequestFormData(): void
    {
        if (empty($_POST) && empty($_FILES)) {
            $this->returnResponse(400, [], ["error_message" => 'The parameters of the request are not defined.']);
        }
        $this->requestParams = array_merge($_POST, $_FILES);
    }

    public function checkAuthorization(): void
    {

        /**
         * validate bearer.
         */
        $validate_bearer = $this->getBearerJWTToken();
        if ($validate_bearer["error"]) {
            $this->returnResponse(401, [], $validate_bearer);
        }

        /**
         * verify JWT token.
         */
        $verify_jwt_token = $this->verifyJWTToken($validate_bearer["bearer"]);
        if ($verify_jwt_token["error"]) {
            $this->returnResponse(401, [], $verify_jwt_token);
        }

        /**
         * set user id & user capabilities.
         */
        $this->currentUserId = $verify_jwt_token["user_id"];
        foreach ($verify_jwt_token["role"] as $role => $capabilities) {
            $this->currentUserCapabilities[] = $role;
            foreach ($capabilities as $capability) {
                $this->currentUserCapabilities[] = $capability;
            }
        }

    }

    public function checkPermissionUser(UserRoles|string ...$capabilities): array
    {
        foreach ($this->currentUserCapabilities as $capability) {
            if (!in_array($capability, $capabilities)) {
                $this->returnResponse(401, [], $this->throwError(ErrorCode::PERMISSION_ERROR));
            }
        }
        return $this->success();
    }

    public function validateParams(InputValidator...$args): void
    {
        foreach ($args as $arg) {
            $result = $arg->validate($this->requestParams);
            if ($result["error"]) {
                $this->returnResponse(400, [], $result);
            }
        }
    }

    public function returnResponse(int $statusCode, array $result = [], array $handler = []): void
    {
        header('Content-Type: application/json');
        header("Status: $statusCode");
        http_response_code($statusCode);

        $response = [
            'code' => $statusCode
        ];

        if (!empty($handler)) {
            foreach ($handler as $k => $v) {
                $response[$k] = $v;
            }
        }

        if (!empty($result)) {
            $response["result"] = $result;
        }

        echo json_encode($response);

        die;
    }

}