<?php


namespace App\Traits\JsonBuilders;


use App\Traits\AuthHelpers\RemoteAPIServerCredentials;

trait SpecialSportBookJsonRequestBuilder
{
    use RemoteAPIServerCredentials;

    /*
     * SportBook Json Data formatters
     */
    private function buildFetchSpecialSportsData($data){
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

    private function buildFetchSpecialSportGroupsData($data){
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

    private function buildFetchSpecialGroupLeaguesData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "group_id"  => $data['group_id'],
                "skin"       => $this->ABX_API_SKIN,
                "lang"       => "EN",
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildFetchSpecialLeagueEventsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "champ_id"  => $data['champ_id'],
                'group_id'  => $data['group_id'],
                "skin"       => $this->ABX_API_SKIN,
                "lang"       => "EN",
                "timezone"   => "Africa/Lagos",
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildSpecialOddListsData($data){
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
