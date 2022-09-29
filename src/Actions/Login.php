<?php

namespace Zlt\LaravelApiAuth\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Zlt\LaravelApiAuth\Support\ApiResponse;
use Zlt\LaravelApiAuth\Enums\Status;

class Login
{
    private string $username;

    private string $authClass;

    public function __construct()
    {
        $this->username = config('laravel-api-auth.username', 'email');

        $this->authClass = config('laravel-api-auth.authUser', \App\Models\User::class);
    }

    public function __invoke(array $values): ApiResponse|Model
    {
        if (!class_exists($this->authClass)) {
            return new ApiResponse('Auth class not exist', Status::INTERNAL_SERVER_ERROR, []);
        }

        if (!(new $this->authClass()) instanceof Model) {
            return new ApiResponse('Auth class should be instance of Model', Status::INTERNAL_SERVER_ERROR, []);
        }

        $rules = ['password' => 'required'];
        if ($this->username === 'email') {
            $rules['email'] = 'required|email';
        } else {
            $rules[$this->username] = 'required';
        }
        $validator = Validator::make($values, $rules);

        if ($validator->fails()) {
            return new ApiResponse('Validation fails', Status::FORBIDDEN, $validator->errors()->messages());
        }

        $credentials = $validator->validated();

        $user = $this->authClass::firstWhere($this->username, $credentials[$this->username]);

        if (!$user || !Hash::check($credentials['password'], $user['password'])) {
            return new ApiResponse('Incorrect email or password', Status::UNAUTHORIZED, [$this->username => $credentials[$this->username]]);
        }

        if (config('laravel-api-auth.enableSanctum', true)) {
            $user->tokens()->firstWhere('name', 'auth')?->delete();
            $user->token = $user->createToken('auth')->plainTextToken;
        }

        return $user;
    }
}
