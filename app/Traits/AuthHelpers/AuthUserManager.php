<?php


namespace App\Traits\AuthHelpers;


use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;

trait AuthUserManager
{

    use AuthTokenProvider;
    public function getCurrentUser(Request $request)
    {
       $token =  Token::where('token', $request->bearerToken())->first();

       if ($token){
           // Check if the token is still valid and update it
           $user = User::where('username', $token->username)->first();
           $data = [
               'username'  => $user->username,
               'user_type' => $user->user_type
           ];

           //Determine the authentication path through the user_type
           if (ucwords($data['user_type']) === 'Player'){
               $response = $this->initiatePlayerToken($data);
           }

           if (ucwords($data['user_type']) === 'Agency'){
               $response = $this->initiateAgencyToken($data);
           }

           if (ucwords($data['user_type']) === 'Admin'){
               $response = $this->initiateAdminToken($data);
           }

          return $user;
       }
       return false;
    }

}
