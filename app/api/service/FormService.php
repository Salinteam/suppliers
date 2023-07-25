<?php

namespace App\api\service;

use App\api\repository\FormMetaRepository;
use App\api\repository\FormsRepository;
use App\core\ErrorCode;
use App\core\ResultHandler;
use App\utils\forms\FormMeta;
use App\utils\users\UserMeta;
use App\utils\users\UserRoles;

class FormService
{
    use ResultHandler;

    private FormsRepository $formsRepository;
    private FormMetaRepository $formMetaRepository;
    private UserService $userService;

    public function __construct()
    {
        $this->formsRepository = new FormsRepository();
        $this->formMetaRepository = new FormMetaRepository();
        $this->userService = new UserService();
    }

    public function checkFormExist(string $field_form, string|int $value): bool
    {
        $user_exist = $this->formsRepository->fetchByConditional(["form_id"], [$field_form => $value]);
        if (is_null($user_exist)) {
            return false;
        }
        return true;
    }

    public function insertForm(int $user_id): array
    {
        /**
         * insert form.
         */
        $form_id = $this->formsRepository->insertGetId([
            "user_id" => $user_id
        ]);
        if (!$form_id) {
            return $this->throwError(ErrorCode::SERVER_ERROR);
        }

        return $this->success(["form_id" => $form_id]);
    }

    public function getAllForm(int $user_id = null): array
    {

        /**
         * get all form.
         */
        if (is_null($user_id)) {
            $get_all_form_user = $this->formsRepository->fetchAll();
        } else {
            $get_all_form_user = $this->formsRepository->fetchAll([], ["user_id" => $user_id]);
        }

        if (empty($get_all_form_user->all())) {
            return $this->throwError(ErrorCode::NOT_FOUND);
        }

        /**
         * get all form meta user.
         */
        $all_form_user = [];
        foreach ($get_all_form_user as $form) {
            $business_data = $this->getFormMeta($form["form_id"], FormMeta::BUSINESS_DATA);
            if ($business_data["error"]) {
                return $this->throwError(ErrorCode::SERVER_ERROR);
            }
            $all_form_user[] = array_merge(json_decode($form, true), $business_data["meta_value"]);
        }

        return $this->success(["result" => $all_form_user]);

    }

    public function setFormMeta(int $form_id, FormMeta|string $meta_key, string|array $meta_value): array
    {

        /**
         * check exist form_id.
         */
        $exist_form_id = $this->formsRepository->fetchByConditional(["form_id"], ["form_id" => $form_id]);
        if (is_null($exist_form_id)) {
            return $this->throwError(ErrorCode::FORM_ID_NOT_EXIST);
        }

        /**
         * check exist meta_key for form_id.
         */
        $exist_meta_key = $this->formMetaRepository->fetchByConditional(["fmeta_id"], ["form_id" => $form_id, "meta_key" => $meta_key]);
        if (is_null($exist_meta_key)) {
            $insert_form_meta = $this->formMetaRepository->insertGetId([
                "form_id" => $form_id,
                "meta_key" => $meta_key,
                "meta_value" => is_string($meta_value) ? $meta_value : serialize($meta_value)
            ]);
            if (!$insert_form_meta) {
                return $this->throwError(ErrorCode::SERVER_ERROR);
            }
        } else {
            $exist_meta_value = $this->formMetaRepository->fetchByConditional(["fmeta_id"], ["form_id" => $form_id, "meta_key" => $meta_key, "meta_value" => $meta_value]);
            if (!is_null($exist_meta_value)) {
                return $this->throwError(ErrorCode::INPUT_ALREADY_UPDATED);
            }
            $this->formMetaRepository->update(
                [
                    "form_id" => $form_id,
                    "meta_key" => $meta_key
                ],
                [
                    "meta_value" => is_string($meta_value) ? $meta_value : serialize($meta_value)
                ]);
        }

        return $this->success();

    }

    public function getFormMeta(int $user_id, FormMeta|string $meta_key): array
    {
        $get_form_meta = $this->formMetaRepository->fetchByConditional(["meta_value"], ["form_id" => $user_id, "meta_key" => $meta_key]);
        if (is_null($get_form_meta)) {
            return $this->throwError(ErrorCode::META_VALUE_NOT_EXIST);
        }
        if (!unserialize($get_form_meta->meta_value)) {
            $form_meta = $get_form_meta->meta_value;
        } else {
            $form_meta = unserialize($get_form_meta->meta_value);
        }
        return $this->success(["meta_value" => $form_meta]);
    }

}