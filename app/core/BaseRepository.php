<?php

namespace App\core;

use Illuminate\Support\Carbon;

defined('ABSPATH') || die();

class BaseRepository
{

    protected $model;

    protected array $validate_select;

    public function findById(int $id, array $select = [])
    {
        $provider_select = $select;

        if (count(array_diff($provider_select, $this->validate_select)) != 0) {
            $provider_select = [];
        }

        return $this->model::select(...$provider_select)->find($id);
    }

    public function fetchAll(array $select = [], array $where = [], int $skip = 0, int $take = 999)
    {
        $provider_select = $select;

        if (count(array_diff($provider_select, $this->validate_select)) != 0) {
            $provider_select = [];
        }

        return $this->model::select(...$provider_select)
            ->where($where)
            ->skip($skip)
            ->take($take)
            ->get();

    }

    /**
     * Successful return result
     * Unsuccessful return null
     **/
    public function fetchByConditional(array $select = [], array $where = [])
    {
        $provider_select = $select;

        if (count(array_diff($provider_select, $this->validate_select)) != 0) {
            $provider_select = [];
        }

        $result = $this->model::select(...$provider_select)
            ->where($where)
            ->first();

        if (is_bool($result) && !$result) {
            return null;
        }

        return $result;
    }

    public function fetchAllCount(array $where = []): int
    {
        return $this->model::where($where)->count();
    }

    /**
     * Successful return true
     * Unsuccessful return false
     **/
    public function deleteById(int $id): bool
    {

        $item = $this->findById($id);

        if ($item) {
            return $item->delete();
        }

        return false;

    }

    /**
     * Successful return primary id
     * Unsuccessful return false
     **/
    public function insertGetId(array $fields)
    {
        $all_fields = array_merge($fields, ['created_at' => Carbon::now()->toDateTimeString(), 'updated_at' => Carbon::now()->toDateTimeString()]);
        return $this->model::insertGetId($all_fields);
    }

    /**
     * Successful return true
     * Unsuccessful return false
     **/
    public function update(array $where, array $update)
    {
        return $this->model::where($where)->update($update);
    }

}