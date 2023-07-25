<?php

namespace App\api\controller\v1;

use App\api\service\AuthService;
use App\api\service\UserService;
use App\core\ApiController;
use App\core\ErrorCode;
use App\core\ResultHandler;
use App\utils\users\UserRoles;
use App\utils\validation\InputRequire;
use App\utils\validation\InputType;
use App\utils\validation\InputTypeDataClass;
use App\utils\validation\InputValue;
use App\utils\validation\InputValueDataClass;
use App\utils\validation\InputValueMethod;
use App\utils\validation\InputValueRegex;

class AuthController extends ApiController
{
    use ResultHandler;

    private AuthService $authService;
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->userService = new UserService();
    }

    public function index()
    {
        header("Access-Control-Allow-Methods: POST");

        $args = func_get_arg(0);

        switch ($this->requestMethod) {
            case "POST" :
                switch ($args) {
                    case !empty($args["query"]) && count($args["query"]) === 1 && isset($args["query"]["method"]) && $args["query"]["method"] === "sign_up" :
                        $this->signUp();
                        break;
                    case !empty($args["query"]) && count($args["query"]) === 1 && isset($args["query"]["method"]) && $args["query"]["method"] === "verify_mobile" :
                        $this->verifyMobile();
                        break;
                    case !empty($args["query"]) && count($args["query"]) === 1 && isset($args["query"]["method"]) && $args["query"]["method"] === "send_mobile_token" :
                        $this->sendMobileToken();
                        break;
                    case !empty($args["query"]) && count($args["query"]) === 1 && isset($args["query"]["method"]) && $args["query"]["method"] === "sign_in_with_token" :
                        $this->signInWithToken();
                        break;
                    default:
                        $this->returnResponse(405);
                }
                break;
            default:
                $this->returnResponse(405);
        }
    }

    private function signUp()
    {
        /**
         * validate request.
         **/
        $this->validateRequestBody();

        /**
         * validate params.
         **/
        $this->validateParams(
            new InputRequire("user_nicename", "user_email", "user_login"),
            new InputType(
                new InputTypeDataClass("user_nicename", "string"),
                new InputTypeDataClass("user_email", "string"),
                new InputTypeDataClass("user_login", "string")
            ),
            new InputValue(
                new InputValueDataClass(
                    "user_nicename",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "user_email",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY, InputValueRegex::SANITIZE_EMAIL
                ),
                new InputValueDataClass(
                    "user_login",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY, InputValueRegex::SANITIZE_MOBILE_NUMBER
                ),
            )
        );

        /**
         * create new user but not active.
         */
        $create_user = $this->userService->insertUser(
            $this->requestParams['user_login'],
            $this->requestParams['user_email'],
            $this->requestParams['user_nicename'],
            UserRoles::CUSTOMER
        );
        if ($create_user["error"]) {
            switch ($create_user["error_code"]) {
                case ErrorCode::SERVER_ERROR :
                    $this->returnResponse(500, [], $create_user);
                    break;
                case ErrorCode::USER_EMAIL_EXIST || ErrorCode::USER_LOGIN_EXIST :
                    $this->returnResponse(406, [], $create_user);
                    break;
            }
        }

        $this->returnResponse(200, [], $create_user);
    }

    private function signInWithToken()
    {
        /**
         * validate request.
         **/
        $this->validateRequestBody();

        /**
         * validate params.
         **/
        $this->validateParams(
            new InputRequire("user_login", "token"),
            new InputType(
                new InputTypeDataClass("user_login", "string"),
                new InputTypeDataClass("token", "string")
            ),
            new InputValue(
                new InputValueDataClass(
                    "user_login",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY, InputValueRegex::SANITIZE_MOBILE_NUMBER
                ),
                new InputValueDataClass(
                    "token",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
            )
        );

        /**
         * check user exist.
         **/
        $sign_in_with_token = $this->authService->signInWithToken(
            $this->requestParams['user_login'],
            $this->requestParams['token']
        );
        if ($sign_in_with_token["error"]) {
            switch ($sign_in_with_token["error_code"]) {
                case ErrorCode::SERVER_ERROR :
                    $this->returnResponse(500, [], $sign_in_with_token);
                    break;
                case ErrorCode::USER_LOGIN_NOT_EXIST || ErrorCode::MOBILE_IS_NOT_CONFIRMED || ErrorCode::TOKEN_OTP_IS_EXPIRE || ErrorCode::TOKEN_OTP_NOT_VALID:
                    $this->returnResponse(406, [], $sign_in_with_token);
                    break;
            }
        }

        $this->returnResponse(200, [], $sign_in_with_token);
    }

    private function sendMobileToken()
    {
        /**
         * validate request.
         **/
        $this->validateRequestBody();

        /**
         * validate params.
         **/
        $this->validateParams(
            new InputRequire("user_login"),
            new InputType(
                new InputTypeDataClass("user_login", "string")
            ),
            new InputValue(
                new InputValueDataClass(
                    "user_login",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY, InputValueRegex::SANITIZE_MOBILE_NUMBER
                )
            )
        );

        /**
         * generate token.
         */
        $generate_token = $this->authService->generateMobileToken($this->requestParams['user_login']);
        if ($generate_token["error"]) {
            switch ($generate_token["error_code"]) {
                case ErrorCode::USER_LOGIN_NOT_EXIST || ErrorCode::SEND_TOKEN_OTP_IS_LIMITED:
                    $this->returnResponse(406, [], $generate_token);
                    break;
                case ErrorCode::SERVER_ERROR :
                    $this->returnResponse(500, [], $generate_token);
                    break;
            }
        }

        $this->returnResponse(200, [], $generate_token);
    }

    private function verifyMobile()
    {

        /**
         * validate request.
         **/
        $this->validateRequestBody();

        /**
         * validate params.
         **/
        $this->validateParams(
            new InputRequire("user_login", "token"),
            new InputType(
                new InputTypeDataClass("token", "string"),
                new InputTypeDataClass("user_login", "string")
            ),
            new InputValue(
                new InputValueDataClass(
                    "user_login",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY, InputValueRegex::SANITIZE_MOBILE_NUMBER
                ),
                new InputValueDataClass(
                    "token",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                )
            )
        );

        /**
         * verification mobile otp.
         */
        $verify_mobile = $this->authService->verifyMobile($this->requestParams['user_login'], $this->requestParams['token']);
        if ($verify_mobile["error"]) {
            switch ($verify_mobile["error_code"]) {
                case ErrorCode::SERVER_ERROR :
                    $this->returnResponse(500, [], $verify_mobile);
                    break;
                case ErrorCode::USER_LOGIN_NOT_EXIST || ErrorCode::TOKEN_OTP_IS_EXPIRE || ErrorCode::TOKEN_OTP_NOT_VALID || ErrorCode::MOBILE_IS_ALREADY_CONFIRMED:
                    $this->returnResponse(406, [], $verify_mobile);
                    break;
            }
        }

        $this->returnResponse(200, [], $verify_mobile);

    }

}