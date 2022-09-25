<?php

namespace Zlt\LaravelApiAuth\Support;

use Throwable;

class ApiResponseException extends \Exception implements Throwable
{
    private ApiResponse $apiResponse;

    public function __construct($message, Status $status, array $data = [], Throwable $previous = null,)
    {
        // some code
        $this->apiResponse = new ApiResponse($message, $status, $data);
        // make sure everything is assigned properly
        parent::__construct($message, $status->value, $previous);
    }

    public function toResponse(): \Illuminate\Http\JsonResponse
    {
        return ($this->apiResponse)();
    }
}
