<?php

namespace Zlt\LaravelApiAuth\Services;

use Zlt\LaravelApiAuth\Support\ApiRequest;
use Zlt\LaravelApiAuth\Support\ApiResponse;
use Zlt\LaravelApiAuth\Support\Status;

abstract class BaseService
{
    protected array $hiddenFields = [];

    abstract static function getInstance(): static;

//    /**
//     * @return static
//     * @throws \Exception
//     */
//    static function getInstance(): static
//    {
//        throw new \Exception('Undefined method');
//    }

    final protected function __construct(protected \Jenssegers\Mongodb\Query\Builder|\Jenssegers\Mongodb\Eloquent\Builder $builder)
    {
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getInstance();
        return $instance->$method(...$args);
    }

    public function __call($method, $args)
    {
        return $this->$method(...$args);
    }

    private function query(ApiRequest $request): \Jenssegers\Mongodb\Query\Builder|\Jenssegers\Mongodb\Eloquent\Builder
    {
        $query = $this->builder;
        if ($request->startDate && $request->endDate) {
            $query = $query->whereBetween('dateTime', ["$request->startDate 00:00:00", "$request->endDate 23:59:59"]);
        }

        if ($request->orderBy) {
            $order = $request->isDesc ? 'orderByDesc' : 'orderBy';
            $query = $query->$order($request->orderBy);
        }

        if ($request->limit) {
            $limit = $request->limit > 1000 ? 1000 : $request->limit;
            $query = $query->limit($limit);
        }
        if ($request->hiddenFields) {
            $this->hiddenFields = $request->hiddenFields;
        }

        if ($request->selectedFields) {
            $query = $query->select($request->selectedFields);
        }
        return $query;
    }

    // Execution methods
    protected function get(array $values): ApiResponse
    {
        $request = (new ApiRequest($values))->validated();
        if ($request instanceof ApiResponse) {
            return $request;
        }
        $query = $this->query($request);
        $data = $query->get();
        if (!empty($this->hiddenFields)) {
            $data = $data->map(function ($item) {
                $temp = [];
                $item = !is_array($item) ? (method_exists($item, 'toArray') ? $item->toArray() : get_object_vars($item)) : $item;
                foreach (array_keys($item) as $key) {
                    if (!in_array($key, $this->hiddenFields)) {
                        $temp[$key] = $item[$key];
                    }
                }
                return $temp;
            });
        }
        return new ApiResponse('Success', Status::OK, $data->toArray());
    }

    protected function count(array $values): ApiResponse
    {
        $request = (new ApiRequest($values))->validated();
        if ($request instanceof ApiResponse) {
            return $request;
        }
        $query = $this->query($request);
        return new ApiResponse('Success', Status::OK, ['count' => $query->count()]);
    }
}
