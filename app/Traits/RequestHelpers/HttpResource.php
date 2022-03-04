<?php


namespace App\Traits\RequestHelpers;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

trait HttpResource
{
    // For fetching data from an API
    public function fetch($url)
    {
        return Http::acceptJson()->get($url)->json();
    }

    // For fetching data from an API with headers
    public function fetchWithHeaders($url, array $headers)
    {
        return Http::acceptJson()->withHeaders($headers)->get($url)->json();
    }

    public function send($url, $data){
        $client = new Client([
            'headers' => ['Content-Type' => 'application/json']
        ]);
        $response = $client->post($url,
            ['body' => $data]
        );
        // json_decode converts response to an array
        return json_decode($response->getBody(), true);
    }

    public function sendPaymentCharge($url, $data){
        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer ".getenv('PAYSTACK_SECRET_KEY')
            ],
        ]);
        $response = $client->post($url,
            ['body' => $data]
        );
        // json_decode converts response to an array
        return json_decode($response->getBody(), true);
    }


    public function sendWithHeaders( $url, array $data, array $headers){
        return Http::acceptJson()->withHeaders($headers)->post($url, $data)->json();
    }

    public function sendWithToken($url, $token, $data) {
        return Http::withToken($token)->acceptJson()->post($url, $data)->json();
    }

}
