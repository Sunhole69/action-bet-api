<?php

namespace App\Http\Controllers;

use App\Mail\PasswordUpdateMail;
use App\Mail\ResetPasswordMail;
use App\Models\Agency;
use App\Models\PadiWinUser;
use App\Models\PasswordReset;
use App\Models\Player;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerPlayerActions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use APIResponse;
    use RemoteAPIServerPlayerActions;

    public function registerAffiliate(Request $request){
        //1. Validating user inputs
        $request->validate([
            'firstname'         => 'required|string|max:255',
            'lastname'          => 'required|string|max:255',
            'gender'            => 'required|string|max:255',
            'state'             => 'required|string|max:255',
            'personal_address'  => 'required|string|max:255',
            'shop_address'      => 'required|string|max:255',
            'date_of_birth'     => 'nullable|string|max:255',
            'username'          => 'required|string|max:255|unique:users,username',
            'phone'             => 'required|unique:users,phone',
            'email'             => 'required|string|unique:users,email',
            'password'          => 'required|string|confirmed'
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
            $user = User::create($regData);

            // Create an agency account for the agent
            Agency::create([
               'user_id'            => $user->id,
               'shop_address'       => $request->shop_address,
               'personal_address'   => $request->personal_address,
               'gender'             => $request->gender,
               'state'              => $request->state,
               'date_of_birth'      => $request->date_of_birth,
               'approved'           => true,
               'active'             => true,
            ]);

        }

        // If successful, save the user details into local database
        if ($response['errorCode'] === "SUCCESS"){
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
            'username'  => 'required|string|max:255|unique:users,username',
            'phone'     => 'required|string|unique:users,phone',
            'email'     => 'required|string|unique:users,email',
            'password'  => 'required|string|confirmed'
        ]);

//        // Check if agency exist
//        if (!User::where('username', $request->agency)->first()){
//            return $this->errorResponse([
//                'errorCode' => "AGENCY_ERROR",
//                'message' => 'Agency doesn\'t exist'
//            ], 404);
//        }

        // Create the data needed for the remote BETTING API
        $data = [
            'firstname'       => $request->firstname,
            'lastname'        => $request->lastname,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'username'        => $request->username,
            'password'        => $request->password,
            'agency'          => 'telvida',
            'user_type'       => 'Player',
            'enabled'         => true,
        ];
        // Drop the user details with the remote BETTING API
        $response = $this->registerPlayerRemotely($data);


        // If successful, save the user details into local database
        if ($response['errorCode'] === "SUCCESS"){
         $data['verification_token'] = Str::random(50);
         $user =  User::create($data);
          Wallet::create([
              'user_id' => $user->id,
              'balance'  => 0,
              'bonus'   => 0
          ]);

          if ($data['user_type'] == 'Player'){
              Player::create([
                  'user_id' =>  $user->id
              ]);
          }
        }

        // Send verification link to users

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
            $data['verification_token'] = Str::random(50);
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
        $user = User::where('username', $fields['username'])->with('player')->with('wallet')->with('transactions')->first();

        //3. verify user password, if authentication fails
        if(!$user || !Hash::check($fields['password'], $user->password)) {
            $response = [
                'status' => 'AUTHENTICATION_ERROR',
                'message' => 'Invalid username or password'
            ];
            return $this->errorResponse($response, 401);
        }

//        if (!$user->email_verified_at){
//            return $this->errorResponse([
//                'errorCode' => 'AUTHENTICATION_ERROR',
//                'message'   => 'Your account is unverified yet'
//            ], 401);
//        }

        // Create the data needed for the remote BETTING API
        $data = [
            'username'  => $request->username,
            'user_type' => $user->user_type
        ];

        //Determine the authentication path through the user_type
        if ($data['user_type'] === 'player'){
            // Generate LCTECH token for user
//            $response = $this->initiatePlayerToken($data);
        }else{
            return $this->errorResponse([
                'errorCode' => 'AUTHENTICATION_ERROR',
                'message'   => 'This account is not a player'
            ], 422);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;


        // Generate sanctum auth token for user
       $responseData = [
           'errorCode'    => 'SUCCESS',
           'role'         => ucwords($data['user_type']),
           'user'         => $user,
           'token'        => $token,
       ];


        //4. Return response to user along with cookie for authentication
      return  $this->successResponseWithCookie($responseData, [
            'name'  => 'token',
            'value' => $token
        ], (5 * 365 * 24 * 60 * 60),200);
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

//        if (!$user->email_verified_at){
//            return $this->errorResponse([
//                'errorCode' => 'AUTHENTICATION_ERROR',
//                'message'   => 'Your account is unverified yet'
//            ], 401);
//        }

        $response = $this->initiateAgencyToken($data);
        $token = $user->createToken('myapptoken')->plainTextToken;

        $responseData = [
            'user' => $user,
            'token' => $token
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

    public function verifyUser(Request $request, $token){
        $user = User::where('verification_token', $token)->first();
        if (!$user){
            return $this->errorResponse([
                'errorCode' => "VERIFICATION_ERROR",
                'message' => 'Invalid verification token'
            ], 404);
        }

        // Update the user registration records
        $user->verification_token = null;
        $user->email_verified_at = Carbon::now();
        $user->save();


        return $this->errorResponse([
            'errorCode' => "SUCCESS",
            'message' => 'Email verified successfully'
        ], 202);

    }

    public function resetPassword(Request $request){
        $request->validate([
           'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user){
            return $this->errorResponse([
                'errorCode' => "AUTHENTICATION_ERROR",
                'message' => 'Email not found'
            ], 404);
        }

        // Check if exist then update else create
        $reset = PasswordReset::where('email', $request->email)->first();
        $token = Str::random(50);
        if ($reset){
            $reset->token = $token;
            $reset->expires_at = Carbon::now()->addMinutes(10);
            $reset->save();
        }else{
            PasswordReset::create([
                'email'        => $request->email,
                'token'        => $token,
                'expires_at'   => Carbon::now()->addMinutes(10)
            ]);
        }

        // Mail the user concerning the update
        Mail::to($user->email)->send(new ResetPasswordMail($user, $token));

        // Send r
        return $this->errorResponse([
            'errorCode' => "SUCCESS",
            'message' => 'Reset link has been sent to you email address'
        ], 202);


    }

    public function chooseNewPassword(Request $request, $token){
        $request->validate([
            'password'  => 'required|string|confirmed'
        ]);

        error_log($token);
        $token = PasswordReset::where('token', $token)->first();
        if (!$token){
            return $this->errorResponse([
                'errorCode' => "VALIDATION_ERROR",
                'message' => 'Invalid reset token'
            ], 422);
        }

        $tokenDate = Carbon::parse($token->expires_at);
        // Check if it has not expired
        if ($tokenDate <= Carbon::now()){
            // Delete the token and send response to user
            $token->delete();
            return $this->errorResponse([
                'errorCode' => "VALIDATION_ERROR",
                'message' => 'Invalid reset token'
            ], 422);
        }

        // Update user password
        $user = User::where('email', $token->email)->first();
        $user->password = $request->password;
        $user->save();

        // Delete token
        $token->delete();

        // Mail the user concerning the update
        Mail::to($user->email)->send(new PasswordUpdateMail($user));

        // Send r
        return $this->errorResponse([
            'errorCode' => "SUCCESS",
            'message' => 'Your password has been updated successfully'
        ], 202);


    }

    public function logout(){
        auth()->user()->tokens()->delete();
        // Set cookie to logged_out
        return  $this->successResponseWithCookie([
            'errorCode'    => 'SUCCESS',
            'message'      => 'User logged out'
        ], [
            'name'  => 'token',
            'value' => 'logged_out'
        ],  (30),200);
    }

    public function fetchCurrentUser(){
        auth()->user();

        // Set cookie to logged_out
        return  $this->successResponseWithCookie([
            'errorCode'    => 'SUCCESS',
            'message'      => 'User logged out'
        ], [
            'name'  => 'token',
            'value' => 'logged_out'
        ],  (30),200);
    }


    // Account update section
    public function updatePlayerProfile(Request $request){
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'phone'     => 'required|string',
            'email'     => 'required|string',

            'date_of_birth' => 'nullable|string',
            'gender'        => 'nullable|string|max:255',
            'country'       => 'nullable|string|max:255',
            'state'         => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'address'       => 'nullable|string|max:450',
        ]);


        //Check if the email is taken
        if (User::where('email', '=', $request->email)->where('id', '!=', auth()->user()->id)->first()){
            return $this->errorResponse([
                'errorCode'    => 'VALIDATION_ERROR',
                'message'      => 'the email has been taken'
            ], 422);
        }

        //Check if the phone number is taken
        if (User::where('phone', '=', $request->phone)->where('id', '!=', auth()->user()->id)->first()){
            return $this->errorResponse([
                'errorCode'    => 'VALIDATION_ERROR',
                'message'      => 'the phone has been taken'
            ], 422);
        }


        // Update the primary profile
        $user = User::find(auth()->user()->id);
            $player = Player::where('user_id', $user->id)->first();
            $user->fill($request->only([
                'firstname',
                'lastname',
                'username',
                'phone',
                'email',
            ]));
            $user->save();

            $player->fill($request->only([
                'date_of_birth',
                'gender',
                'country',
                'state',
                'city',
                'address',
            ]));
            $player->save();

            $user = User::with('player')->with('transactions')->with('wallet')->find($user->id);

            $token = $user->createToken('myapptoken')->plainTextToken;

            // Generate sanctum auth token for user
            $responseData = [
                'errorCode'    => 'SUCCESS',
                'user'         => $user,
                'token'        => $token,
            ];

            //4. Return response to user along with cookie for authentication
            return  $this->successResponseWithCookie($responseData, [
                'name'  => 'token',
                'value' => $token
            ], (5 * 365 * 24 * 60 * 60),200);

    }

    public function updateAgencyProfile(Request $request){
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username',
            'phone'     => 'required|string|unique:users,phone',
            'email'     => 'required|string|unique:users,email',


            'personal_address'  => 'required|string|max:255',
            'shop_address'      => 'required|string|max:255',
            'date_of_birth'     => 'nullable|string|max:255',
            'gender'            => 'nullable|string|max:255',
            'state'             => 'required|string|max:255',
        ]);

        // Update the primary profile
        $user = User::find(auth()->user()->id);
            $agency = Agency::where('user_id', $user->id)->first();

            $user->fill($request->only([
                'firstname',
                'lastname',
                'username',
                'phone',
                'email',
            ]));
            $user->save();

            $agency->fill($request->only([
                'date_of_birth',
                'gender',
                'state',
                'personal_address',
                'shop_address',
            ]));
            $agency->save();

            $user = User::with('agency')->with('transactions')->with('wallet')->find($user->id);

            $token = $user->createToken('myapptoken')->plainTextToken;

            // Generate sanctum auth token for user
            $responseData = [
                'errorCode'    => 'SUCCESS',
                'user'         => $user,
                'token'        => $token,
            ];

            //4. Return response to user along with cookie for authentication
            return  $this->successResponseWithCookie($responseData, [
                'name'  => 'token',
                'value' => $token
            ], (5 * 365 * 24 * 60 * 60),200);


    }

    public function changePassword(Request $request){
        $request->validate([
            'current_password'  => 'required|string',
            'password'          => 'required|string|confirmed'
        ]);

        $user = User::find(auth()->user()->id);
        // Confirm password
        if(!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse([
                'errorCode' => 'AUTHENTICATION_ERROR',
                'message'   => 'Incorrect password'
            ], 401);
        }

        $user->password = $request->password;
        $user->save();

        return  $this->successResponse([
            'errorCode'     =>  'SUCCESS',
            'message'       =>  'Password updated'
        ], 200);
    }



}
