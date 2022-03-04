<?php


namespace App\Traits;


trait TransactionJsonRequestBuilder
{
    use RemoteAPIServerCredentials;

    /*
     * Login Json Data formatters
     */
    private function buildUserTransactionData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data' => [
                'amount' => $data['amount'],
                'note' => $data['note'],
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildUserExistingTransactionsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['token'],
            'data' => [
                'username'  => $data['username'],
                'amount' => $data['amount'],
                'note' => $data['note'],
            ]
        ];

        return json_encode($dataBuild);
    }


}
