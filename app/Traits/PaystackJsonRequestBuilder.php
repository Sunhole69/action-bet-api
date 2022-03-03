<?php


namespace App\Traits;


trait PaystackJsonRequestBuilder {


    public function buildChargeRequestData($data){
        $dataBuild = [
            "email" => $data['email'],
            "amount" => $data['amount'],
        ];

        return json_encode($dataBuild);
    }
}
