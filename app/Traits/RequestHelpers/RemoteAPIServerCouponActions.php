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

    private function playCouponMultipleAndSplitSetup($data){
        $data['action'] = 'play_coupon';
        if ($data['type'] === 'multiple' || $data['type'] === 'split'){
            $jsonData = $this->buildPlayerPlayCouponMultipleAndSplitData($data);
        }
        return $this->send($this->url, $jsonData);
    }

    private function playerPlayCouponCombinedSetup($data){
        $data['action'] = 'play_coupon';
        $jsonData = $this->buildPlayerPlayCouponCombinedData($data);
        return $this->send($this->url, $jsonData);
    }

    private function playerGetCouponsSetup($data){
        $data['action'] = 'get_transactions';
        $jsonData = $this->buildPlayerGetCouponsData($data);
        return $this->send($this->url, $jsonData);
    }

    private function playerShowCouponsSetup($data){
        $data['action'] = 'coupon_info';
        $jsonData = $this->buildPlayerShowCouponsData($data);
        return $this->send($this->url, $jsonData);
    }

    private function playerCancelCouponSetup($data){
        $data['action'] = 'cancel_coupon';
        $jsonData = $this->buildPlayerCancelCoupon($data);
        return $this->send($this->url, $jsonData);
    }

    private function playerCouponsCashoutListSetup($data){
        $data['action'] = 'cashout_list';
        $jsonData = $this->buildPlayerCouponsCashoutList($data);
        return $this->send($this->url, $jsonData);
    }

    private function playerDoCouponsCashoutSetup($data){
        $data['action'] = 'do_cashout';
        $jsonData = $this->buildPlayerDoCouponCashout($data);
        return $this->send($this->url, $jsonData);
    }

}
