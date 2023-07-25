<?php

namespace App\api\repository;

use App\api\model\MediaMeta;
use App\core\BaseRepository;

class MediaMetaRepository extends BaseRepository
{

    private array $validate_media_meta_table = [
        "mmeta_id",
        "media_id",
        "meta_key",
        "meta_value",
        "created_at",
        "updated_at"
    ];

    public function __construct()
    {
        $this->model = MediaMeta::class;
        $this->validate_select = $this->validate_media_meta_table;
    }

}