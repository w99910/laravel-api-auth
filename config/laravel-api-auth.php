<?php

return [
    /*
     * If true, `api/login` and `api/register` routes will be registered.
     */
    'shouldIncludeRoutes' => true,

    /*
     * Define your auth model
     */
    'authUser' => App\Models\User::class,

    /*
     * If true, action will try to create token in registration and appending token in login.
     */
    'enableSanctum' => true,

    /*
     * In some cases, you might want to use other column than `email`
     */
    'username' => 'email',

    /*
     * Validation rule for password in registration
     */
    'password:rule' => 'required|min:8',

    /*
     * Determine if api routes should have `application/json` as Accept header
     */
    'addApplicationJsonHeader' => true,

    /**
     * When using `Zlt\LaravelApiAuth\Utils\CanCache` trait, you can define cache prefix here.
     */
    'cache-prefix' => null,
];
