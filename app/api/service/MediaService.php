<?php

namespace App\api\service;

use App\api\repository\MediaMetaRepository;
use App\api\repository\MediaRepository;
use App\core\ErrorCode;
use App\core\ResultHandler;
use App\utils\media\MediaMeta;
use Exception;

class MediaService
{

    use ResultHandler;

    private MediaRepository $mediaRepository;
    private MediaMetaRepository $mediaMetaRepository;

    public function __construct()
    {
        $this->mediaRepository = new MediaRepository();
        $this->mediaMetaRepository = new MediaMetaRepository();
    }

    public function getAllMedia(int $user_id = null): array
    {

        /**
         * get all media.
         */
        if (is_null($user_id)) {
            $get_all_media = $this->mediaRepository->fetchAll();
        } else {
            $get_all_media = $this->mediaRepository->fetchAll([], ["user_id" => $user_id]);
        }

        if (empty($get_all_media->all())) {
            return $this->throwError(ErrorCode::NOT_FOUND);
        }

        return $this->success(["result" => $get_all_media]);

    }

    private function organizeMedia(array $media): array
    {
        $organize_media = [];
        if (is_string($media["name"])) {
            $organize_media["name"][] = $media["name"];
            $organize_media["type"][] = $media["type"];
            $organize_media["tmp_name"][] = $media["tmp_name"];
        } else {
            foreach ($media["name"] as $index => $_media) {
                $organize_media["name"][] = $media["name"][$index];
                $organize_media["type"][] = $media["type"][$index];
                $organize_media["tmp_name"][] = $media["tmp_name"][$index];
            }
        }
        return $organize_media;
    }

    public function uploadMedia(int $user_id, array $media, ?string $dir): array
    {

        $uploads = [];

        /**
         * check exist uploads dir.
         */
        is_dir(PROJECT_UPLOADS_PATH) || mkdir(PROJECT_UPLOADS_PATH, 0777, true);

        try {

            $_media = $this->organizeMedia($media);

            foreach ($_media["name"] as $index => $_media_name) {

                $media_name = pathinfo($_media_name, PATHINFO_FILENAME);
                $media_mime = pathinfo($_media_name, PATHINFO_EXTENSION);
                $tmp_media_name = uniqid() . "." . $media_mime;

                if (is_null($dir)) {

                    move_uploaded_file($_media["tmp_name"][$index], PROJECT_UPLOADS_PATH . $tmp_media_name);

                } else {

                    /**
                     * check exist custom dir.
                     */
                    is_dir(PROJECT_UPLOADS_PATH . $dir) || mkdir(PROJECT_UPLOADS_PATH . $dir, 0777, true);

                    $path = trim($dir, "/");
                    $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
                    move_uploaded_file($_media["tmp_name"][$index], PROJECT_UPLOADS_PATH . $path . DIRECTORY_SEPARATOR . $tmp_media_name);

                }

                $media_id = $this->mediaRepository->insertGetId([
                    "user_id" => $user_id,
                    "media_name" => $media_name,
                    "media_extension" => $media_mime,
                    "media_link" => ($dir . "/" . $tmp_media_name)
                ]);

                $uploads[] = [
                    "media_id" => $media_id,
                    "media_extension" => $media_mime,
                    "media_name" => $media_name,
                    "media_link" => ($dir . "/" . $tmp_media_name)
                ];

            }

            return $this->success($uploads);

        } catch (Exception $e) {

            return $this->throwError(ErrorCode::SERVER_ERROR);

        }

    }

    public function setMediaMeta(int $media_id, MediaMeta|string $meta_key, string|array $meta_value): array
    {

        /**
         * check exist media_id.
         */
        $exist_media_id = $this->mediaRepository->fetchByConditional(["media_id"], ["media_id" => $media_id]);
        if (is_null($exist_media_id)) {
            return $this->throwError(ErrorCode::MEDIA_ID_NOT_EXIST);
        }

        /**
         * check exist meta_key for media_id.
         */
        $exist_meta_key = $this->mediaMetaRepository->fetchByConditional(["mmeta_id"], ["media_id" => $media_id, "meta_key" => $meta_key]);
        if (is_null($exist_meta_key)) {
            $insert_media_meta = $this->mediaMetaRepository->insertGetId([
                "media_id" => $media_id,
                "meta_key" => $meta_key,
                "meta_value" => is_string($meta_value) ? $meta_value : serialize($meta_value)
            ]);
            if (!$insert_media_meta) {
                return $this->throwError(ErrorCode::SERVER_ERROR);
            }
        } else {
            $exist_meta_value = $this->mediaMetaRepository->fetchByConditional(["mmeta_id"], ["media_id" => $media_id, "meta_key" => $meta_key, "meta_value" => $meta_value]);
            if (!is_null($exist_meta_value)) {
                return $this->throwError(ErrorCode::INPUT_ALREADY_UPDATED);
            }
            $this->mediaMetaRepository->update(
                [
                    "media_id" => $media_id,
                    "meta_key" => $meta_key
                ],
                [
                    "meta_value" => is_string($meta_value) ? $meta_value : serialize($meta_value)
                ]);
        }

        return $this->success(["media_id" => $media_id]);

    }

}