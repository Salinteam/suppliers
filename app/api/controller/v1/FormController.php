<?php

namespace App\api\controller\v1;

use App\api\service\FormService;
use App\api\service\UserService;
use App\core\ApiController;
use App\core\ErrorCode;
use App\core\ResultHandler;
use App\utils\forms\FormMeta;
use App\utils\users\UserRoles;
use App\utils\validation\InputRequire;
use App\utils\validation\InputType;
use App\utils\validation\InputTypeDataClass;
use App\utils\validation\InputValue;
use App\utils\validation\InputValueDataClass;
use App\utils\validation\InputValueMethod;
use App\utils\validation\InputValueOperation;
use App\utils\validation\InputValueRegex;

class FormController extends ApiController
{
    use ResultHandler;

    private FormService $formService;
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->formService = new FormService();
        $this->userService = new UserService();
    }

    public function index()
    {

        header("Access-Control-Allow-Methods: POST");

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
                    case empty($args["query"]) && empty($args["path"]):
                        $this->getAllForm();
                        break;
                    default:
                        $this->returnResponse(405);
                }
                break;
            case "POST" :
                switch ($args) {
                    case empty($args["query"]) && empty($args["path"]):
                        $this->insertForm();
                        break;
                    default:
                        $this->returnResponse(405);
                }
                break;
            default:
                $this->returnResponse(405);
        }

    }

    private function insertForm()
    {
        /**
         * validate request.
         **/
        $this->validateRequestBody();

        /**
         * validate params.
         **/
        $this->validateParams(
            new InputRequire(
                "business_name",
                "business_agent",
                "agent_gender",
                "business_state",
                "business_city",
                "business_address",
                "business_postal_code",
                "business_tel",
                "business_type",
                "business_category",
                "business_property"
            ),
            new InputType(
                new InputTypeDataClass("business_name", "string"),
                new InputTypeDataClass("business_agent", "string"),
                new InputTypeDataClass("business_email", "string"),
                new InputTypeDataClass("agent_gender", "string"),
                new InputTypeDataClass("business_document", "array"),
                new InputTypeDataClass("business_state", "string"),
                new InputTypeDataClass("business_city", "string"),
                new InputTypeDataClass("business_address", "string"),
                new InputTypeDataClass("business_postal_code", "string"),
                new InputTypeDataClass("business_tel", "string"),
                new InputTypeDataClass("business_fax", "string"),
                new InputTypeDataClass("business_type", "string"),
                new InputTypeDataClass("business_category", "integer"),
                new InputTypeDataClass("business_property", "array"),
                new InputTypeDataClass("business_catalog", "array")
            ),
            new InputValue(
                new InputValueDataClass(
                    "business_name",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "business_agent",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "business_email",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY, InputValueRegex::SANITIZE_EMAIL
                ),
                new InputValueDataClass(
                    "agent_gender",
                    InputValueMethod::INCLUDE,
                    "female", "male"
                ),
                new InputValueDataClass(
                    "business_document",
                    InputValueMethod::OPERATION,
                    InputValueOperation::SANITIZE_EMPTY_ARRAY, InputValueOperation::SANITIZE_INTEGER_ARRAY
                ),
                new InputValueDataClass(
                    "business_state",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "business_city",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "business_address",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "business_postal_cade",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "business_tel",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "business_fax",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "business_type",
                    InputValueMethod::INCLUDE,
                    "service", "product"
                ),
                new InputValueDataClass(
                    "business_category",
                    InputValueMethod::REGEX,
                    InputValueRegex::SANITIZE_EMPTY
                ),
                new InputValueDataClass(
                    "business_property",
                    InputValueMethod::OPERATION,
                    InputValueOperation::SANITIZE_EMPTY_ARRAY, InputValueOperation::SANITIZE_STRING_ARRAY
                ),
                new InputValueDataClass(
                    "business_catalog",
                    InputValueMethod::OPERATION,
                    InputValueOperation::SANITIZE_EMPTY_ARRAY, InputValueOperation::SANITIZE_INTEGER_ARRAY
                )
            )
        );

        /**
         * insert form.
         */
        $insert_form = $this->formService->insertForm($this->currentUserId);
        if ($insert_form["error"]) {
            $this->returnResponse(500, [], $insert_form);
        }

        /**
         * insert form meta.
         */
        $insert_form_mata = $this->formService->setFormMeta(intval($insert_form["form_id"]), FormMeta::BUSINESS_DATA, [
            "business_name" => $this->requestParams['business_name'],
            "business_agent" => $this->requestParams['business_agent'],
            "business_email" => $this->requestParams['business_email'] ?? "",
            "agent_gender" => $this->requestParams['agent_gender'],
            "business_document" => $this->requestParams['business_document'] ?? [],
            "business_state" => $this->requestParams['business_state'],
            "business_city" => $this->requestParams['business_city'],
            "business_address" => $this->requestParams['business_address'],
            "business_postal_code" => $this->requestParams['business_postal_code'],
            "business_tel" => $this->requestParams['business_tel'],
            "business_fax" => $this->requestParams['business_fax'] ?? "",
            "business_type" => $this->requestParams['business_type'],
            "business_category" => $this->requestParams['business_category'],
            "business_property" => $this->requestParams['business_property'],
            "business_catalog" => $this->requestParams['business_catalog'] ?? []
        ]);
        if ($insert_form_mata["error"]) {
            switch ($insert_form_mata["error_code"]) {
                case ErrorCode::SERVER_ERROR :
                    $this->returnResponse(500, [], $insert_form_mata);
                    break;
                case ErrorCode::FORM_ID_NOT_EXIST || ErrorCode::INPUT_ALREADY_UPDATED :
                    $this->returnResponse(400, [], $insert_form_mata);
                    break;
            }
        }

        $this->returnResponse(200, [], $insert_form_mata);
    }

    private function getAllForm()
    {

        /**
         * get all form for administrator.
         */
        if (in_array(UserRoles::ADMINISTRATOR, $this->currentUserCapabilities)) {
            $get_all_media = $this->formService->getAllForm();
            if ($get_all_media["error"]) {
                $this->returnResponse(404, [], $get_all_media);
            }
            $this->returnResponse(200, [], $get_all_media);
        }

        /**
         * get all form for customer.
         */
        if (in_array(UserRoles::CUSTOMER, $this->currentUserCapabilities)) {
            $get_all_media = $this->formService->getAllForm($this->currentUserId);
            if ($get_all_media["error"]) {
                $this->returnResponse(404, [], $get_all_media);
            }
            $this->returnResponse(200, [], $get_all_media);
        }

    }

}