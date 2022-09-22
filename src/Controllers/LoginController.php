<?php

namespace Zlt\LaravelApiAuth\Controllers;

use Zlt\LaravelApiAuth\Actions\Login;

class LoginController
{
    public function __invoke(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $response = call_user_func(new Login($request->all()));
        return $response();
    }
}
