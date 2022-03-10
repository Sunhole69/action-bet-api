<?php


namespace App\Traits\RequestHelpers;


use App\Traits\AuthHelpers\AuthTokenProvider;
use App\Traits\JsonBuilders\CouponJsonRequestBuilder;

trait RemoteAPIServerCouponActions
{
    use CouponJsonRequestBuilder;
    use AuthTokenProvider;
    /*
     * Player Authentication methods
     */
    private function getAgencyDefaultCouponBonus(){

        // Initiate the player account creation with the admin token
        $data['action'] = 'default_coupon_bonus';
        $jsonData = $this->buildAgencyDefaultCouponData($data);
        return $this->send($this->url, $jsonData);
    }
}
