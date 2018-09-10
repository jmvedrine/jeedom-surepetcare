<?php

/*
 * Sure PetCare Client.
 * Used to authorize the client and send requests
 *
 * PHP class based on https://github.com/alextoft/sureflap
 *
 * Author: Jean-Michel Védrine 2018
 * Note: in order to not overload the sure Petcare's servers, if you use this class
 * in your own project, you should implement some caching mechanism and not call
 * the server too frequently. For the same reason you should cache the auth token and not call
 * the login function too frequently.
 */

class SurePetCareApi {
    public static function request($url, $payload = array(), $method = 'POST', $headers = array()) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $requestHeaders = array(
            'Connection: keep-alive',
            'Origin: https://surepetcare.io',
            'Referer: https://surepetcare.io/',
        );

        if($method == 'POST' || $method == 'PUT') {
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
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code =='200') {
            return json_decode($result, true);
        } else {
            throw new Exception('Http return code: ' . $code);
        }
    }
}

class SurePetCareClient {
    protected $baseUrl = 'https://app.api.surehub.io';
    private $email;
    private $password;
    private $userid;
    private $deviceid;
    private $token;
    private $households;
    private $devices;
    public $pets;
    private $petslocation;
    private $house_timeline;
    private $pet_timeline;

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

    public function getUserid() {
        return $this->userid;
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
        // var_dump($result);
        if(isset($result['data']['token'])) {
            $this->token = $result['data']['token'];
            $this->userid = $result['data']['user']['id'];
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

        return $this->pets[$petid];
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

    public function getDeviceControl($deviceid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            $result = SurePetCareApi::request($this->baseUrl. "/api/device/$deviceid/control", null, 'GET', array('Authorization: Bearer ' . $this->token));
        }
        return $result['data'];
    }

    public function getDeviceStatus($deviceid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            $result = SurePetCareApi::request($this->baseUrl. "/api/device/$deviceid/status", null, 'GET', array('Authorization: Bearer ' . $this->token));
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
            $this->petslocation[$petid] = $result['data']['where'];
        }
        return $this->petslocation[$petid];
    }

    public function getAllPetsLocation($forceupdate = false) {
        foreach($this->pets as $pet) {
            $this->getPetLocation($pet['id'], $forceupdate);
        }
        return $this->petslocation;
    }
    
    public function getHouseTimeLine($householdid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            $result = SurePetCareApi::request($this->baseUrl. "/api/timeline/household/$householdid", null, 'GET', array('Authorization: Bearer ' . $this->token));
            $this->house_timeline[$householdid] = $result['data'];
            // Now we update each pet timeline.
            foreach($this->pets as $petid => $pet) {
                if($pet['household_id'] == $householdid) {
                    $pettimeline = array();
                    foreach($this->house_timeline[$householdid] as $evt) {
                        if($evt['movements'][0]['type'] == 0 && $evt['movements'][0]['tag_id'] == $pet['tag_id']) {
                            $pettimeline[] = $evt;
                        }
                    }
                    $this->pet_timeline[$petid] = $pettimeline;
                }
            }
        }
        return $this->house_timeline[$householdid];
    }
    
    public function getPetTimeLine($petid, $forceupdate = false) {
        if($this->token !== false || $forceupdate) {
            if (isset($this->pets[$petid])) {
                // We must update the corresponding household timeline.
                $householdid = $this->pets[$petid]['household_id'];
                $this->getHouseTimeLine($householdid, $forceupdate);
            }
        }
        return $this->pet_timeline[$petid];
    }

    public function setLockingMode($deviceid, $lockmode) {
        $payload = array('locking' => "$lockmode");
        $result = SurePetCareApi::request($this->baseUrl."/api/device/$deviceid/control", $payload, 'PUT', array('Authorization: Bearer ' . $this->token));
        return $result['data']['locking'];
    }

    /* Hub led brightness
     * 	0 = off
     *  1 = bright
     *  4 = dim
     */
    public function setHubLedBrightness($deviceid, $ledmode) {
        $payload = array("led_mode" => $ledmode);
        $result = SurePetCareApi::request($this->baseUrl."/api/device/$deviceid/control", $payload, 'PUT', array('Authorization: Bearer ' . $this->token));
        return $result['data']['led_mode'];
    }

    /* Lock and Unlock Times format : HH:MM (eg. 18:00 06:00) */
    public function setEnableCurfew($deviceid, $locktime, $unlocktime) {
        $payload = array('curfew' => array('enabled' => true, 'lock_time' => "$locktime", 'unlock_time' => "$unlocktime"));
        $result = SurePetCareApi::request($this->baseUrl."/api/device/$deviceid/control", $payload, 'PUT', array('Authorization: Bearer ' . $this->token));
        return $result['data']['curfew']['enabled'];
    }

    public function setDisableCurfew($deviceid) {
        $payload = array('curfew' => array('enabled' => false));
        $result = SurePetCareApi::request($this->baseUrl."/api/device/$deviceid/control", $payload, 'PUT', array('Authorization: Bearer ' . $this->token));
        return $result['data']['curfew']['enabled'];
    }
}
