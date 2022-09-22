<?php

namespace Zlt\LaravelApiAuth\Support;

class ApiResponse
{
    public function __construct(public readonly string $message, public readonly Status $status, public readonly array $data = [])
    {
    }

    public function __invoke(): \Illuminate\Http\JsonResponse
    {
        return response()->json(array_merge(['message' => $this->message, 'data' => $this->data]), $this->status->value);
    }
}
