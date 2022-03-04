<?php


namespace App\Traits\Utils;


trait ArrayJsonManager
{
    public function arrayToJson($array){
        $array  = json_encode($array); // Converts the response array to string
        return json_decode($array); // Converts the string to json
    }

}
