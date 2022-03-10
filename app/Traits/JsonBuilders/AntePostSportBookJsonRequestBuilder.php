<?php


namespace App\Traits\JsonBuilders;


use App\Traits\AuthHelpers\RemoteAPIServerCredentials;

trait AntePostSportBookJsonRequestBuilder
{
    use RemoteAPIServerCredentials;

    /*
     * SportBook Json Data formatters
     */
    private function buildFetchAntePostSportsData($data){
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

    private function buildFetchAntePostSportGroupsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "sport_id"  => $data['sport_id'],
                "skin"       => $this->ABX_API_SKIN,
                "lang"       => "EN",
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildFetchAntePostGroupEventsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "group_id"  => $data['group_id'],
                "skin"       => $this->ABX_API_SKIN,
                "timezone"   => "Africa/Lagos",
                "lang"       => "EN",
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildFetchAnteOddListsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "search_code"  => $data['search_code'],
                "skin"       => $this->ABX_API_SKIN,
                "lang"       => "EN",
            ]
        ];
        return json_encode($dataBuild);
    }


}
