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
    'request' => [
        /*
         * 'column' => request parameter/s. Values accept:
         * - string: column will be exactly look up with value (e.g 'username' => 'name' )
         * - array: column will be look up inside value. (i.e, like in_array php function)  (e.g 'address' => ['localAddress','regionalAddress'])
         * - array with keys: column will be look up between the key and the value. (e.g 'date' => ['startDate' => 'endDate'])
         * For e.g.,
         */

        // \Zlt\LaravelApiAuth\Support\QueryableColumn::from('date', ['startDate', 'endDate'], 'date_format:Y-m-d|required_with:startDate|required_with:endDate', \Zlt\LaravelApiAuth\Support\Operator::BETWEEN),
    ]
];
