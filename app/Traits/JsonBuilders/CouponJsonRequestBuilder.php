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

    public function buildPlayerPlayCouponSingleData ($data) {
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data'   => [
//                'username'             => $data['username'],
//                'rechargeAndBet'       => true,
                'type'                 => 'single',
                'amount'               => $data['amount'],
                'acceptOddChanges' => true,
                'odds'                 => [
                    [
                        'search_code' => $data['search_code'],
                        'sign'        => $data['sign_key'],
                        'rank'        => $data['rank']
                    ]
                ]
            ]
        ];

        return json_encode($dataBuild);

    }

    public function buildAgencyPlayCouponSingleData ($data) {
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data'   => [
                'username'             => $data['username'],
                'rechargeAndBet'       => true,
                'type'                 => 'single',
                'amount'               => $data['amount'],
                'acceptOddChanges'     => true,
                'odds'                 => [
                    [
                        'search_code' => $data['search_code'],
                        'sign'        => $data['sign_key'],
                        'rank'        => $data['rank']
                    ]
                ]
            ]
        ];

        return json_encode($dataBuild);

    }


    public function buildPlayerPlayCouponMultipleAndSplitData ($data) {
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data'   => [
                'type'                 => $data['type'],
                'amount'               => $data['amount'],
                'acceptOddChanges' => true,
                'odds'                 => $data['events']
            ]
        ];

        return json_encode($dataBuild);
    }


    public function buildPlayerPlayCouponCombinedData ($data) {
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data'   => [
                'type'                    => $data['type'],
                'amount'                  => $data['amount'],
                'acceptOddChanges'        => true,
                'odds'                    => $data['events'],
                'amounts'                 => $data['amounts']
            ]
        ];

        return json_encode($dataBuild);
    }

}
