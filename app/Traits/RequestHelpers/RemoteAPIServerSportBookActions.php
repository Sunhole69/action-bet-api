<?php


namespace App\Traits\RequestHelpers;


use App\Traits\AuthHelpers\AuthTokenProvider;
use App\Traits\JsonBuilders\SportBookJsonRequestBuilder;
use App\Traits\JsonBuilders\TransactionJsonRequestBuilder;

trait RemoteAPIServerSportBookActions
{
    use AuthTokenProvider;
    use SportBookJsonRequestBuilder;

    /*
     * Player Authentication methods
     */
    private function initiateFetchAllSports(){
        // Get the admin token and send along the request

        $data['action'] = "prematch_sport_list";
        $jsonData = $this->buildFetchPrematchSportsData($data);
        return $this->send($this->url, $jsonData);
    }

}
