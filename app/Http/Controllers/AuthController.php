<?php

namespace App\Http\Controllers;

use App\Models\PadiWinUser;
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
            'role'      => 'agency',
            'enabled'   => true,
        ];

        $response = $this->registerAffiliateRemotely($data);

        // If successful, save the user details into local database
        if ($response['errorCode'] === "SUCCESS"){
            $regData = $request->all();
            $regData['user_type'] = 'agency';
            User::create($regData);
        }

        // If successful, save the user details into local database
        if ($response['errorCode'] === "SUCCESS"){
            $user =  User::create($data);
            Wallet::create([
                'user_id' => $user->id,
                'balance'  => 0,
                'bonus'   => 0
            ]);
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

    public function signUpReferredUser(Request $request, $user_ref_id){
        // Find referer
        $refUser = PadiWinUser::where('user_ref_id', $user_ref_id)->first();
        if (!$refUser){
            return $this->errorResponse([
                'errorCode' => 'PADIWIN_ACCOUNT_ERROR',
                'message'   => 'Invalid padiwin referrer link'
            ], 404);
        }

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
                'errorCode' => 'AGENCY_ERROR',
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
            'referred'        => true,
            'referrer_id'     => $refUser->user_id,
            'user_type'       => 'Player',
            'enabled'         => true,
        ];
        // Drop the user details with the remote BETTING API
        $response = $this->registerPlayerRemotely($data);

        // If successful, save the user details into local database
        if ($response['errorCode'] === "SUCCESS"){
            $user =  User::create($data);
            Wallet::create([
                'user_id'  => $user->id,
                'balance'  => 0,
                'bonus'    => 0
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
        if ($data['user_type'] === 'player'){
            $response = $this->initiatePlayerToken($data);
        }else{
            return $this->errorResponse([
                'errorCode' => 'AUTHENTICATION_ERROR',
                'message'   => 'This account is not a player'
            ], 422);
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
            'agency'    => $request->username
        ];

       if ($user->user_type !== 'agency'){
           return $this->errorResponse([
               'errorCode' => 'AUTHENTICATION_ERROR',
               'message'   => 'This account is not an agency'
           ], 422);
       }
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
        $response = $this->initiateAdminToken($data);

        $responseData = [
            'user' => $user,
            'token' => $response
        ];

        //4. Return response to user along with cookie for authentication
        return $this->successResponse($responseData,200);
    }

}
