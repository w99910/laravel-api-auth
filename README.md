# Laravel Api Auth

This package provides basic api authorization and easy-to-use servicing your Models.

- Using this package, you don't need to write your own authorization logic for your api.
  Even if you don't want to, you can use actions classes to ease your own authorization.
- You don't have to write your own query.
  Instead of using `Post::whereBetween(...)->orderByDesc(...)->get()`, you can use like
  ```php
  PostService::get([
    'startDate' => '2022-01-01',
    'endDate' => '2022-06-01',
    'orderBy' => 'date',
    'isDesc' => true,
  ])
  ```

## Table Of Contents

- [Installation](#installation)
- [Publish config file](#publish-config-file)
- [Authorization](#authorization)
    - [Manually Login and Register](#manually-login-and-register)
- [Servicing](#servicing)
    - [Why you would need this?](#why-you-would-need-this)
    - [How to use?](#how-to-use-it)
- [Caching Response](#caching-response)
- [License](#license)
- [Conclusion](#conclusion)

## Installation

```bash
$ composer require zlt/laravel-api-auth
```

## Publish config file

```bash
$ php artisan vendor:publish --provider="Zlt\LaravelApiAuth\LaravelApiAuthServiceProvider"
```

The following are the default config.

```php
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

```

## Authorization

There are already api routes defined for you to authorize a user such as `/api/login`, `/api/register`
and `/api/delete`.
In order to disable those routes, you can set the `shouldIncludeRoutes` option to `false` in
the `config/laravel-api-auth.php` file.

### Manually Login and Register

- #### Login a user
  `Zlt\LaravelApiAuth\Actions\Login:class` can be used to log user in.
    - Create instance.
    - invoke the instance `values` as parameter. Let action handle validation and checking credentials.
    - `Zlt\LaravelApiAuth\Support\ApiResponse` will be always returned with corresponding message, additional data and
      status code.

    ```php
    $values = ['email'=>'mail@mail.com','password' => '12345678'];
    $login = new Zlt\LaravelApiAuth\Actions\Login;
    $response = $login(values: $values);
    ```

- #### Registering a user
  `Zlt\LaravelApiAuth\Actions\Register::class` can be used to register a user.
    - Create instance.
    - invoke the instance `values` as parameter. Let action handle validation and checking credentials.
    - `Zlt\LaravelApiAuth\Support\ApiResponse` will be always returned with corresponding message, additional data and
      status code.
    ```php
    $values = ['email'=>'mail@mail.com','password' => '12345678'];
    $register = new Zlt\LaravelApiAuth\Actions\Register;
    $response = $register(values:$values);
    ```
- ### Deleting a user
  `Zlt\LaravelApiAuth\Actions\Delete::class` can be used to delete a user.
    - Create instance.
    - invoke the instance `values` as parameter. Let action handle validation and checking credentials.
    - `Zlt\LaravelApiAuth\Support\ApiResponse` will be always returned with corresponding message, additional data and
      status code.
  ```php
  $values = ['email'=>'mail@mail.com'];
  $delete = new Zlt\LaravelApiAuth\Actions\Delete;
  $response = $delete($values);
  ```

## Servicing

### Why you would need this?

The idea of servicing is to provide a easy-to-use way to build database query and execute it.

For example, you have a `Post` model and you want to build query instead of using
like `Post::whereBetween('date',['2022-01-01','2022-06-01'])->orderByDesc('date')->get()`. Then you can use the
following service:

```php
<?php

namespace App\Services;

use App\Models\Post;
use Zlt\LaravelApiAuth\Enums\Operator;
use Zlt\LaravelApiAuth\Services\BaseService;
use Zlt\LaravelApiAuth\Support\QueryableColumn;

/**
 * @method static ApiResponse get(array $values);
 * @method static ApiResponse count(array $values);
 */
class PostService extends BaseService
{
    public function __construct()
    {
        $this->registerQueryColumn(QueryableColumn::from(
            'date',
            ['startDate', 'endDate'],
            'date_format:Y-m-d|required_with:startDate|required_with:endDate',
            Operator::BETWEEN,
        ));

        parent::__construct(Post::query());
    }

    static function getInstance(): static
    {
        return new static();
    }
}
```

Then call static method `get` or `count` to get the result.

```php
PostService::get([
    'startDate' => '2022-01-01',
    'endDate' => '2022-06-01',
    'orderBy' => 'date',
    'isDesc' => true,
]);

PostService::count([
    'startDate' => '2022-01-01',
    'endDate' => '2022-06-01',
    'orderBy' => 'date',
    'isDesc' => true,
]);
```

### How to use it?

- First, you need to extend `Zlt\LaravelApiAuth\Services\BaseService` and implement the `getInstance` method.
  ```php
  class YourService extends Zlt\LaravelApiAuth\Services\BaseService{
  
    static function getInstance(): static
    {
       
    }
  }
  ```
- Then, you need to pass your `Builder` instance to constructor or inside the constructor.
  ```php
  // Pass to constructor
  static function getInstance(): static
  {
     return new static(YourModel::query());
  }
  
  // Inside constructor
  public function __construct()
  {
     parent::__construct(YourModel::query());
  }
  ```
- You can use some built-in features offered by package.
    - `orderBy`
    - `limit`
    - `offset`
    - `hiddenFields`
    - `selectedFields`

  For example,
    ```php
    PostService::get([
        'orderBy' => 'date',
        'isDesc' => true,
        'limit' => 10,
        'offset' => 0,
        'hiddenFields' => ['id','created_at','updated_at'],
        'selectedFields' => ['title','date'],
    ]);
    ```
- You can register queryable column and cast the value before using in query.
  For example, you want to get posts between two dates. ( Assume there is `date` column)
  ```php
  // Register queryable column in constructor
  class YourService extends Zlt\LaravelApiAuth\Services\BaseService{
  
    public function __construct()
    {
        $this->registerQueryColumn(QueryableColumn::from(
            'date', // DB Column
            ['startDate', 'endDate'], // Request Parameters
            'date_format:Y-m-d|required_with:startDate|required_with:endDate', // Validation Rule
            Operator::BETWEEN, // Operator
        ));
        parent::__construct(YourModel::query());
    }
  }
 
  // And then 
  YourService::get([
      'startDate' => '2022-01-01',
      'endDate' => '2022-06-01',
  ]);
  ```

  You can also cast the value before using in query.
  ```php
  public function __construct()
  {
      $this->registerQueryColumn(QueryableColumn::from(
          'date', // DB Column
          ['startDate', 'endDate'], // Request Parameters
          'date_format:Y-m-d|required_with:startDate|required_with:endDate', // Validation Rule
          Operator::BETWEEN, // Operator
          function ($value, $parameter) {
                $date = new Carbon($value);
                if ($parameter === 'startDate') {
                    return $date->startOfDay()->timestamp;
                }
                return $date->endOfDay()->timestamp;
            }
      ));
      parent::__construct(YourModel::query());
  }
  ```

## Caching response

This package also offers a trait to cache response.

```php
use Zlt\LaravelApiAuth\Utils\CanCache;

class YourClass {

    use CanCache;
  
    public function yourMethod(Request $request){
        if($this->checkIfCacheKeyExists($request)){
            return $this->getCacheData($request);
        }
        $response = $this->computeData();
        $this->storeCache($request,$response);
        return $response;
    }
}
```

## License

[MIT](LICENSE)

## Conclusion

Why I created this package is because I needed to use such features in my projects. But now I think it would probably
help
others too. So I decided to share it with you. I hope you find it useful.

Feel free to contribute to this package.

If you find it useful, please give it a star or buy me a coffee via Binance.

<img src="https://zawlintun.me/BinancePayQR.png" alt="binancePayQR" width="200"/>

Cheers!


