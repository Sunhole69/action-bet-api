<?php

namespace App\Http\Controllers;

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

    public function __construct(Request $request)
    {
        $this->user = $this->getCurrentUser($request);
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


}

