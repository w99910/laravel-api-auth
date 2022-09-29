<?php

namespace Zlt\LaravelApiAuth\Controllers;

use Zlt\LaravelApiAuth\Actions\Register;
use Zlt\LaravelApiAuth\Enums\Status;
use Zlt\LaravelApiAuth\Support\ApiResponse;

class RegisterController
{
    public function __invoke(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $register = new Register;
        $response = $register($request->all());
        if ($response instanceof ApiResponse) {
            return $response();
        }
        return (new ApiResponse('Successfully registered', Status::OK, $response->toArray()))();
    }
}
