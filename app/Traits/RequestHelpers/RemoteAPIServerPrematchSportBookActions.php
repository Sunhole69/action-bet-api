<?php


namespace App\Traits\RequestHelpers;


use App\Traits\AuthHelpers\AuthTokenProvider;
use App\Traits\JsonBuilders\PrematchSportBookJsonRequestBuilder;
use App\Traits\JsonBuilders\TransactionJsonRequestBuilder;

trait RemoteAPIServerPrematchSportBookActions
{
    use AuthTokenProvider;
    use PrematchSportBookJsonRequestBuilder;

    /*
     * Sport Book record
     */
    private function initiateFetchAllPrematchSports(){
        // Get the admin token and send along the request

        $data['action'] = "prematch_sport_list";
        $jsonData = $this->buildFetchPrematchSportsData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllPrematchSportGroups($sport_id){
        // Get the admin token and send along the request

        $data['action'] = "prematch_group_list";
        $data['sport_id'] = $sport_id;
        $jsonData = $this->buildFetchPrematchSportGroupsData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllPrematchGroupLeagues($group_id){
        // Get the admin token and send along the request

        $data['action'] = "prematch_champ_list";
        $data['group_id'] = $group_id;
        $jsonData = $this->buildFetchPrematchGroupLeaguesData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllPrematchLeagueEvents($league_id){
        // Get the admin token and send along the request

        $data['action'] = "prematch_event_list";
        $data['champ_id'] = $league_id;
        $jsonData = $this->buildFetchPrematchLeagueEventsData($data);

        return $this->send($this->url, $jsonData);
    }

}
