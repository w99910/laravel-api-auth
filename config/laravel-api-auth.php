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

    /*
     * Only specified columns will be accepted in request.
     */
    'queryableColumns' => [
//        \Zlt\LaravelApiAuth\Support\QueryableColumn::from('dateTime',
//            ['startDate', 'endDate'],
//            'date_format:Y-m-d|required_with:startDate|required_with:endDate',
//            \Zlt\LaravelApiAuth\Enums\Operator::BETWEEN,
//            function ($value, $parameter) {
//                $date = new Carbon\Carbon($value);
//                if ($parameter === 'startDate') {
//                    return $date->startOfDay()->toDateTimeString();
//                }
//                return $date->endOfDay()->toDateTimeString();
//            }),
    ],

    'cache' => [
        'enable' => false,
        'cache-prefix' => null,
    ]
];
