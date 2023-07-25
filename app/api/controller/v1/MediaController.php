<?php

namespace App\api\controller\v1;

use App\api\service\MediaService;
use App\api\service\UserService;
use App\core\ApiController;
use App\core\ErrorCode;
use App\core\ResultHandler;
use App\utils\conversion\Conversion;
use App\utils\media\MediaMeta;
use App\utils\users\UserRoles;
use App\utils\validation\FileMime;
use App\utils\validation\InputRequire;
use App\utils\validation\InputType;
use App\utils\validation\InputTypeDataClass;
use App\utils\validation\InputValue;
use App\utils\validation\InputValueDataClass;
use App\utils\validation\InputValueMethod;
use App\utils\validation\InputValueRegex;

class MediaController extends ApiController
{

    use ResultHandler;

    private MediaService $mediaService;
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->mediaService = new MediaService();
        $this->userService = new UserService();
    }

    public function index()
    {

        header("Access-Control-Allow-Methods: GET ,POST, DELETE");

        /**
         * check Authorization.
         */
        $this->checkAuthorization();

        /**
         * check user permission.
         */
        $this->checkPermissionUser(UserRoles::ADMINISTRATOR, UserRoles::CUSTOMER);

        $args = func_get_arg(0);

        switch ($this->requestMethod) {
            case "GET" :
                switch ($args) {
                    case empty($args["path"]) && empty($args["query"]):
                        $this->getAllMedia();
                        break;
                    default:
                        $this->returnResponse(400);
                }
                break;
            case "POST" :
                switch ($args) {
                    case empty($args["path"]) && !empty($args["query"]) && count($args["query"]) === 1 && isset($args["query"]["action"]) && $args["query"]["action"] === "set_media_meta" :
                        $this->setMediaMeta();
                        break;
                    case empty($args["path"]) && empty($args["query"]):
                        $this->uploadMedia();
                        break;
                    default :
                        $this->returnResponse(400);
                        break;
                }
                break;
            default:
                $this->returnResponse(405);
        }

    }

    private function getAllMedia()
    {

        /**
         * get all form for administrator.
         */
        if (in_array(UserRoles::ADMINISTRATOR, $this->currentUserCapabilities)) {
            $get_all_media = $this->mediaService->getAllMedia();
            if ($get_all_media["error"]) {
                $this->returnResponse(404, [], $get_all_media);
            }
            $this->returnResponse(200, [], $get_all_media);
        }

        /**
         * get all form for customer.
         */
        if (in_array(UserRoles::CUSTOMER, $this->currentUserCapabilities)) {
            $get_all_media = $this->mediaService->getAllMedia($this->currentUserId);
            if ($get_all_media["error"]) {
                $this->returnResponse(404, [], $get_all_media);
            }
            $this->returnResponse(200, [], $get_all_media);
        }

    }

    private function uploadMedia()
    {

        /**
         * validate request.
         **/
        $this->validateRequestFormData();

        /**
         * validate params.
         **/
        $this->validateParams(
            new InputRequire("media"),
            new InputType(
                new InputTypeDataClass("media", "array")
            ),
            new InputValue(
                new InputValueDataClass(
                    "media",
                    InputValueMethod::FILE_SIZE,
                    Conversion::megabytesToBytes(1)
                ),
                new InputValueDataClass(
                    "media",
                    InputValueMethod::FILE_MIME,
                    FileMime::JPEG, FileMime::JPG, FileMime::PNG
                )
            )
        );

        /**
         * upload media.
         */
        $upload_media = $this->mediaService->uploadMedia($this->currentUserId, $this->requestParams['media'], strval($this->currentUserId));
        if ($upload_media["error"]) {
            $this->returnResponse(500, [], $upload_media);
        }

        $this->returnResponse(200, [], $upload_media);

    }

    private function setMediaMeta()
    {

        /**
         * validate request.
         **/
        $this->validateRequestBody();

        /**
         * validate params.
         **/
        $this->validateParams(
            new InputRequire("media_id", "meta_key", "meta_value"),
            new InputType(
                new InputTypeDataClass("media_id", "integer"),
                new InputTypeDataClass("meta_key", "string"),
                new InputTypeDataClass("meta_value", "string", "array")
            ),
            new InputValue(
                new InputValueDataClass(
                    "media_id",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "meta_key",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "meta_key",
                    InputValueMethod::INCLUDE,
                    MediaMeta::MEDIA_CAPTION
                ),
                new InputValueDataClass(
                    "meta_value",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
            )
        );

        /**
         * set media meta.
         */
        $set_media_meta = $this->mediaService->setMediaMeta(
            $this->requestParams['media_id'],
            $this->requestParams['meta_key'],
            $this->requestParams['meta_value']
        );
        if ($set_media_meta["error"]) {
            switch ($set_media_meta["error_code"]) {
                case ErrorCode::SERVER_ERROR :
                    $this->returnResponse(500, [], $set_media_meta);
                    break;
                case ErrorCode::MEDIA_ID_NOT_EXIST || ErrorCode::INPUT_ALREADY_UPDATED:
                    $this->returnResponse(400, [], $set_media_meta);
                    break;
            }
        }

        $this->returnResponse(200, [], $set_media_meta);

    }

}