<?php

namespace App\Traits\RequestHelpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;


trait APIResponse
{
    /*
      *  Api response codes
      * ----------------------
      *  200 means response OK
      *  201 means data created
      */
    private function successResponse($data, $code)
    {
        return response()->json($data, $code);
    }

    private function successResponseWithCookie($data, $cookie, $code=200)
    {
        return response()
            ->json($data, $code)   // JsonResponse object
            ->withCookie(cookie($cookie['name'], $cookie['value'], 50000));
    }

    protected function errorResponse($data, $code)
    {
        return response()->json($data, $code);
    }

    protected function showAll(Collection $collection, $code = 200)
    {
        return response()->json(['data' => $collection], $code);
    }

    protected function showOne(Model $model, $code = 200)
    {
        return response()->json(['data' => $model] , $code);
    }
}
