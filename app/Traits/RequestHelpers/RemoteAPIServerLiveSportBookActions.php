<?php


namespace App\Traits\RequestHelpers;


use App\Traits\AuthHelpers\AuthTokenProvider;
use App\Traits\JsonBuilders\LiveSportBookJsonRequestBuilder;

trait RemoteAPIServerLiveSportBookActions
{
    use AuthTokenProvider;
    use LiveSportBookJsonRequestBuilder;


    private function initiateFetchAllLiveEventsList(){
        $data['action'] = "live_event_list";
        $jsonData = $this->buildFetchLiveEventsData($data);

        return $this->send($this->url, $jsonData);
    }

    private function initiateFetchAllLiveOddStructureList(){
        $data['action'] = "live_odds_structure";
        $jsonData = $this->buildFetchLiveEventsData($data);

        return $this->send($this->url, $jsonData);
    }


}
