<?php

namespace App\Http\Controllers;

use App\Models\AgencyBet;
use App\Models\Token;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerCouponActions;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use APIResponse;
    use AuthUserManager;
    use RemoteAPIServerCouponActions;
    public $user;
    public $token;

    public function __construct(Request $request)
    {
        $this->user  = $this->getCurrentUser($request);
        if ($this->user){
            $this->token = Token::where('username', $this->user->username)->first()->token;
        }
    }

    public function defaultAgencyCoupon(){
        $response = $this->getAgencyDefaultCouponBonus();
        return $this->successResponse($response, 200);

    }

    public function userCouponBonus(Request $request){

        $data = [
            'username'  => $this->user->username,
            'user_type' => $this->user->user_type,
        ];
        if (ucwords($data['user_type']) === 'Player'){
            $token = $this->initiatePlayerToken($data);
        }

        if (ucwords($data['user_type']) === 'Agency'){
            $token = $this->initiateAgencyToken($data);
        }

        $data['token'] = $token;
        $response = $this->getUserCouponBonus($data);
        return $this->successResponse($response, 200);
    }



    public function playerPlayCouponSingle(Request $request){
        $request->validate([
            'amount'       => 'required|numeric',
            'search_code'  => 'required|string',
            'sign_key'     => 'required|string',
            'rank'         => 'required|numeric',
        ]);

        $data['amount']       = $request->amount;
        $data['search_code']  = $request->search_code;
        $data['sign_key']     = $request->sign_key;
        $data['rank']         = $request->rank;
        $data['token']        = $this->token;
        $response = $this->playerPlayCouponSingleSetup($data);
        return $this->successResponse($response, 200);
    }

    public function AgencyPlayCouponSingle(Request $request){
        $request->validate([
            'amount'              => 'required|numeric',
            'search_code'         => 'required|string',
            'sign_key'            => 'required|string',
            'rank'                => 'required|numeric',
            'player_username'     => 'required',
        ]);

        if(!User::where('username', $request->player_username)->where('user_type', 'player')->first()){
            return $this->errorResponse(array([
                'status' => 'Invalid request',
                'message'   => "Player with the username ' $request->player_username ' not found"
            ]), 404);
        }

        //Check agent wallet before placing bet
        $wallet = Wallet::where('user_id', $this->user->id)->first();
        if ($wallet->balance === 0 && $wallet->bonus === 0){
            return $this->errorResponse(array([
                'status' => 'Insufficient fund',
                'message'   => "You have 0 fund in your agency wallet"
            ]), 422);
        }

        if ($wallet->balance < $request->amount && $wallet->bonus < $request->amount){
            return $this->errorResponse(array([
                'status' => 'Insufficient fund',
                'message'   => "Your wallet balance is insufficient for the bet"
            ]), 422);
        }


        $data['username']     = $request->player_username;
        $data['amount']       = $request->amount;
        $data['search_code']  = $request->search_code;
        $data['sign_key']     = $request->sign_key;
        $data['rank']         = $request->rank;
        $data['token']        = $this->token;
        $response             = $this->agencyPlayCouponSingleSetup($data);

        //Debit agency balance or bonus if request is successful
        if ($response["errorCode"] === "SUCCESS"){
            // Check if they have enough cash in their bonus wallet
            if ($wallet->bonus >= $request->amount){
                $wallet->bonus = $wallet->bonus - $request->amount;
            } else {
                //Debit from their balance
                $wallet->balance = $wallet->balance - $request->amount;
            }
            $wallet->save();

            // Lastly Insert the betting record to database
            AgencyBet::create([
                'user_id'         => $this->user->username,
                'player_username' => $data['username'],
                'bet_type'        => 'single',
                'amount'          => $request->amount,
            ]);
        }

        return $this->successResponse($response, 200);
    }





    public function playCouponMultiple(Request $request){
        $request->validate([
            'amount'       => 'required|numeric',
            'search_code'  => 'required|string',
            'sign_key'     => 'required|string',
            'rank'         => 'required|string|max:255|unique:users,username',
        ]);

        $data['username']     = $this->user->username;
        $data['amount']       = $request->amount;
        $data['search_code']  = $request->search_code;
        $data['sign_key']     = $request->sign_key;
        $data['rank']         = $request->rank;
        $data['token']        = $this->token;
        $response = $this->playCouponMultipleSetup($data);
        return $this->successResponse($response, 200);
    }




}

