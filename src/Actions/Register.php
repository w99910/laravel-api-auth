<?php

namespace Zlt\LaravelApiAuth\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Zlt\LaravelApiAuth\Support\ApiResponse;
use Zlt\LaravelApiAuth\Support\Status;

class Register
{
    private string $username;

    private string $passwordRule;

    private string $authClass;

    public function __construct(public readonly array $values)
    {
        $this->username = config('laravel-api-auth.username', 'email');

        $this->passwordRule = config('laravel-api-auth.password:rule', 'required:min:8');

        $this->authClass = config('laravel-api-auth.authUser', \App\Models\User::class);
    }

    public function __invoke(): ApiResponse
    {
        if (!class_exists($this->authClass)) {
            return new ApiResponse('Auth class not exist', Status::INTERNAL_SERVER_ERROR, []);
        }
        $user = new $this->authClass();
        if (!$user instanceof Model) {
            return new ApiResponse('Auth class should be instance of Model', Status::INTERNAL_SERVER_ERROR, []);
        }

        $rules = ['password' => $this->passwordRule];

        if ($this->username === 'email') {
            $rules['email'] = 'required|email';
        } else {
            $rules[$this->username] = 'required';
        }
        $validator = Validator::make($this->values, $rules);

        if ($validator->fails()) {
            return new ApiResponse('Validation fails', Status::FORBIDDEN, $validator->errors()->messages());
        }

        $values = $this->values;

        if ($this->authClass::where($this->username, $values[$this->username])->exists()) {
            return new ApiResponse('User exists', Status::CONFLICT, [$this->username => $values[$this->username]]);
        }
        foreach (array_keys($this->values) as $attribute) {
            if ($attribute === 'password') {
                $values[$attribute] = Hash::make($values[$attribute]);
            }
            $user->$attribute = $values[$attribute];
        }
        $user->save();
        $user = $user->refresh();
        $data = $user->toArray();
        if (method_exists($user, 'createToken')) {
            $token = $user->createToken('auth');
            $data['token'] = $token->plainTextToken;
        }

        return new ApiResponse('Successfully Created', Status::OK, $data);
    }
}
