<?php


namespace App\Traits\RequestHelpers;


use App\Traits\AuthHelpers\AuthTokenProvider;
use App\Traits\JsonBuilders\TransactionJsonRequestBuilder;

trait RemoteAPIServerTransactionActions
{
    use AuthTokenProvider;
    use TransactionJsonRequestBuilder;

    /*
     * Player Authentication methods
     */
    private function initiateRemoteTransaction($data){
        // Get the admin token and send along the request
        if ($data['user_type'] === 'Player') {
            $data['token'] = $this->initiatePlayerToken($data);
        }
        if ($data['user_type'] === 'Agency') {
            $data['token'] = $this->initiateAgencyToken($data);
        }

        $jsonData = $this->buildUserTransactionData($data);
        return $this->send($this->url, $jsonData);
    }

}
