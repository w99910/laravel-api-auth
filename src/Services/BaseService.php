<?php

namespace Zlt\LaravelApiAuth\Services;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Zlt\LaravelApiAuth\Support\ApiRequest;
use Zlt\LaravelApiAuth\Support\ApiResponse;
use Zlt\LaravelApiAuth\Enums\Status;
use Zlt\LaravelApiAuth\Support\QueryableColumn;

abstract class BaseService implements Serviceable
{
    protected array $hiddenFields = [];

    private array $queryColumns = [];

    private array $extraRules = [];

    public function __construct(protected QueryBuilder|EloquentBuilder $builder)
    {
        foreach ($this->queryColumns as $queryColumn) {
            if ($queryColumn instanceof QueryableColumn) {
                // If parameter is an array
                if (is_array($queryColumn->requestParameter)) {
                    foreach ($queryColumn->requestParameter as $parameter) {
                        $this->extraRules[$parameter] = $queryColumn->rule;
                    }
                    continue;
                }
                $this->extraRules[$queryColumn->requestParameter] = $queryColumn->rule;
            }
        }
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

    private function query(ApiRequest $request): EloquentBuilder|QueryBuilder
    {
        $query = $this->builder;
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
        return $this->processExtraQueries($query, $request->getValidatedValues());
    }

    private function processExtraQueries(QueryBuilder|EloquentBuilder $query, array $validatedValues): EloquentBuilder|QueryBuilder
    {
        foreach ($this->queryColumns as $column) {
            if ($column instanceof QueryableColumn) {
                $parameters = $column->requestParameter;
                $parameterIsArrayAndValid = is_array($parameters) && in_array($parameters[0], array_keys($validatedValues));
                $parameterIsValid = !is_array($parameters) && in_array($parameters, array_keys($validatedValues));
                if ($parameterIsArrayAndValid || $parameterIsValid) {
                    $query = $column->query($query, $validatedValues);
                }
            }
        }
        return $query;
    }

    // Execution methods
    protected function get(array $values): ApiResponse
    {
        try {
            $request = (new ApiRequest($values));
            if (!empty($this->extraRules)) {
                $request->registerRules($this->extraRules);
            }
            $request = $request->validated();
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
        } catch (\Exception $e) {
            return new ApiResponse($e->getMessage(), Status::INTERNAL_SERVER_ERROR);
        }
    }

    protected function count(array $values): ApiResponse
    {
        try {
            $request = (new ApiRequest($values));
            if (!empty($this->extraRules)) {
                $request->registerRules($this->extraRules);
            }
            $request = $request->validated();
            if ($request instanceof ApiResponse) {
                return $request;
            }
            $query = $this->query($request);
            return new ApiResponse('Success', Status::OK, ['count' => $query->get()->count()]);
        } catch (\Exception $e) {
            return new ApiResponse($e->getMessage(), Status::INTERNAL_SERVER_ERROR);
        }
    }

    protected function registerQueryColumn(QueryableColumn $column)
    {
        $this->queryColumns[] = $column;
    }
}
