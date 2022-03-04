<?php


namespace App\Traits\JsonBuilders;


use App\Traits\AuthHelpers\RemoteAPIServerCredentials;

trait SportBookJsonRequestBuilder
{
    use RemoteAPIServerCredentials;

    /*
     * Login Json Data formatters
     */
    private function buildFetchPrematchSportsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "skin"       => $this->ABX_API_SKIN,
                "timezone"   => "Africa/Lagos",
                "lang"       => "EN",
                "datefilter" => "DEFAULT"
            ]
        ];

        return json_encode($dataBuild);
    }



}
