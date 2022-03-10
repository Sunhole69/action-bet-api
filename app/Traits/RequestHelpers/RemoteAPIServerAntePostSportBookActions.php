<?php


namespace App\Traits\RequestHelpers;


use App\Traits\AuthHelpers\AuthTokenProvider;
use App\Traits\JsonBuilders\AntePostSportBookJsonRequestBuilder;

trait RemoteAPIServerAntePostSportBookActions
{
    use AuthTokenProvider;
    use AntePostSportBookJsonRequestBuilder;

    /*
     * Sport Book record
     */
    private function initiateFetchAllAntePostSports(){
        // Get the admin token and send along the request

        $data['action'] = "antepost_sport_list";
        $jsonData = $this->buildFetchAntePostSportsData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllAntePostSportGroups($sport_id){
        // Get the admin token and send along the request

        $data['action'] = "antepost_group_list";
        $data['sport_id'] = $sport_id;
        $jsonData = $this->buildFetchAntePostSportGroupsData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllAntePostGroupEvents($group_id){
        // Get the admin token and send along the request

        $data['action'] = "antepost_event_list";
        $data['group_id'] = $group_id;
        $jsonData = $this->buildFetchAntePostGroupEventsData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllAntePostOddLists($search_code){
        // Get the admin token and send along the request

        $data['action'] = "antepost_odd_list";
        $data['search_code'] = $search_code;
        $jsonData = $this->buildFetchAnteOddListsData($data);

        return $this->send($this->url, $jsonData);
    }

}
