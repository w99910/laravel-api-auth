<?php

namespace Zlt\LaravelApiAuth\Support;

use Illuminate\Support\Facades\Validator;

class ApiRequest
{
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $orderBy = null;
    public ?int $limit = null;
    public bool $isDesc = true;
    public ?array $hiddenFields = null;
    public ?array $selectedFields = null;

    public function __construct(public array $values)
    {
    }

    public function validated(): ApiResponse|static
    {
        $validator = Validator::make($this->values, [
            'startDate' => 'date_format:Y-m-d|required_with:endDate',
            'endDate' => 'date_format:Y-m-d|required_with:startDate',
            'orderBy' => 'string',
            'limit' => 'int|min:1',
            'isDesc' => 'boolean',
            'hiddenFields' => 'string',
            'selectedFields' => 'string'
        ]);

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
        return $this;
    }
}
