<?php

/*
* Sure PetCare Api.
* Used to authorize the client and send requests
*
* PHP class based on https://github.com/alextoft/sureflap
*
* Author: Jean-Michel Védrine 2018
*/

require_once('SurePetCareApi.php');

class SurePetCareClient {
    protected $baseUrl = 'https://app.api.surehub.io';
    public $email;
    public $password;
    public $deviceid;
    public $token;
    public $households;
    public $devices;
    public $pets;

    public function __construct($email, $password) {
        $this->email = $email;
        $this->password = $password;
        // Invent something for mandatory fingerprintJs login value. Any 32bit integer will suffice.
        $this->deviceid = (string) rand(1000000000,9999999999);

    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setDeviceid($deviceid) {
        $this->deviceid = $deviceid;
    }

    public function getToken() {
        return $this->token;
    }

    public function login() {
        try {
            $result = SurePetCareApi::request($this->baseUrl.'/api/auth/login',
                    array(
                        'email_address' => $this->email,
                        'password' => $this->password,
                        'device_id' => $this->deviceid
                    )
            );
        } catch (Exception $e) {
            echo "Login failed ". $e->getMessage();
            return false;
        }
        if(isset($result['data']['token'])) {
            $this->token = $result['data']['token'];
            return $this->token;
        }
        return false;

    }

    public function logout() {
        if($this->token !== false) {
            $result = SurePetCareApi::request($this->baseUrl.'/api/auth/logout', null, 'GET', array('Authorization: Bearer ' . $this->token));
        }
    }

    public function getHouseholds($forceupdate = false) {

        if($this->token !== false || $forceupdate) {
            $result = SurePetCareApi::request($this->baseUrl.'/api/household', null, 'GET', array('Authorization: Bearer ' . $this->token));
            if(isset($result['data'])) {
                $this->households = $result['data'];
            }
        }

        return $this->households;
    }

    public function getDevices($householdid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            $result = SurePetCareApi::request($this->baseUrl. "/api/household/$householdid/device", null, 'GET', array('Authorization: Bearer ' . $this->token));
            if(isset($result['data'])) {
                $this->devices[$householdid] = $result['data'];
            }
        }

        return $this->devices[$householdid];
    }

    public function getAllDevices($forceupdate = false) {
        foreach($this->households as $household) {
            $this->getDevices($household['id'], $forceupdate);
        }
        return $this->devices;
    }

    public function getPet($petid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            if (isset($this->pets[$petid])) {
                $householdid = $this->pets[$petid]['household_id'];
                $result = SurePetCareApi::request($this->baseUrl. "/api/household/$householdid/pet", null, 'GET', array('Authorization: Bearer ' . $this->token));
                if(isset($result['data'])) {
                    foreach($result['data'] as $pet){
                        if ($pet['id'] == $petid) {
                            $this->pets[$petid] = $pet;
                        }
                    }
                }
            }
        }

        return $this->pets[$householdid];
    }

    public function getPets($householdid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            $petarray = array();
            $result = SurePetCareApi::request($this->baseUrl. "/api/household/$householdid/pet", null, 'GET', array('Authorization: Bearer ' . $this->token));
            if(isset($result['data'])) {
                foreach($result['data'] as $pet){
                    $this->pets[$pet['id']] = $pet;
                    $petarray[] = $pet;
                }
            }
        }

        return $petarray;
    }

    public function getAllPets($forceupdate = false) {
        foreach($this->households as $household) {
            $this->getPets($household['id'], $forceupdate);
        }
        return $this->pets;
    }

    public function getDeviceStatus($deviceid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            $result = SurePetCareApi::request($this->baseUrl. "/api/device/$deviceid/control", null, 'GET', array('Authorization: Bearer ' . $this->token));
        }
        return $result['data'];
    }
    
    /* Locking modes:
     *  0 = unlocked
     * 	1 = locked in
     *  2 = locked out
     *  3 = locked all
     *  4 = curfew
     */
    public function getLockingMode($deviceid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            $result = SurePetCareApi::request($this->baseUrl. "/api/device/$deviceid/control", null, 'GET', array('Authorization: Bearer ' . $this->token));
        }
        return $result['data']['locking'];
    }
    /* Location:
     * 	1 = inside
     *  2 = outside
     */
    public function getPetLocation($petid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            $result = SurePetCareApi::request($this->baseUrl. "/api/pet/$petid/position", null, 'GET', array('Authorization: Bearer ' . $this->token));
        }
        return $result['data']['where'];
    }

    public function getAllPetsLocation($forceupdate = false) {
        foreach($this->pets as $pet) {
            $this->getPetLocation($pet['id'], $forceupdate);
        }
    }


}
