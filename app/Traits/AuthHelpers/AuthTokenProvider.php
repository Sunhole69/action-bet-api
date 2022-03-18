<?php


namespace App\Traits\AuthHelpers;


use App\Models\Affiliate;
use App\Models\Token;
use App\Traits\JsonBuilders\AuthJsonRequestBuilder;
use App\Traits\RequestHelpers\HttpResource;
use Carbon\Carbon;
use Carbon\Traits\Date;

trait AuthTokenProvider
{
    private string $url = 'https://skin.bettingadmin.com/api/sportbetting/';
    use HttpResource;
    use AuthJsonRequestBuilder;

    public string $currentUserValidToken;


    private function getChildRemoteToken($data){
        $jsonData = $this->buildPlayerLoginData($data);
        return $this->send($this->url, $jsonData);
    }

    public function fetchToken($data)
    {
        // Check if user has a token
        $localToken = Token::where('username', $data['username'])->where('user_type', 'player')->first();
        if($localToken){
            // Check if the token is still valid
            if ($this->checkTokenValidity($localToken)){
                $this->currentUserValidToken = $localToken->token;
            }
            // Retrieve new token from the remote server
            return $this->getChildRemoteToken($data);

        }else{
            // If the user doesn't have a token;
            return $this->getChildRemoteToken($data);
        }

    }


    /*
     * User Authentication methods
     */
    private function initiateAdminToken($data){
        $data['user_type'] = 'admin';
        $data['username']  = $this->ABX_API_BO_USERNAME;
        $data['action'] = 'authenticate';
        return $this->fetchUserToken($data);
    }

    private function initiateAgencyToken($data){
        $data['user_type'] = 'agency';
        $data['action'] = 'authenticate';
        return $this->fetchAgencyToken($data);
    }

    private function initiatePlayerToken($data){
        $data['user_type'] = 'player';
        $data['action'] = 'authenticate';
        return $this->fetchUserToken($data);
    }


    public function fetchUserToken($data)
    {
        // Check if user has a token
        $localToken = Token::where('username', $data['username'])->first();
        if($localToken){
            // Check if the token is still valid
            if ($this->checkTokenValidity($localToken)){
               return $localToken->token;
            }
            // Retrieve new token from the remote server
            $remoteToken =  $this->getUserRemoteToken($data);

            //Update the old token record
            $localToken->update([
                'token' => $remoteToken['data']['token'],
                'created_at' => Carbon::now(),
            ]);
            // If the user token;
            return $remoteToken['data']['token'];
        }else{
            // Retrieve new token from the remote server
            $remoteToken =  $this->getUserRemoteToken($data);
            Token::create([
                'token' => $remoteToken['data']['token'],
                'user_type' => $data['user_type'],
                'username' => $data['username']
            ]);
            // If the user token;
            return $remoteToken['data']['token'];
        }

    }

    public function fetchAgencyToken($data)
    {
        // Check if user has a token
        $localToken = Token::where('username', $data['agency'])->first();
        if($localToken){
            // Check if the token is still valid
            if ($this->checkTokenValidity($localToken)){
                return $localToken->token;
            }
            // Retrieve new token from the remote server
            $remoteToken =  $this->getAgencyRemoteToken($data);

            //Update the old token record
            $localToken->update([
                'token' => $remoteToken['data']['token'],
                'created_at' => Carbon::now(),
            ]);
            // If the user token;
            return $remoteToken['data']['token'];
        }else{
            // Retrieve new token from the remote server
            $remoteToken =  $this->getAgencyRemoteToken($data);
            Token::create([
                'token' => $remoteToken['data']['token'],
                'user_type' => $data['user_type'],
                'username' => $data['agency']
            ]);
            // If the user token;
            return $remoteToken['data']['token'];
        }

    }

    private function getUserRemoteToken($data){
        $jsonData = $this->buildUserLoginData($data);

        return $this->send($this->url, $jsonData);
    }


    /*
     * Admin Authentication methods
     */
    private function initiateAgentToken($data){
        $data['action'] = 'authenticate';
        return $this->fetchAgentToken($data);
    }

    public function fetchAgentToken($data)
    {
        // Check if user has a token
        $localToken = Token::where('username', $data['username'])->first();
        if($localToken){
            // Check if the token is still valid
            if ($this->checkTokenValidity($localToken)){
                return $localToken->token;
            }
            // Retrieve new token from the remote server
            $remoteToken =  $this->getAgencyRemoteToken($data);

            //Update the old token record
            $localToken->update([
                'token' => $remoteToken['data']['token'],
                'created_at' => Carbon::now(),
            ]);
            // If the user token;
            return $remoteToken['data']['token'];
        }else{
            // Retrieve new token from the remote server
            $remoteToken =  $this->getAgencyRemoteToken($data);
            Token::create([
                'token' => $remoteToken['data']['token'],
                'user_type' => 'Agent',
                'username' => $this->ABX_API_BO_USERNAME
            ]);
            // If the user token;
            return $remoteToken['data']['token'];
        }

    }

    private function getAgencyRemoteToken($data){
        $jsonData = $this->buildAgentLoginData($data);
        return $this->send($this->url, $jsonData);
    }


    /*
     * Common authentication utility methods
     */
    private function checkTokenValidity($token){
        // If local token is still valid i.e not expired due to time factor(8min max)
        if (Carbon::now()->subMinutes(8) > $token->updated_at){
            return false;
        }
        $token->updated_at = Carbon::now()->addMinutes(8);
        $token->save();
        return true;
    }


}
