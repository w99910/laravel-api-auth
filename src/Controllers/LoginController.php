<?php

namespace Zlt\LaravelApiAuth\Controllers;

use Zlt\LaravelApiAuth\Actions\Login;
use Zlt\LaravelApiAuth\Enums\Status;
use Zlt\LaravelApiAuth\Support\ApiResponse;

class LoginController
{
    public function __invoke(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $login = new Login;
        $response = $login($request->all());
        if ($response instanceof ApiResponse) {
            return $response();
        }
        return (new ApiResponse('Successfully login', Status::OK, $response->toArray()))();
    }
}
