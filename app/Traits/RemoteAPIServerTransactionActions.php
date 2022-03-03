<?php


namespace App\Traits;


trait RemoteAPIServerTransactionActions
{
    use AuthTokenProvider;
    use TransactionJsonRequestBuilder;

    /*
     * Player Authentication methods
     */
    private function depositRemotely($data){
        // Get the admin token and send along the request
        if ($data['user_type'] === 'Player') {
            $data['token'] = $this->initiatePlayerToken($data);
        }
        if ($data['user_type'] === 'Agency') {
            $data['token'] = $this->initiateAgencyToken($data);
        }

        $data['action'] = 'deposit_money';
        $jsonData = $this->buildUserDepositData($data);
        return $this->send($this->url, $jsonData);
    }

}
