<?php

namespace App\Http\Controllers;

use App\Models\Token;
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
        $this->token = Token::where('username', $this->user->username)->first()->token;
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

    public function playCouponSingle(Request $request){
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
        $response = $this->playCouponSingleSetup($data);
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

