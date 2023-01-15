# Laravel Api Auth

This package provides basic actions to authenticate a user without the needs of your own implementation.

## Installation

```bash
$ composer require zlt/laravel-api-auth
```

## Publish config file

```bash
$ php artisan vendor:publish --provider="Zlt\LaravelApiAuth\LaravelApiAuthServiceProvider"
```

### Usage

There are two main action classes:

- ### Login Action
  `Zlt\LaravelApiAuth\Actions\Login:class` can be used to log user in.
    - Create instance with `values` as parameter.
    - Let action handle validation and checking credentials.
    - `Zlt\LaravelApiAuth\Support\ApiResponse` will be always returned with corresponding message, additional data and
      status code.

    ```php
    $values = ['email'=>'mail@mail.com','password' => '12345678'];
    $login = new Zlt\LaravelApiAuth\Actions\Login(values: $values);
    $response = $login();
    ```

- ### Register Action
  `Zlt\LaravelApiAuth\Actions\Register::class` can be used to register a user.
    - Create instance with `values` as parameter.
    - Let action handle validation and checking credentials.
    - `Zlt\LaravelApiAuth\Support\ApiResponse` will be always returned with corresponding message, additional data and
      status code.
    ```php
    $values = ['email'=>'mail@mail.com','password' => '12345678'];
    $register = new Zlt\LaravelApiAuth\Actions\Register(values: $values);
    $response = $register();
    ```

- ### Configuration
     The following are the default configs.
    ```php
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
     ];
    ```
