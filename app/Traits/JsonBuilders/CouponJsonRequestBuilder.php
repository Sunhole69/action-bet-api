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


    public function buildPlayerGetCouponsData ($data) {
        $dateFrom = $data['dateFrom'];
        $dateTo = $data['dateTo'];

        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data'   => [
                'username'                => $data['username'],
                'timezone'                => $data['timezone'],
                'dateFrom'               => "$dateFrom 00:00:00",
                'dateTo'                 => "$dateTo 23:59:59",
                'page'                    => $data['page'],
            ]
        ];

        return json_encode($dataBuild);
    }


    public function buildPlayerShowCouponsData ($data) {

        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data'   => [
                'username'                => $data['username'],
                'timezone'                => $data['timezone'],
                'lang'                    => $data['lang'],
                'coupon_id'               => $data['coupon_id'],
            ]
        ];
        return json_encode($dataBuild);
    }

    public function buildPlayerCouponsCashoutList ($data) {

        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
        ];
        return json_encode($dataBuild);
    }


    public function buildPlayerDoCouponCashout($data) {

        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data'   => [
                'coupon_id'      => $data['coupon_id'],
                'cashout_amount' => $data['cashout_amount']
            ]
        ];
        return json_encode($dataBuild);
    }


}
