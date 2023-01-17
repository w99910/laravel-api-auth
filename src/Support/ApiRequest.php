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

    public ?int $offset = null;
    public bool $isDesc = true;
    public ?array $hiddenFields = null;
    public ?array $selectedFields = null;

    private array $rules = [
        'orderBy' => 'string',
        'limit' => 'int|min:1',
        'offset' => 'int|min:0',
        'isDesc' => 'boolean',
        'hiddenFields' => 'string',
        'selectedFields' => 'string'
    ];
    protected array $validatedValues = [];

    public function __construct(public array $values)
    {
    }

    public function registerRules(array $rules): void
    {
        $this->rules = array_merge($this->rules, $rules);
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
