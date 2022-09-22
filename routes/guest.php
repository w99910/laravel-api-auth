<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
    Route::post('/login', Zlt\LaravelApiAuth\Controllers\LoginController::class);
    Route::post('/register', Zlt\LaravelApiAuth\Controllers\RegisterController::class);
});
