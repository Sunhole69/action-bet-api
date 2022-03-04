<?php


namespace App\Traits\JsonBuilders;


use App\Traits\AuthHelpers\RemoteAPIServerCredentials;

trait AuthJsonRequestBuilder
{
    use RemoteAPIServerCredentials;

    /*
     * Login Json Data formatters
     */
    private function buildUserLoginData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                'username' => $data['username'],
                'password' => $this->ABX_API_BO_PASSWORD,
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildAgentLoginData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                'username' => $data['agency'],
                'password' => $this->ABX_API_BO_PASSWORD,
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildPlayerLoginData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'data' => [
                'username' => $data['username'],
                'password' => $data['password'],
            ]
        ];

        return json_encode($dataBuild);
    }


    /*
     * Registration Json Data formatters
     */
    private function buildAffiliateRegistrationData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => 'create_user',
            'token'  => $data['admin_token'],
            'data' => [
                "parent" => $this->ABX_API_BO_USERNAME,
                "role" => 'agency',
                "username" => $data['username'],
                "password" => $this->ABX_API_BO_PASSWORD,
                "firstname" => $data['firstname'],
                "lastname" => $data['lastname'],
                "phone" => $data['phone'],
                "email" => $data['email'],
                "enabled" => $data['enabled']
            ]
        ];


        return json_encode($dataBuild);
    }

    private function buildPlayerRegistrationData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['agency_token'],
            'data' => [
                "parent" => $this->ABX_API_AGENT_USERNAME,
                "role" => strtolower($data['user_type']),
                "username" => $data['username'],
                "password" => $this->ABX_API_BO_PASSWORD,
                "firstname" => $data['firstname'],
                "lastname" => $data['lastname'],
                "phone" => $data['phone'],
                "email" => $data['email'],
                "enabled" => $data['enabled']
            ]
        ];

        return json_encode($dataBuild);
    }

}
