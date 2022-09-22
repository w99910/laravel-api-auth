<?php

namespace Zlt\LaravelApiAuth\Controllers;

use Zlt\LaravelApiAuth\Actions\Register;

class RegisterController
{
    public function __invoke(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $response = call_user_func(new Register($request->all()));
        return $response();
    }
}
