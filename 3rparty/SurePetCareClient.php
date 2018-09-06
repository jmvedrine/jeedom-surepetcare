<?php

/*
* Sure PetCare Api.
* Used to authorize the client and send requests
*
* PHP class based on https://github.com/alextoft/sureflap
*
* Author: Jean-Michel Védrine 2018
*/

require_once("SurePetCareApi.php");

class SurePetCareClient {
    protected $baseUrl = 'https://app.api.surehub.io';
    public $email;
    public $password;
    public $deviceid;
    public $token;
    public $households;
    public $devices;
    public $pets;

    public function __construct($email, $password, $deviceid) {
        $this->email = $email;
        $this->password = password;
        // Invent something for mandatory fingerprintJs login value. Any 32bit integer will suffice.
        $this->deviceid = (string) rand(1000000000,9999999999);

    }

    public function setToken($token) {
        $this->token = $token;
    }
    
    public function getToken() {
        return $this->token;
    }
    
    public function login() {
        $result = SurePetCareApi::request($this->baseUrl."/api/auth/login",
            array(
                "email_address" => $this->email,
                "password" => $this->password,
                "device_id" => $this->deviceid
            )
        );

        if(isset($result['token'])) {
            $this->token = $result['token'];
        }

        return $this->token;
    }

    public function getHouseholds() {
        $result = array("message" => "no token");

        if($this->token !== false) {
            $result = SurePetCareApi::request($this->baseUrl."/api/household", null, "GET", array("Authorization: Bearer " . $this->token));
            if(isset($result['data'])) {
                $this->households = $result['data'];
            }
        }
        
        return $this->households;
    }
    
    public function getDevices($householdid) {
        if($this->token !== false) {
            $result = SurePetCareApi::request($this->baseUrl. "/api/household/$householdid/device", null, "GET", array("Authorization: Bearer " . $this->token));
            if(isset($result['data'])) {
                $this->devices[$householdid] = $result['data'];
            }
        }

        return $this->devices[$householdid];
    }
    
    public function getAllDevices() {
        foreach($this->households as $household) {
            $this->getDevices($household['id']);
        }
        return $this->devices;
    }
    
    public function getPets($householdid) {
        if($this->token !== false) {
            $result = SurePetCareApi::request($this->baseUrl. "/api/household/$householdid/pet", null, "GET", array("Authorization: Bearer " . $this->token));
            if(isset($result['data'])) {
                $this->pets[$householdid] = $result['data'];
            }
        }

        return $this->pets[$householdid];
    }
    
    public function getAllPets() {
        foreach($this->households as $household) {
            $this->getPets($household['id']);
        }
        return $this->pets;
    }
}
