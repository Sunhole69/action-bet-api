<?php


namespace App\Traits\JsonBuilders;


use App\Traits\AuthHelpers\RemoteAPIServerCredentials;

trait PrematchSportBookJsonRequestBuilder
{
    use RemoteAPIServerCredentials;

    /*
     * SportBook Json Data formatters
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

    private function buildFetchPrematchSportGroupsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "sport_id"  => $data['sport_id'],
                "skin"       => $this->ABX_API_SKIN,
                "timezone"   => "Africa/Lagos",
                "lang"       => "EN",
                "datefilter" => "DEFAULT"
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildFetchPrematchGroupLeaguesData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "group_id"  => $data['group_id'],
                "skin"       => $this->ABX_API_SKIN,
                "timezone"   => "Africa/Lagos",
                "lang"       => "EN",
                "datefilter" => "DEFAULT"
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildFetchPrematchLeagueEventsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                "champ_id"  => $data['champ_id'],
                "skin"       => $this->ABX_API_SKIN,
                "timezone"   => "Africa/Lagos",
                "lang"       => "EN",
                "datefilter" => "DEFAULT"
            ]
        ];

        return json_encode($dataBuild);
    }


}
