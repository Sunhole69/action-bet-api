<?php


namespace App\Traits\RequestHelpers;


use App\Traits\AuthHelpers\AuthTokenProvider;
use App\Traits\JsonBuilders\CouponJsonRequestBuilder;

trait RemoteAPIServerCouponActions
{
    use CouponJsonRequestBuilder;

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





    private function playerPlayCouponSingleSetup($data){
        $data['action'] = 'play_coupon';
        $jsonData = $this->buildPlayerPlayCouponSingleData($data);
        return $this->send($this->url, $jsonData);
    }

    private function agencyPlayCouponSingleSetup($data){
        $data['action'] = 'play_coupon';
        $jsonData = $this->buildAgencyPlayCouponSingleData($data);
        return $this->send($this->url, $jsonData);
    }

    private function playCouponMultipleSetup($data){
        $data['action'] = 'play_coupon';
        $jsonData = $this->buildPlayCouponMultipleData($data);
        return $this->send($this->url, $jsonData);
    }

}
