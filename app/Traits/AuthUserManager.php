<?php


namespace App\Traits;


use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;

trait AuthUserManager
{
    public function getCurrentUser(Request $request)
    {
       $token =  Token::where('token', $request->bearerToken())->first();
       if ($token){
          return User::where('username', $token->username)->first();
       }
       return false;
    }

}
