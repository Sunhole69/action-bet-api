<?php

namespace App\Http\Controllers;

use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerCouponActions;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use APIResponse;
    use RemoteAPIServerCouponActions;
    public function defaultAgencyCoupon(){
        $response = $this->getAgencyDefaultCouponBonus();
        return $this->successResponse($response, 200);

    }
}
