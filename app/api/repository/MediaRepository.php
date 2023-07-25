<?php

namespace App\api\repository;

use App\api\model\Media;
use App\core\BaseRepository;

class MediaRepository extends BaseRepository
{

    private array $validate_media_table = [
        "media_id",
        "user_id",
        "media_name",
        "media_extension",
        "media_link",
        "created_at",
        "updated_at"
    ];

    public function __construct()
    {
        $this->model = Media::class;
        $this->validate_select = $this->validate_media_table;
    }

}