<?php


namespace App\Traits\RequestHelpers;


use App\Traits\AuthHelpers\AuthTokenProvider;
use App\Traits\JsonBuilders\CouponJsonRequestBuilder;

trait RemoteAPIServerCouponActions
{
    use CouponJsonRequestBuilder;
    use AuthTokenProvider;

    private function getAgencyDefaultCouponBonus(){
        $data['action'] = 'default_coupon_bonus';
        $jsonData = $this->buildAgencyDefaultCouponData($data);
        return $this->send($this->url, $jsonData);
    }

    private function getUserCouponBonus($data){
        $data['action'] = 'user_coupon_bonus';
        $jsonData = $this->buildUserCouponBonusData($data);
        return $this->send($this->url, $jsonData);
    }
}
