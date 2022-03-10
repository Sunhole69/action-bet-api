<?php


namespace App\Traits\JsonBuilders;


use App\Traits\AuthHelpers\RemoteAPIServerCredentials;

trait LiveSportBookJsonRequestBuilder
{
    use RemoteAPIServerCredentials;

    /*
     * SportBook Json Data formatters
     */
    private function buildFetchLiveEventsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "skin"       => $this->ABX_API_SKIN,
                "lang"       => "EN",
            ]
        ];

        return json_encode($dataBuild);
    }


}
