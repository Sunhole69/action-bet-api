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

    public function buildPlayCouponSingleData ($data) {
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
                'acceptableOddChanges' => true,
                'odds'                 => [
                    [
                        'search_code' => $data['search_code'],
                        'sign_key'    => $data['sign_key'],
                        'rank'        => $data['rank']
                    ]
                ]
            ]
        ];

        return json_encode($dataBuild);

    }

    public function buildPlayCouponMultipleData ($data) {
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data'   => [
                'username'             => $data['username'],
                'rechargeAndBet'       => true,
                'type'                 => 'multiple',
                'amount'               => $data['amount'],
                'acceptableOddChanges' => true,
                'odds'                 => $this->multipleOddsStructure($data['games'])
            ]
        ];

        return json_encode($dataBuild);
    }


    public function multipleOddsStructure($games){
        $odds = [];
        foreach ($games as $game){
            array_push($odds, [
                'search_code' => $game['search_code'],
                'sign_key'    => $game['sign_key'],
                'rank'        => $game['rank']
            ]);
        }

        return $odds;
    }
}
