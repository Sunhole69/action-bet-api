<?php


namespace App\Traits\RequestHelpers;


use App\Traits\AuthHelpers\AuthTokenProvider;
use App\Traits\JsonBuilders\SpecialSportBookJsonRequestBuilder;

trait RemoteAPIServerSpecialSportBookActions
{
    use AuthTokenProvider;
    use SpecialSportBookJsonRequestBuilder;

    /*
     * Sport Book record
     */
    private function initiateFetchAllSpecialSports(){
        // Get the admin token and send along the request

        $data['action'] = "special_sport_list";
        $jsonData = $this->buildFetchSpecialSportsData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllSpecialSportGroups($sport_id){
        // Get the admin token and send along the request

        $data['action'] = "special_group_list";
        $data['sport_id'] = $sport_id;
        $jsonData = $this->buildFetchSpecialSportGroupsData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllSpecialGroupLeagues($group_id){
        // Get the admin token and send along the request

        $data['action'] = "special_champ_list";
        $data['group_id'] = $group_id;
        $jsonData = $this->buildFetchSpecialGroupLeaguesData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllSpecialLeagueEvents($league){
        // Get the admin token and send along the request

        $data['action'] = "special_event_list";
        $data['champ_id'] = $league->id;
        $data['group_id'] = $league->group_id;
        $jsonData = $this->buildFetchSpecialLeagueEventsData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllSpecialOddLists($search_code){
        // Get the admin token and send along the request

        $data['action'] = "special_odd_list";
        $data['search_code'] = $search_code;
        $jsonData = $this->buildSpecialOddListsData($data);

        return $this->send($this->url, $jsonData);
    }


}
