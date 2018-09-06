<?php

/*
* Sure PetCare Api.
* Helper class to make requests against Sure PetCare API
*
* PHP class based on https://github.com/alextoft/sureflap
*
* Author: Jean-Michel Védrine 2018
*/

class SurePetCareApi {
    public static function request($url, $payload = array(), $method = "POST", $headers = array()) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $requestHeaders = array(
            'Connection: keep-alive',
            'Origin: https://surepetcare.io',
            'Referer: https://surepetcare.io/',
        );

        if($method == "POST") {
            $json = json_encode($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $requestHeaders[] = 'Content-Type: application/json';
            $requestHeaders[] = 'Content-Length: ' . strlen($json);
        }



        if(count($headers) > 0) {
            $requestHeaders = array_merge($requestHeaders, $headers);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 7.0; SM-G930F Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/64.0.3282.137 Mobile Safari/537.36');

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}
