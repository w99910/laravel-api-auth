<?php

namespace Zlt\LaravelApiAuth\Support;

use Zlt\LaravelApiAuth\Enums\Status;

class ApiResponse
{
    public function __construct(public readonly string $message, public readonly Status $status, public $data = [])
    {
    }

    public function __invoke(): \Illuminate\Http\JsonResponse
    {
        $data = ['message' => $this->message];
        if (is_array($this->data)) {
            $data = array_merge($data, $this->data);
        } else {
            $data[] = $this->data;
        }
        return response()->json($data, $this->status->value);
    }
}
