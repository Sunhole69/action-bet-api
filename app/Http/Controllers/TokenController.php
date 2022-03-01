<?php

namespace App\Http\Controllers;

use App\Traits\HttpResource;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    use HttpResource;

    public function testFetch(){
        return $this->fetch('https://jsonplaceholder.typicode.com/users');
    }

    public function getToken(Request $request) {

        $username = $request->input('username', 'actionbet');
        $password = $request->input('password', 'password');

        // $header = $request->header('Authorization');
        $authToken = $request->bearerToken();

        if ($authToken == "11ae2a8d-39a0-4fff-8b37-b9359d2f0c89") {

            $hTTPController = New HTTPController();

            // $token = $hTTPController::getToken();
            // $token = $hTTPController->getToken();
            $token = $hTTPController->getToken($username, $password);

            return $token;



        }
        $response = ["message" =>'Unable to retrieve token, please try again'];
        return response($response, 500);
    }



}
