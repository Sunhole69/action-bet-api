<?php


namespace App\Traits;


trait RemoteAPIServerPlayerActions
{
    use AuthTokenProvider;
    use UserJsonRequestBuilder;

    /*
     * Player Authentication methods
     */
    private function registerPlayerRemotely($data){
        // Get the admin token and send along the request
        $data['agency_token'] = $this->initiateAgencyToken($data);

        // Initiate the player account creation with the admin token
        $data['action'] = 'create_user';
        $jsonData = $this->buildPlayerRegistrationData($data);
        return $this->send($this->url, $jsonData);
    }

    private function fetchPlayerDetailsRemotely($data){
        // Get the admin token and send along the request
        $data['admin_token'] = $this->initiateAdminToken($data);

        // Initiate the player account creation with the admin token
        $data['action'] = 'user_details';
        $jsonData = $this->buildFetchUserDetailsData($data);
        return $this->send($this->url, $jsonData);
    }

    private function fetchAllAgentPlayersRemotely($data){
        // Get the admin token and send along the request
        $data['admin_token'] = $this->initiateAdminToken($data);

        // Initiate the player account creation with the admin token
        $data['action'] = 'get_children';
        $jsonData = $this->buildFetchAllAgentPlayersData($data);
        return $this->send($this->url, $jsonData);
    }


    private function updatePlayerDetailsRemotely($data){
        // Get the admin token and send along the request
        $data['admin_token'] = $this->initiateAdminToken($data);


        // Initiate the player account creation with the admin token
        $data['action'] = 'user_details';
        $jsonData = $this->buildFetchUserDetailsData($data);
        return $this->send($this->url, $jsonData);
    }

    private function updateUserPasswordRemotely($data){
        // Get the admin token and send along the request
        $data['admin_token'] = $this->initiateAgencyToken($data);


        // Initiate the player account creation with the admin token
        $data['action'] = 'change_password';
        $jsonData = $this->buildUpdateUserPasswordData($data);
        return $this->send($this->url, $jsonData);
    }

    private function updateUserStatusRemotely($data){
        // Get the admin token and send along the request
        $data['admin_token'] = $this->initiateAdminToken($data);

        // Initiate the player account creation with the admin token
        $data['action'] = 'update_user_status';
        $jsonData = $this->buildUpdateUserStatusData($data);
        return $this->send($this->url, $jsonData);
    }

    /*
     * Affiliate Authentication methods
     */
    private function registerAffiliateRemotely($data){
        //Get the admin token and send along the request
        $data['admin_token'] = $this->initiateAdminToken($data);

        $jsonData = $this->buildAffiliateRegistrationData($data);
        return $this->send($this->url, $jsonData);
    }
}
