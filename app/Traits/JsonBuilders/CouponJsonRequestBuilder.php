<?php


namespace App\Traits\JsonBuilders;


use App\Traits\AuthHelpers\RemoteAPIServerCredentials;
use App\Traits\RequestHelpers\APIResponse;

trait CouponJsonRequestBuilder
{
    use RemoteAPIServerCredentials;
    public function buildAgencyDefaultCouponData ($data) {
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "skin" => $this->ABX_API_SKIN,
            ]
        ];

        return json_encode($dataBuild);

    }

    public function buildUserCouponBonusData ($data) {
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token']
        ];

        return json_encode($dataBuild);

    }


}
