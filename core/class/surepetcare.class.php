<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class surepetcare extends eqLogic {
    /*     * *************************Attributs****************************** */
    public static $_widgetPossibility = array('custom' => true);


    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
     */
    public static function cron() {
        log::add('surepetcare', 'debug', 'cron');
        foreach (eqLogic::byType('surepetcare', true) as $eqLogic) {
            if ($eqLogic->getConfiguration('type') == 'pet') {
                $eqLogic->getPetStatus();
            } else if ($eqLogic->getConfiguration('type') == 'device') {
                $eqLogic->getDeviceStatus();
            }
        }
    }

    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
     */
    /*
    TODO voir s'il est nécessaire ou pas de rafraîchir le token.
      public static function cronDaily() {
          // Voir s'il faut d'abord faire un logout.
          // On demande un nouveau token et on le stocke.
          surepetcare::login();
      }
    */

    public static function request($url, $payload = null, $method = 'POST', $headers = array()) {
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
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $requestHeaders[] = 'Content-Type: application/json';
            $requestHeaders[] = 'Content-Length: ' . strlen($payload);
        }

        if(count($headers) > 0) {
            $requestHeaders = array_merge($requestHeaders, $headers);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 7.0; SM-G930F Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/64.0.3282.137 Mobile Safari/537.36');

        $result = curl_exec($ch);
        log::add('surepetcare','debug','Request result '.$result);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code =='200') {
            return json_decode($result, true);
        } else if ($code =='201' || $code =='204') {
            // La requête ou la création a réussi mais rien à retourner.
            // Le code 204 est retourné lors d'une requête position si
            // l'animal n'a jamais franchi aucune chatière ou n'est
            // enregistré dans aucune chatière (cas où seul le distributeur
            // de nourriture est présent).
            // Le code 201 est retourné lors d'une requête pour setposition.
            return '';
        } else {
            log::add('surepetcare','debug','Request failed result='.$result);
            throw new \Exception(__('Erreur lors de la requete : ',__FILE__).$url.' ('.$method.'), data : '.json_encode($payload).' erreur : ' . $code);
        }
    }

  public static function login() {
    $url = 'https://app.api.surehub.io/api/auth/login';
    $mailadress = config::byKey('emailAdress','surepetcare');
    $password = config::byKey('password','surepetcare');
    $device_id = rand(1,9);
    for($i=0; $i<9; $i++) {
        $device_id .= rand(0,9);
    }

    $data = array(
            'email_address' => $mailadress,
            'password' => $password,
            'device_id' => $device_id
    );
    $json = json_encode($data);
    log::add('surepetcare','debug', 'login data='.str_replace($password,'****',$json));
    // log::add('surepetcare','debug', 'login data='.$json);
    $request_http = new com_http($url);
    $request_http->setNoSslCheck(true);
    $request_http->setUserAgent('Mozilla/5.0 (Linux; Android 7.0; SM-G930F Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/64.0.3282.137 Mobile Safari/537.36');
    $headers = array(
            'Connection: keep-alive',
            'Origin: https://surepetcare.io',
            'Referer: https://surepetcare.io/',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json)
    );
    $request_http->setHeader($headers);
    $request_http->setPost(json_encode($data));

    $result = $request_http->exec();
    log::add('surepetcare','debug','login result='.$result);
    $result = is_json($result, $result);
    if(isset($result['data']['token'])) {
            $token = $result['data']['token'];
            cache::set('surepetcare::token',$token, 0);
            return $token;
    }
    cache::set('surepetcare::token','', 0);
    return false;
  }

  public static function getHouseholds(){
    $token = cache::byKey('surepetcare::token')->getValue();
    if ($token == '') {
        $token = surepetcare::login();
    }
    $url = 'https://app.api.surehub.io/api/household';
    $request_http = new com_http($url);
    $request_http->setNoSslCheck(true);
    $requestHeaders = array(
            'Connection: keep-alive',
            'Origin: https://surepetcare.io',
            'Referer: https://surepetcare.io/',
            'Authorization: Bearer ' . $token
        );
    $request_http->setHeader($requestHeaders);
    $request_http->setUserAgent('Mozilla/5.0 (Linux; Android 7.0; SM-G930F Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/64.0.3282.137 Mobile Safari/537.36');

    $result = $request_http->exec();
    log::add('surepetcare','debug','Gethouseholds result : '.$result);
    $result = is_json($result, $result);
    $households = $result['data'];
    if(count($households) == 0){
      return;
    }
    log::add('surepetcare','debug','Found '.count($households). ' households');
    // config::remove('households', 'surepetcare');
    $households_config = config::byKey('households','surepetcare',array());
    log::add('surepetcare','debug','Household_config= '.print_r($households_config, true));
    foreach ($households as $household) {
      log::add('surepetcare','debug','Household id= '.$household['id']);
      log::add('surepetcare','debug','Household name= '.$household['name']);
      foreach ($households_config as $key=>$household_config) {
        if($household_config['id'] == $household['id']){
          $household_config['name'] = $household['name'];
          $households_config[$key] = $household_config;
          break(2);
        }
      }
      $households_config[] = array('id' => $household['id'], 'name' => $household['name']);
    }
    config::save('households',$households_config,'surepetcare');
  }
  public static function sync(){
    $token = surepetcare::login();
    surepetcare::getHouseholds();
    $households = config::byKey('households','surepetcare',array());
    foreach ($households as $household) {
        // Récupération des devices.
        $result = surepetcare::request('https://app.api.surehub.io/api/household/'. $household['id'].'/device', null, 'GET', array('Authorization: Bearer ' . $token));
        log::add('surepetcare','debug','getDevices result : '.json_encode($result));

        if(isset($result['data'])) {
            $devices = $result['data'];
            foreach ($devices as $key => $device) {
                log::add('surepetcare','debug','Device '.$key. '='.json_encode($device));
                if(!isset($device['id']) || !isset($device['product_id'])){
                    log::add('surepetcare','debug','Missing device id or product id');
                    continue;
                }
                $found_eqLogics[] = self::findProduct($device,$household);
                log::add('surepetcare','debug',json_encode($found_eqLogics));
            }
        }
        $result = surepetcare::request('https://app.api.surehub.io/api/household/'. $household['id'].'/pet', null, 'GET', array('Authorization: Bearer ' . $token));
        log::add('surepetcare','debug','getPets result : '.json_encode($result));

        if(isset($result['data'])) {
            foreach($result['data'] as $key => $pet){
                log::add('surepetcare','debug','Pet '.$key. '='.json_encode($pet));
                if(!isset($pet['id']) || !isset($pet['name'])){
                    log::add('surepetcare','debug','Missing pet id or name');
                    continue;
                }
                $result = surepetcare::request('https://app.api.surehub.io/api/pet/' . $pet['id'].'?with[]=photo&with[]=breed&with[]=conditions&with[]=tag&with[]=food_type&with[]=species', null, 'GET', array('Authorization: Bearer ' . $token));
                $petfull = $result['data'];
                log::add('surepetcare','debug','Petfull '.$key. '='.json_encode($petfull));
                $found_eqLogics[] = self::findPet($petfull,$household);
                log::add('surepetcare','debug',json_encode($found_eqLogics));
            }
        }
        // Construction de la liste de choix des animaux pour les commandes profile.
        $petstags = array();
        foreach (eqLogic::byType('surepetcare', true) as $eqLogic) {
            if ($eqLogic->getConfiguration('type') == 'pet') {
                $name = $eqLogic->getName();
                $tagId = $eqLogic->getConfiguration('tag_id');
                $petstags[] = $tagId . '|' . $name;
            }
        }
        $listTags = implode(';', $petstags);
        foreach (eqLogic::byType('surepetcare', true) as $eqLogic) {
            if ($eqLogic->getConfiguration('type') == 'device') {
                $profile2 = $eqLogic->getCmd(null, 'dev.profile::2');
                if (is_object($profile2)) {
                    $profile2->setConfiguration('listValue', $listTags);
                    $profile2->save();
                }
                $profile3 = $eqLogic->getCmd(null, 'dev.profile::3');
                if (is_object($profile3)) {
                    $profile3->setConfiguration('listValue', $listTags);
                    $profile3->save();
                }
            }
        }
    }

  }

  public static function findProduct($_device,$_household) {
    $create = false;
    $eqLogic = self::byLogicalId('dev.' . $_device['id'], 'surepetcare');
    if(!is_object($eqLogic)){
       log::add('surepetcare','info','Nouvel équipement : '.$_device['name']);
      event::add('jeedom::alert', array(
        'level' => 'warning',
        'page' => 'surepetcare',
        'message' => __('Nouveau produit detecté', __FILE__),
      ));
      $create = true;
      $eqLogic = new surepetcare();
      if ($_device['name'] != '') {
      $eqLogic->setName($_device['name']);
      } else {
          $eqLogic->setName( __('Produit', __FILE__). $_device['id']);
      }
    }
    $eqLogic->setEqType_name('surepetcare');
    $eqLogic->setIsEnable(1);
    $eqLogic->setIsVisible(1);
    $eqLogic->setLogicalId('dev.' . $_device['id']);
    $eqLogic->setConfiguration('household_id', $_household['id']);
    $eqLogic->setConfiguration('household_name', $_household['name']);
    $eqLogic->setConfiguration('type', 'device');
    if(isset($_device['category'])){
      $eqLogic->setConfiguration('category', $_device['category']);
    }
    if(isset($_device['parent_device_id'])){
      $eqLogic->setConfiguration('parent_device_id', $_device['parent_device_id']);
    }
    if(isset($_device['product_id'])){
      $eqLogic->setConfiguration('product_id', $_device['product_id']);
    }
    if(isset($_device['serial_number'])){
      $eqLogic->setConfiguration('serial_number', $_device['serial_number']);
    }
    $eqLogic->setConfiguration('surepetcare_id', $_device['surepetcare_id']);
    $products = $eqLogic->getConfiguration('products',array());
    if (!in_array($_device['product_id'],$products)){
      $products[]=$_device['product_id'];
    }
    if ($eqLogic->getConfiguration('iconProduct','') == ''){
      $eqLogic->setConfiguration('iconProduct','device'. $_device['product_id'].'.png');
    }
    $eqLogic->setConfiguration('products', $products);
    $eqLogic->save();
    if(file_exists(__DIR__.'/../config/products/device'.$_device['product_id'].'.json')){
      log::add('surepetcare','debug','Found config file for product id ' . $_device['product_id']);
      $products = json_decode(file_get_contents(__DIR__.'/../config/products/device'.$_device['product_id'].'.json'),true);
      log::add('surepetcare','debug','Products : '.file_get_contents(__DIR__.'/../config/products/device'.$_device['product_id'].'.json'));
      $eqLogic->setConfiguration('product_name', $products['configuration']['product_name']);
      if (isset($products['configuration']['battery_type'])) {
        $eqLogic->setConfiguration('battery_type', $products['configuration']['battery_type']);
      }
      $eqLogic->save();
      $link_cmds = array();
      foreach ($products['commands'] as $product) {
         log::add('surepetcare','debug','Commande : '.json_encode($product));
        $cmd = $eqLogic->getCmd(null,'dev.'.$product['logicalId']);
        if(is_object($cmd)){
          continue;
        }
        $cmd = new surepetcareCmd();
        utils::a2o($cmd,$product);
        $cmd->setLogicalId('dev.'.$product['logicalId']);
        $cmd->setEqLogic_id($eqLogic->getId());
        $cmd->save();
        if (isset($product['value'])) {
          $link_cmds[$cmd->getId()] = $product['value'];
        }
      }
    } else {
        log::add('surepetcare','debug','No config file for product id ' . $_device['product_id']);
    }
    if (count($link_cmds) > 0) {
      foreach ($eqLogic->getCmd() as $eqLogic_cmd) {
        foreach ($link_cmds as $cmd_id => $link_cmd) {
          if ($link_cmd == $eqLogic_cmd->getName()) {
            $cmd = cmd::byId($cmd_id);
            if (is_object($cmd)) {
              $cmd->setValue($eqLogic_cmd->getId());
              $cmd->save();
            }
          }
        }
      }
    }
    $updatedValue = $eqLogic->applyData($_device);
    return $eqLogic;
  }

  public static function findPet($_pet,$_household) {
    $create = false;
    $eqLogic = self::byLogicalId('pet.' . $_pet['id'], 'surepetcare');
    if(!is_object($eqLogic)){
       log::add('surepetcare','info','Nouvel animal '.$_pet['name']);
      event::add('jeedom::alert', array(
        'level' => 'warning',
        'page' => 'surepetcare',
        'message' => __('Nouvel animal detecté', __FILE__),
      ));
      $create = true;
      $eqLogic = new surepetcare();
      $eqLogic->setName($_pet['name']);
    }
    $eqLogic->setEqType_name('surepetcare');
    $eqLogic->setIsEnable(1);
    $eqLogic->setIsVisible(1);
    $eqLogic->setLogicalId('pet.' . $_pet['id']);
    $eqLogic->setConfiguration('household_id', $_household['id']);
    $eqLogic->setConfiguration('pet_id', $_pet['id']);
    $eqLogic->setConfiguration('household_name', $_household['name']);
    $eqLogic->setConfiguration('type', 'pet');
    if(isset($_pet['category'])){
      $eqLogic->setConfiguration('category', $_pet['category']);
    }
    if(isset($_pet['gender'])){
      $eqLogic->setConfiguration('gender', $_pet['gender']);
    }
    if(isset($_pet['weight'])){
      $eqLogic->setConfiguration('weight', $_pet['weight']);
    }
    if(isset($_pet['photo']['location'])){
      $extension = pathinfo($_pet['photo']['location'], PATHINFO_EXTENSION);
      $photo_location = 'plugins/surepetcare/data/pet'. $_pet['id']. '.' . $extension;
      $eqLogic->setConfiguration('photo_location', $photo_location);
      file_put_contents(__DIR__.'/../../data/pet'. $_pet['id']. '.' . $extension, file_get_contents($_pet['photo']['location']));
    }
    if(isset($_pet['comments'])){
      $eqLogic->setConfiguration('comments', $_pet['comments']);
    }
    if(isset($_pet['breed'])){
      $eqLogic->setConfiguration('breed_id', $_pet['breed']['id']);
      $eqLogic->setConfiguration('breed_name', $_pet['breed']['name']);
    }
    if(isset($_pet['food_type_id'])){
      $eqLogic->setConfiguration('food_type_id', $_pet['food_type_id']);
    }
    if(isset($_pet['species_id'])){
      $eqLogic->setConfiguration('species_id', $_pet['species_id']);
    }
    if(isset($_pet['tag_id'])){
      $eqLogic->setConfiguration('tag_id', $_pet['tag_id']);
    }
    $eqLogic->save();

    return $eqLogic;
  }

  public static function devicesParameters($_device = '') {
    $return = array();
    foreach (ls(dirname(__FILE__) . '/../config/devices', '*') as $dir) {
      $path = dirname(__FILE__) . '/../config/devices/' . $dir;
      if (!is_dir($path)) {
        continue;
      }
      log::add('surepetcare', 'debug', 'devicesParameters path '.$path);
      $files = ls($path, '*.json', false, array('files', 'quiet'));
      foreach ($files as $file) {
        try {
          $content = file_get_contents($path . '/' . $file);
          if (is_json($content)) {
            $return += json_decode($content, true);
          }
        } catch (Exception $e) {
        }
      }
    }
    if (isset($_device) && $_device != '') {
      if (isset($return[$_device])) {
        return $return[$_device];
      }
      return array();
    }
    log::add('surepetcare', 'debug', 'devicesParameters return '.json_encode($return));
    return $return;
  }

  public static function formatTime($_time) {
    if (strlen($_time) == 4) {
        return substr($_time, 0, 2) . ':' . substr($_time, 2, 2);
    } elseif (strlen($_time) == 3) {
        return '0' . substr($_time, 0, 1) . ':' . substr($_time, 1, 2);
    } else {
        return '00:00';
    }
  }
    /*     * *********************Méthodes d'instance************************* */
    public function getDeviceStatus() {
        $token = cache::byKey('surepetcare::token')->getValue();
        if ($token == '') {
            $token = surepetcare::login();
        }
        // On récupère les infos sur l'équipement.
        $logicalId = explode('.',$this->getLogicalId());
        $deviceId = $logicalId[1];
        $url = 'https://app.api.surehub.io/api/device/' . $deviceId . '/status';
        $result = surepetcare::request($url, null, 'GET', array('Authorization: Bearer ' . $token));

        if (isset($result['data'])) {
            if (isset($result['data']['battery'])) {
                $battery_max = 6.0;
                $battery_min = 4.2;
                $battery = round(($result['data']['battery'] - $battery_min) / ($battery_max - $battery_min) * 100, 0);
                if ($battery < 0) {
                    $battery = 0;
                }
                if ($battery > 100) {
                    $battery = 100;
                }
                $this->batteryStatus($battery);
            }
            $url = 'https://app.api.surehub.io/api/device/' . $deviceId . '/control';
            $result2 = surepetcare::request($url, null, 'GET', array('Authorization: Bearer ' . $token));
            if (isset($result2['data']['curfew'])) {
                log::add('surepetcare','debug','curfew: '. print_r($result2['data']['curfew'], true));
                $result['data']['curfew'] = $result2['data']['curfew'];
            }
            $this->applyData($result['data']);
        }
    }

    public function getPetStatus() {
        $token = cache::byKey('surepetcare::token')->getValue();
        if ($token == '') {
            $token = surepetcare::login();
        }
        $logicalId = explode('.',$this->getLogicalId());
        $petId = $logicalId[1];
        $url = 'https://app.api.surehub.io/api/pet/' . $petId . '?with[]=status';
        $result = surepetcare::request($url, null, 'GET', array('Authorization: Bearer ' . $token));
        log::add('surepetcare','debug', "Résultat getPetStatus $petId : ". print_r($result, true));
        // $url = 'https://app.api.surehub.io/api/pet/' . $petId . '/position';
        // $result = surepetcare::request($url, null, 'GET', array('Authorization: Bearer ' . $token));
        // log::add('surepetcare','debug', "GetPetStatus $petId : ". print_r($result, true));
        if (isset($result['data']['status']['activity']['where'])) {
            $position = ($result['data']['status']['activity']['where'] == 1);
            $since = $result['data']['status']['activity']['since'];
            $device_id = $result['data']['status']['activity']['device_id'];
            $eqLogic = self::byLogicalId('dev.' . $device_id, 'surepetcare');
            if(is_object($eqLogic)){
                $this->checkAndUpdateCmd('pet.through', $eqLogic->getName());
            } else {
                log::add('surepetcare','debug', 'Device inconnu id ' . $device_id . ' dans getPetStatus');
            }
            log::add('surepetcare','debug', 'Mise à jour position animal ' . $petId . ' nouvelle valeur ' . $position);
            $this->checkAndUpdateCmd('pet.position', $position);
            $date = new DateTime($since, new DateTimeZone('UTC'));
            date_timezone_set($date,  new DateTimeZone(config::byKey('timezone')));
            log::add('surepetcare','debug', 'Mise à jour dernier passage ' . $date->format('Y-m-d H:i:s'));
            $this->checkAndUpdateCmd('pet.since', $date->format('Y-m-d H:i:s'));
        }
    }

  public function applyData($_data) {
    log::add('surepetcare','debug','applyData '.print_r($_data, true));
    $updatedValue = false;
    if($this->getConfiguration('type') != 'device') {
      log::add('surepetcare', 'debug', 'aplyData wrong type');
      return $updatedValue;
    }

    foreach ($this->getCmd('info') as $cmd) {
      $logicalId = $cmd->getLogicalId();
      if ($logicalId == '') {
        continue;
      }
      $epClusterPath = explode('.', $logicalId);
      if ($epClusterPath[0] != 'dev') {
        log::add('surepetcare', 'debug', 'applyData wrong clusterpath');
        continue;
      }
      $path = explode('::', $epClusterPath[1]);
      $value = $_data;
      foreach ($path as $key) {
        if (!isset($value[$key])) {
            continue (2);
        }
        $value = $value[$key];
      }
      if (!is_array($value)){
        log::add('surepetcare', 'debug', 'Mise à jour commande ' . $cmd->getName() . ' nouvelle valeur ' . $value);
        $this->checkAndUpdateCmd($cmd,$value);
        $updatedValue = true;
      } else {
          log::add('surepetcare', 'debug', 'applyData new value is an array '. print_r($value, true) . ' for key '. $key);
      }
    }
    // TODO batterie.
    return $updatedValue;
  }

  public function postSave() {
    If ($this->getConfiguration('type') == 'device') {
        if ($this->getConfiguration('applyProductId') != $this->getConfiguration('product_id')) {
          $this->applyModuleConfiguration();
          $this->refreshWidget();
        }
    }
    If ($this->getConfiguration('type') == 'pet') {
        if ($this->getIsEnable() == 1) {
            // Position (info).
            $position = $this->getCmd(null, 'pet.position');
            if (!is_object($position)) {
                $position = new surepetcareCmd();
                $position->setIsVisible(0);
                $position->setName(__('Position', __FILE__));
                $position->setConfiguration('historizeMode', 'none');
                $position->setIsHistorized(1);
            }
            $position->setDisplay('generic_type', 'PRESENCE');
            $position->setEqLogic_id($this->getId());
            $position->setType('info');
            $position->setSubType('binary');
            $position->setLogicalId('pet.position');
            $position->save();

            // Fixer la position (action)
            $setposition = $this->getCmd(null, 'pet.setposition::#select#');
            if (!is_object($setposition)) {
                $setposition = new surepetcareCmd();
                $setposition->setName(__('Fixer la position', __FILE__));
                $setposition->setIsVisible(1);
            }
            $setposition->setDisplay('generic_type', 'DONT');
            $setposition->setEqLogic_id($this->getId());
            $setposition->setType('action');
            $setposition->setSubType('select');
            $setposition->setConfiguration('listValue','0|Extérieur;1|Intérieur');
            $setposition->setLogicalId('pet.setposition::#select#');
            $setposition->setValue($position->getId());
            $setposition->save();
            
            // Date/Heure dernier passage.
            $since = $this->getCmd(null, 'pet.since');
            if (!is_object($since)) {
                $since = new surepetcareCmd();
                $since->setIsVisible(0);
                $since->setName(__('Dernier passage', __FILE__));
                $since->setConfiguration('historizeMode', 'none');
                $since->setIsHistorized(0);
            }
            $since->setDisplay('generic_type', 'DONT');
            $since->setEqLogic_id($this->getId());
            $since->setType('info');
            $since->setSubType('string');
            $since->setLogicalId('pet.since');
            $since->save();
            // Entré/sorti par.
            $through = $this->getCmd(null, 'pet.through');
            if (!is_object($through)) {
                $through = new surepetcareCmd();
                $through->setIsVisible(0);
                $through->setName(__('Passé par', __FILE__));
                $through->setConfiguration('historizeMode', 'none');
                $through->setIsHistorized(0);
            }
            $through->setDisplay('generic_type', 'DONT');
            $through->setEqLogic_id($this->getId());
            $through->setType('info');
            $through->setSubType('string');
            $through->setLogicalId('pet.through');
            $through->save();
        }
    }
  }

  public function getImage() {
    if ($this->getConfiguration('type') == 'device') {
      return 'plugins/surepetcare/core/config/images/' . $this->getConfiguration('iconProduct');
    } else if ($this->getConfiguration('type') == 'pet') {
      return $this->getConfiguration('photo_location');
    } else {
      return 'plugins/surepetcare/plugin_info/surepetcare_icon.png';
    }
  }

  public function applyModuleConfiguration() {
    log::add('surepetcare', 'debug', 'debut de applyModuleConfiguration');
    log::add('surepetcare', 'debug', 'product_id='.$this->getConfiguration('product_id'));
    $this->setConfiguration('applyProductId', $this->getConfiguration('product_id'));
    $this->save();
    if ($this->getConfiguration('product_id') == '') {
      log::add('surepetcare', 'debug', 'applyModuleConfiguration retour true');
      return true;
    }
    log::add('surepetcare', 'debug', 'applyModuleConfiguration envoi vers devicesParameters');
    $device = self::devicesParameters($this->getConfiguration('product_id'));
    if (!is_array($device)) {
      return true;
    }
    log::add('surepetcare', 'debug', 'applyModuleConfiguration import' . print_r($device, true));
    $this->import($device);
  }

    public function preInsert() {

    }

    public function postInsert() {

    }

    public function preSave() {

    }

    public function preUpdate() {

    }

    public function postUpdate() {

    }

    public function preRemove() {

    }

    public function postRemove() {

    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
     */
public function toHtml($_version = 'dashboard') {
    if ($this->getConfiguration('type') == 'device') {
        return parent::toHtml($_version);
    }

    $replace = $this->preToHtml($_version);
    if (!is_array($replace)) {
        return $replace;
    }
    $version = jeedom::versionAlias($_version);
    if ($this->getDisplay('hideOn' . $version) == 1) {
        return '';
    }

    foreach ($this->getCmd('info') as $cmd) {
        $replace['#' . $cmd->getLogicalId() . '_display#'] = (is_object($cmd) && $cmd->getIsVisible()) ? '#' . $cmd->getLogicalId() . '_display#' : "none";
        $replace['#' . $cmd->getLogicalId() . '_name_display#'] = ($cmd->getDisplay('icon') != '') ? $cmd->getDisplay('icon') : $cmd->getName();
        $replace['#' . $cmd->getLogicalId() . '_name#'] = $cmd->getName();
        $replace['#' . $cmd->getLogicalId() . '_hide_name#'] = '';
        $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
        $replace['#' . $cmd->getLogicalId() . '_version#'] = $_version;
        $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
        $replace['#' . $cmd->getLogicalId() . '_uid#'] = 'cmd' . $cmd->getId() . eqLogic::UIDDELIMITER . mt_rand() . eqLogic::UIDDELIMITER;
        $replace['#' . $cmd->getLogicalId() . '_collectDate#'] = $cmd->getCollectDate();
        $replace['#' . $cmd->getLogicalId() . '_valueDate#'] = $cmd->getValueDate();
        $replace['#' . $cmd->getLogicalId() . '_alertLevel#'] = $cmd->getCache('alertLevel', 'none');
        if ($cmd->getIsHistorized() == 1) {
            $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
        }
        if ($cmd->getDisplay('showNameOn' . $_version, 1) == 0) {
            $replace['#' . $cmd->getLogicalId() . '_hide_name#'] = 'hidden';
        }
    }
    $setpositionCmd = surepetcareCmd::byEqLogicIdAndLogicalId($this->getId(),'pet.setposition::#select#');
    $replace['#pet.position_display#'] = (is_object($setpositionCmd) && $setpositionCmd->getIsVisible()) ? "#pet.position_display#" : "none";
    $cmdlogic = surepetcareCmd::byEqLogicIdAndLogicalId($this->getId(),'pet.setposition::#select#');
    $replace['#pet.fixposition_id#'] = $cmdlogic->getId();
    $replace['#pet.fixposition_str#'] = __('Changer la position', __FILE__);
    $replace['#photolocation#'] = $this->getConfiguration('photo_location');
    $html = template_replace($replace, getTemplate('core', $version, 'pet', 'surepetcare'));
    return $this->postToHtml($_version, $html);
}

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class surepetcareCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */
    public function datatype($_data){
        $type_array = array('led_mode' => 'num');
        if (isset($type_array[$_data])) {
            return $type_array[$_data];
        }
        return 'string';
    }

    public function execute($_options = array()) {
        if ($this->getType() != 'action') {
            return;
        }
        $token = cache::byKey('surepetcare::token')->getValue();
        if ($token == '') {
            $token = surepetcare::login();
        }
        $method = 'PUT';
        $eqLogic = $this->getEqLogic();
        $eqType = $eqLogic->getConfiguration('type', '');
        $actionerDatas = explode('.',$eqLogic->getLogicalId());
        $actionerId = $actionerDatas[1];
        $logicalId = $this->getLogicalId();
        if ($eqType == 'device') {
            $url = 'https://app.api.surehub.io/api/device/' . $actionerId . '/control';
        }
        if ($eqType =='pet') {
            $url = 'https://app.api.surehub.io/api/pet/' . $actionerId . '/position';
        }
        log::add('surepetcare', 'debug', 'execute url='.$url);
        $actionDatas = explode('.',$logicalId);
        $parameters = array();
        $datasList = explode(';',$actionDatas[1]);
        $replace = array();
        switch ($this->getSubType()) {
            case 'slider':
            $replace['#slider#'] = intval($_options['slider']);
            break;
            case 'color':
            $replace['#color#'] = $_options['color'];
            break;
            case 'select':
            $replace['#select#'] = $_options['select'];
            break;
            case 'message':
            $replace['#title#'] = $_options['title'];
            $replace['#message#'] = $_options['message'];
            if ($_options['message'] == '' && $_options['title'] == '') {
              throw new Exception(__('Le message et le sujet ne peuvent pas être vide', __FILE__));
            }
            break;
        }
        // log::add('surepetcare','debug','Replace ' . print_r($replace, true));
        foreach ($datasList as $datas){
            $keyValue = explode('::',$datas);
            $type = self::datatype($keyValue[0]);
            $value = str_replace(array_keys($replace),$replace,explode('::',$datas)[1]);
            $parameters[$keyValue[0]] = $value;
            if ($type == 'bool'){
              $parameters[$keyValue[0]] = ($parameters[$keyValue[0]] == '0') ? false : true;
            }else if ($type == 'num'){
              $parameters[$keyValue[0]] = intval($parameters[$keyValue[0]]);
            }
            if($keyValue[0] =='curfew'){
                if ($parameters[$keyValue[0]]) {
                    $locktime = $eqLogic->getConfiguration('lock_time', '');
                    $unlocktime = $eqLogic->getConfiguration('unlock_time', '');
                    if ($locktime != '' && $unlocktime != '') {
                        $parameters[$keyValue[0]] = array(
                            'enabled' => true,
                            'lock_time' => $eqLogic::formatTime($locktime),
                            'unlock_time' => $eqLogic::formatTime($unlocktime)
                        );
                    } else {
                        log::add('surepetcare','error','Il faut remplir les heures de début et de fin de couvre-feu dans la configutation');
                        throw new Exception(__('Heures de couvre-feu incorrectes', __FILE__));
                    }
                } else {
                    $parameters[$keyValue[0]] = array('enabled' => false);
                }
            } else if($keyValue[0] =='setposition'){
                $method = 'POST';
                if ($parameters[$keyValue[0]] != 0 && $parameters[$keyValue[0]] != 1) {
                    log::add('surepetcare','debug','Invert position');
                    // No value passed to command, Invert position.
                    $positionCmd = surepetcareCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'pet.position');
                    $position = $positionCmd->execCmd();
                    log::add('surepetcare','debug','Current position '. $position);
                    if ($position != 0 && $position != 1) {
                        log::add('surepetcare','debug','No current position, return');
                        // No current position, impossible to invert it.
                        return;
                    }
                    // Invert position.
                    $parameters['where'] = ($position == 1 ? 2 : 1);
                    log::add('surepetcare','debug','Where parameter in invert position ' . $parameters['where']);
                } else {
                    // Just set position to corresponding value.
                    $parameters['where'] = ($parameters[$keyValue[0]] == 1 ? 1 : 2);
                    log::add('surepetcare','debug','Where parameter in set position ' . $parameters['where']);
                }
                // $parameters['since'] = date("Y-m-d H:i");
                $parameters['since'] = gmdate("Y-m-d H:i");
                unset($parameters['setposition']);
            } else if($keyValue[0] =='profile'){
                $url = 'https://app.api.surehub.io/api/device/' . $actionerId . '/tag/' . intval($_options['select']);
                log::add('surepetcare','debug','url='. $url);
                log::add('surepetcare','debug','keyvalue0' .$parameters[$keyValue[0]]);
            }
        }
        log::add('surepetcare','debug','Execute command whith parameters : '.json_encode($parameters));
        $result = surepetcare::request($url, json_encode($parameters), $method, array('Authorization: Bearer ' . $token));
        // On rafraichit les infos.
        if ($eqType =='pet') {
            $eqLogic->getPetStatus();
        } else if ($eqType =='device') {
            $eqLogic->getDeviceStatus();
        }
        $eqLogic->refreshWidget();
    }

    /*     * **********************Getteur Setteur*************************** */
}
