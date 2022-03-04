<?php


namespace App\Traits\JsonBuilders;


use App\Traits\AuthHelpers\RemoteAPIServerCredentials;

trait UserJsonRequestBuilder
{
    use RemoteAPIServerCredentials;

    private function buildFetchUserDetailsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['admin_token'],
            'data' => [
                'username' => $data['username'],
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildFetchAllAgentPlayersData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['admin_token'],
            'data' => [
                'parent' => $data['parent'],
                'page'   => $data['page']
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildUpdateUserDetailsData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['admin_token'],
            'data' => [
                'username' => $data['username'],
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildUpdateUserPasswordData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['admin_token'],
            'data' => [
                'username' => $data['username'],
                'newpassword' => $data['password']
            ]
        ];

        return json_encode($dataBuild);
    }

    private function buildUpdateUserStatusData($data){
        $dataBuild = [
            'partner' => $this->ABX_API_PARTNER,
            'secretkey' => $this->ABX_API_SECRETE_KEY,
            'action' => $data['action'],
            'token'  => $data['admin_token'],
            'data' => [
                'username' => $data['username'],
                'enabled' => $data['status']
            ]
        ];

        return json_encode($dataBuild);
    }

}
