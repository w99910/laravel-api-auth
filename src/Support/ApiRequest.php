<?php

namespace Zlt\LaravelApiAuth\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Validator;
use Zlt\LaravelApiAuth\Enums\Status;

class ApiRequest
{
    public ?string $orderBy = null;
    public ?int $limit = null;
    public bool $isDesc = true;
    public ?array $hiddenFields = null;
    public ?array $selectedFields = null;
    private array $rules = [
        'orderBy' => 'string',
        'limit' => 'int|min:1',
        'isDesc' => 'boolean',
        'hiddenFields' => 'string',
        'selectedFields' => 'string'
    ];
    protected array $queryChains = [];
    protected array $validatedValues = [];


    public function processQuery(QueryBuilder|EloquentBuilder $query): QueryBuilder|EloquentBuilder
    {
        if (empty($this->validatedValues)) {
            $this->validated();
        }
        foreach ($this->queryChains as $queryChain) {
            $query = $queryChain($query, $this->validatedValues);
        }
        return $query;
    }

    public function __construct(public array $values)
    {
        $queryableColumns = config('laravel-api-auth.queryableColumns');
        if (!empty($queryableColumns)) {
            $customRules = [];
            foreach ($queryableColumns as $column) {
                if ($column instanceof QueryableColumn) {
                    $parameters = $column->requestParameter;
                    $this->queryChains[$column->column] = fn($query, $parameters) => $column->query($query, $parameters);
                    if (is_array($parameters)) {
                        foreach ($parameters as $parameter) {
                            $customRules[$parameter] = $column->rule;
                        }
                        continue;
                    }
                    $customRules[$parameters] = $column->rule;
                }
            }
            $this->rules = array_merge($this->rules, $customRules);
        }
    }

    public function validated(): ApiResponse|static
    {
        $validator = Validator::make($this->values, $this->rules);

        if ($validator->fails()) {
            return new ApiResponse('Validation fails', Status::FORBIDDEN, $validator->errors()->messages());
        }

        foreach ($validator->validated() as $key => $value) {
            if (property_exists($this, $key)) {
                if (in_array($key, ['hiddenFields', 'selectedFields'])) {
                    $this->$key = explode(',', $value);
                    continue;
                }
                $this->$key = $value;
            }
        }
        $this->validatedValues = $validator->validated();
        return $this;
    }

    public function getValidatedValues(): array
    {
        return $this->validatedValues;
    }
}
