<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerPlayerActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use APIResponse;
    use RemoteAPIServerPlayerActions;

    public function registerAffiliate(Request $request){
        //1. Validating user inputs
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username',
            'phone'     => 'required|string|unique:users,phone',
            'email'     => 'required|string|unique:users,email',
            'password'  => 'required|string|confirmed'
        ]);

        // Create the data needed for the remote BETTING API
        $data = [
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'username'  => $request->username,
            'password'  => $request->password,
            'role'      => 'player',
            'enabled'   => true,
        ];

        $response = $this->registerAffiliateRemotely($data);

        // If successful, save the user details into local database
        if ($response['errorCode'] === "SUCCESS"){
            User::create($request->all());
        }

        //4. Return response to user along with cookie for authentication
        return $this->successResponse($response,200);
    }

    public function signUp(Request $request){
       //1. Validating user inputs
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'agency'    => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username',
            'phone'     => 'required|string|unique:users,phone',
            'email'     => 'required|string|unique:users,email',
            'password'  => 'required|string|confirmed'
        ]);

        // Check if agency exist
        if (!User::where('username', $request->agency)->first()){
            return $this->errorResponse([
                'message' => 'Agency doesn\'t exist'
            ], 422);
        }

        // Create the data needed for the remote BETTING API
        $data = [
            'firstname'       => $request->firstname,
            'lastname'        => $request->lastname,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'username'        => $request->username,
            'password'        => $request->password,
            'agency'          => $request->agency,
            'user_type'       => 'Player',
            'enabled'         => true,
        ];
        // Drop the user details with the remote BETTING API
        $response = $this->registerPlayerRemotely($data);

        // If successful, save the user details into local database
        if ($response['errorCode'] === "SUCCESS"){
         $user =  User::create($data);
          Wallet::create([
              'user_id' => $user->id,
              'balance'  => 0,
              'bonus'   => 0
          ]);
        }

        // Return response to user
        return $this->successResponse($response,200);
    }

    public function login(Request $request){
        //1. Validating user inputs
        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        //2. Verify user email
        $user = User::where('username', $fields['username'])->first();

        //3. verify user password, if authentication fails
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            $response = [
                'status' => 'fail',
                'message' => 'Invalid username or password *l'
            ];
            return $this->errorResponse($response, 401);
        }

        // Create the data needed for the remote BETTING API
        $data = [
            'username'  => $request->username,
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

       $responseData = [
           'role' => ucwords($data['user_type']),
           'user' => $user,
           'wallet' => $user->wallet,
           'token' => $response,
       ];

        //4. Return response to user along with cookie for authentication
        return $this->successResponse($responseData,200);
    }

    public function agencyLogin(Request $request){
        //1. Validating user inputs
        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        //2. Verify user email
        $user = User::where('username', $fields['username'])->first();

        //3. verify user password, if authentication fails
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            $response = [
                'status' => 'fail',
                'message' => 'Invalid username or password'
            ];
            return $this->errorResponse($response, 401);
        }

        // Create the data needed for the remote BETTING API
        $data = [
            'username'  => $request->username,
        ];
        $response = $this->initiateAgencyToken($data);

        $responseData = [
            'user' => $user,
            'token' => $response
        ];

        //4. Return response to user along with cookie for authentication
        return $this->successResponse($responseData,200);
    }

    public function adminLogin(Request $request){
        //1. Validating user inputs
        $fields = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        //2. Verify user email
        $user = User::where('username', $fields['username'])->first();

        //3. verify user password, if authentication fails
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            $response = [
                'status' => 'fail',
                'message' => 'Invalid username or password'
            ];
            return $this->errorResponse($response, 401);
        }

        // Create the data needed for the remote BETTING API
        $data = [
            'username'  => $request->username,
        ];
        $response = $this->initiateAgencyToken($data);

        $responseData = [
            'user' => $user,
            'token' => $response
        ];

        //4. Return response to user along with cookie for authentication
        return $this->successResponse($responseData,200);
    }

}
