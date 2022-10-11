<?php

namespace Zlt\LaravelApiAuth\Controllers;

use Zlt\LaravelApiAuth\Actions\Delete;
use Zlt\LaravelApiAuth\Enums\Status;
use Zlt\LaravelApiAuth\Support\ApiResponse;

class DeleteController
{
    public function __invoke(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $delete = new Delete();
        $response = $delete($request->all());
        if ($response instanceof ApiResponse) {
            return $response();
        }
        return (new ApiResponse('Successfully Deleted', Status::OK, $response->toArray()))();
    }
}
