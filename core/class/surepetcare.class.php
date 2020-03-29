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
        $autorefresh = config::byKey('autorefresh', 'surepetcare');
        if ($autorefresh != '') {
            try {
                $c = new Cron\CronExpression(checkAndFixCron($autorefresh), new Cron\FieldFactory);
                if ($c->isDue()) {
                    log::add('surepetcare', 'debug', 'cron is due');
                    $token = cache::byKey('surepetcare::token')->getValue();
                    if ($token == '') {
                        $token = surepetcare::login();
                    }
                    $url = 'https://app.api.surehub.io/api/pet?with[]=status&with[]=position&with[]=tag';
                    $result = surepetcare::request($url, null, 'GET', array('Authorization: Bearer ' . $token));
                    log::add('surepetcare','debug', "Pets Data : ". print_r($result, true));
                    if (isset($result['data'])) {
                        surepetcare::updatePetsStatus($result['data']);
                    } else {
                        log::add('surepetcare','debug', 'Aucune donnée pour les animaux lors de la mise à jour');
                    }
                    $url = 'https://app.api.surehub.io/api/device?with[]=children&with[]=status&with[]=curfew&with[]=control';
                    $result = surepetcare::request($url, null, 'GET', array('Authorization: Bearer ' . $token));
                    log::add('surepetcare','debug', "Devices Data : ". print_r($result, true));
                    if (isset($result['data'])) {
                        surepetCare::updateDevicesStatus($result['data']);
                    } else {
                        log::add('surepetcare','debug', 'Aucune donnée pour les équipements lors de la mise à jour');
                    }
                }
            } catch (Exception $exc) {
                log::add('surepetcare', 'error', __("Erreur lors de l'exécution du cron ", __FILE__) . $exc->getMessage());
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
    public static function cronDaily() {
        // Voir s'il faut d'abord faire un logout.
        // On demande un nouveau token et on le stocke.
        log::add('surepetcare','debug', 'Cron Daily');
        if (!surepetcare::login()) {
            log::add('surepetcare', 'error', __('Impossible de rafraîchir le token', __FILE__));
        }
    }

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

    public function logout() {
        $token = cache::byKey('surepetcare::token')->getValue();
        if ($token !== '') {
            $result = surepetcare::request('https://app.api.surehub.io/api/auth/logout', null, 'GET', array('Authorization: Bearer ' . $token));
        }
    }

  public static function sync(){
        $token = surepetcare::login();
    $result = surepetcare::request('https://app.api.surehub.io/api/me/start', null, 'GET', array('Authorization: Bearer ' . $token));
    log::add('surepetcare','debug','me/start result : '.json_encode($result));
    config::remove('households', 'surepetcare');
    if(isset($result['data']['households'])) {
        $households = $result['data']['households'];
        if(count($households) == 0){
          log::add('surepetcare','error','Aucun foyer trouvé, synchronisation impossible');
          return;
        }
        log::add('surepetcare','debug','Found '.count($households). ' households');
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
                $households_config[$household['id']] = $household['name'];
        }
        config::save('households',$households_config,'surepetcare');
    } else {
        log::add('surepetcare','error','Aucun foyer trouvé, synchronisation impossible');
        return;
    }
    if(isset($result['data']['devices'])) {
        foreach ($result['data']['devices'] as $key => $device) {
                log::add('surepetcare','debug','Device '.$key. '='.json_encode($device));
                if(!isset($device['id']) || !isset($device['product_id'])){
                    log::add('surepetcare','debug','Missing device id or product id');
                    continue;
                }
            $found_eqLogics[] = self::findProduct($device);
            log::add('surepetcare','debug',json_encode($found_eqLogics));
        }
    }

    if(isset($result['data']['pets'])) {
        foreach($result['data']['pets'] as $key => $pet){
            log::add('surepetcare','debug','Pet '.$key. '='.json_encode($pet));
            if(!isset($pet['id']) || !isset($pet['name'])){
                log::add('surepetcare','debug','Missing pet id or name');
                continue;
            }
            $result = surepetcare::request('https://app.api.surehub.io/api/pet/' . $pet['id'].'?with[]=photo&with[]=breed&with[]=conditions&with[]=tag&with[]=food_type&with[]=species', null, 'GET', array('Authorization: Bearer ' . $token));
            $petfull = $result['data'];
            log::add('surepetcare','debug','Petfull '.$key. '='.json_encode($petfull));
            $found_eqLogics[] = self::findPet($petfull);
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
        // Profile commands are only available for Microchip cat door connect.
        if ($eqLogic->getConfiguration('type') == 'device' && $eqLogic->getConfiguration('device_id') == 6) {
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

  public static function findProduct($_device) {
    $create = false;
    $households = config::byKey('households','surepetcare',array());
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
      $eqLogic->setEqType_name('surepetcare');
      $eqLogic->setIsEnable(1);
      $eqLogic->setIsVisible(1);
      $eqLogic->setLogicalId('dev.' . $_device['id']);
      $eqLogic->setConfiguration('device_id', $_device['id']);
      $eqLogic->setConfiguration('type', 'device');
    }
    $eqLogic->setConfiguration('household_id', $_device['household_id']);
    $eqLogic->setConfiguration('household_name', $households[$_device['household_id']]);
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
    if(isset($_device['mac_address'])){
      $eqLogic->setConfiguration('mac_address', $_device['mac_address']);
    }
    if(isset($_device['version'])){
      $eqLogic->setConfiguration('version', $_device['version']);
    }
    if(isset($_device['created_at'])){
      $eqLogic->setConfiguration('created_at', $_device['created_at']);
    }
    if(isset($_device['updated_at'])){
      $eqLogic->setConfiguration('updated_at', $_device['updated_at']);
    }
    // $eqLogic->setConfiguration('surepetcare_id', $_device['surepetcare_id']);
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
      if (isset($products['configuration']['battery_max'])) {
        $eqLogic->setConfiguration('battery_max', $products['configuration']['battery_max']);
      }
      if (isset($products['configuration']['battery_min'])) {
        $eqLogic->setConfiguration('battery_min', $products['configuration']['battery_min']);
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

  public static function findPet($_pet) {
    $create = false;
    $households = config::byKey('households','surepetcare',array());
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
      $eqLogic->setEqType_name('surepetcare');
      $eqLogic->setIsEnable(1);
      $eqLogic->setIsVisible(1);
      $eqLogic->setLogicalId('pet.' . $_pet['id']);
      $eqLogic->setConfiguration('pet_id', $_pet['id']);
      $eqLogic->setConfiguration('type', 'pet');
    }

    $eqLogic->setConfiguration('household_id', $_pet['household_id']);
    $eqLogic->setConfiguration('household_name', $households[$_pet['household_id']]);

    if(isset($_pet['category'])){
      $eqLogic->setConfiguration('category', $_pet['category']);
    }
    if(isset($_pet['gender'])){
        if ($_pet['gender'] == 1) {
            $eqLogic->setConfiguration('gender', __('Mâle', __FILE__));
        } else if ($_pet['gender'] == 2) {
            $eqLogic->setConfiguration('gender', __('Femelle', __FILE__));
        }
    }
    if(isset($_pet['weight'])){
      $eqLogic->setConfiguration('weight', $_pet['weight']);
    }
    if(isset($_pet['version'])){
      $eqLogic->setConfiguration('version', $_pet['version']);
    }
    if(isset($_pet['created_at'])){
      $eqLogic->setConfiguration('created_at', $_pet['created_at']);
    }
    if(isset($_pet['updated_at'])){
      $eqLogic->setConfiguration('updated_at', $_pet['updated_at']);
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
  
    public static function updatePetsStatus($data) {
        foreach ($data as $key => $pet) {
            log::add('surepetcare','debug','updatePetsStatus pet : '. print_r($pet, true));
            $eqLogic = self::byLogicalId('pet.' . $pet['id'], 'surepetcare');
            if(is_object($eqLogic) && $eqLogic->getIsEnable() == 1){
                if (isset($pet['status']['activity']['where'])) {
                    log::add('surepetcare','debug','updatePetsStatus pet activity : '. print_r($pet['status']['activity'], true));
                    $position = ($pet['status']['activity']['where'] == 1);
                    $since = $pet['status']['activity']['since'];
                    $date = new DateTime($since, new DateTimeZone('UTC'));
                    date_timezone_set($date,  new DateTimeZone(config::byKey('timezone')));
                    $device_id = $pet['status']['activity']['device_id'];
                    $eqLogic2 = self::byLogicalId('dev.' . $device_id, 'surepetcare');
                    if(is_object($eqLogic2)){
                        $eqLogic->checkAndUpdateCmd('pet.through', $eqLogic2->getName(), $date->format('Y-m-d H:i:s'));
                        log::add('surepetcare','debug', 'Mise à jour passé par ' . $pet['id'] . ' nouvelle valeur ' . $eqLogic2->getName());
                    } else {
                        // Ceci peut se produire si on a mis à jour la position par commande.
                        log::add('surepetcare','debug', 'Chatière inconnue id ' . $device_id . ' dans updatePetsStatus');
                    }
                    log::add('surepetcare','debug', 'Mise à jour position animal ' . $pet['id'] . ' nouvelle valeur ' . $position);
                    $eqLogic->checkAndUpdateCmd('pet.position', $position, $date->format('Y-m-d H:i:s'));
                    log::add('surepetcare','debug', 'Mise à jour dernier passage ' . $date->format('Y-m-d H:i:s'));
                    $eqLogic->checkAndUpdateCmd('pet.since', $date->format('Y-m-d H:i:s'));
                }
                if (isset($pet['status']['feeding'])) {
                    log::add('surepetcare','debug','updatePetsStatus pet feeding : '. print_r($pet['status']['feeding'], true));
                    $feedingtime = $pet['status']['feeding']['at'];
                    $date = new DateTime($feedingtime, new DateTimeZone('UTC'));
                    date_timezone_set($date,  new DateTimeZone(config::byKey('timezone')));
                    $device_id = $pet['status']['feeding']['device_id'];
                    $eqLogic2 = self::byLogicalId('dev.' . $device_id, 'surepetcare');
                    if(is_object($eqLogic2)){
                        $eqLogic->checkAndUpdateCmd('pet.feedingdevice', $eqLogic2->getName(), $date->format('Y-m-d H:i:s'));
                        log::add('surepetcare','debug', 'Mise à jour distributeur dernier repas ' . $pet['id'] . ' nouvelle valeur ' . $eqLogic2->getName());
                    } else {
                        log::add('surepetcare','debug', 'Mangeoire inconnue id ' . $device_id . ' dans updatePetsStatus');
                    }
                    if (isset($pet['status']['feeding']['change'])) {
                        if (is_array($pet['status']['feeding']['change'])) {
                            foreach ($pet['status']['feeding']['change'] as $key => $weight) {
                                log::add('surepetcare','debug', 'Poids dernier repas index '.$key. ' pour ' . $pet['id'] . ' nouvelle valeur ' . $weight);
                                $eqLogic->checkAndUpdateCmd('pet.feedingweight'.$key, $weight, $date->format('Y-m-d H:i:s'));
                            }
                        }
                    }
                    
                    log::add('surepetcare','debug', 'Mise à jour heure dernier repas ' . $date->format('Y-m-d H:i:s'));
                    $eqLogic->checkAndUpdateCmd('pet.feedingtime', $date->format('Y-m-d H:i:s'));
                } else {
                    log::add('surepetcare','debug', 'Pas d\'info dernier repas ');
                }
            }
        }
    }
    
    public static function updateDevicesStatus($data) {
        foreach ($data as $key => $device) {
            log::add('surepetcare','debug','updateDevicesStatus device : '. print_r($device, true));
            $eqLogic = self::byLogicalId('dev.' . $device['id'], 'surepetcare');
            if(is_object($eqLogic) && $eqLogic->getIsEnable() == 1){
                if (isset($device['status']['battery'])) {
                    log::add('surepetcare','debug','updateDevicesStatus battery : '. $device['status']['battery']);
                    $battery_max = $eqLogic->getConfiguration('battery_max', 6.0);
                    $battery_min = $eqLogic->getConfiguration('battery_min', 4.2);
                    $battery = round(($device['status']['battery'] - $battery_min) / ($battery_max - $battery_min) * 100, 0);
                    if ($battery < 0) {
                        $battery = 0;
                    }
                    if ($battery > 100) {
                        $battery = 100;
                    }
                    log::add('surepetcare','debug','Niveau batterie : '. $battery . '%');
                    $eqLogic->batteryStatus($battery);
                }
                if ($eqLogic->getConfiguration('product_id') == 3 || $eqLogic->getConfiguration('product_id') == 6) {
                    // It's a flap.
                    if (isset($device['control']['curfew'])) {
                        log::add('surepetcare','debug','updateDevicesStatus curfew : '. print_r($device['control']['curfew'], true));
                        if (isset($device['control']['curfew'][0])) {
                            // It's an array of several curfew.
                            $device['control']['curfew'] = $device['control']['curfew'][0];
                        }
                        if (count($device['control']['curfew']) == 0) {
                            // In case it's just an empty array.
                            log::add('surepetcare','debug','updateDevicesStatus curfew empty deactivating');
                            $device['control']['curfew'] = array('enabled' => false);
                        }
                    } else {
                        // Deactivate curfew .
                        log::add('surepetcare','debug','updateDevicesStatus curfew not set deactivating');
                        $device['control']['curfew'] = array('enabled' => false);
                    }
                    log::add('surepetcare','debug','updateDevicesStatus curfew after recoding : '. print_r($device['control']['curfew'], true));
                }
                $eqLogic->applyData($device);
            }
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
        $url = 'https://app.api.surehub.io/api/device/' . $deviceId . '?with[]=status&with[]=control&with[]=curfew&with[]=feeding';
        $result = surepetcare::request($url, null, 'GET', array('Authorization: Bearer ' . $token));
        log::add('surepetcare','debug',"Résultat getDeviceStatus $deviceid : " . print_r($result['data'], true));
        if (isset($result['data'])) {
            surepetCare::updateDevicesStatus(array($result['data']));
        }
    }

    public function getPetStatus() {
        $token = cache::byKey('surepetcare::token')->getValue();
        if ($token == '') {
            $token = surepetcare::login();
        }
        // On récupère les infos sur l'animal.
        $logicalId = explode('.',$this->getLogicalId());
        $petId = $logicalId[1];
        $url = 'https://app.api.surehub.io/api/pet/' . $petId . '?with[]=status';
        $result = surepetcare::request($url, null, 'GET', array('Authorization: Bearer ' . $token));
        log::add('surepetcare','debug', "Résultat getPetStatus $petId : ". print_r($result['data'], true));
        if (isset($result['data'])) {
            surepetCare::updatePetsStatus(array($result['data']));
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
                log::add('surepetcare', 'debug', 'applyData value key not set ' . $key);
                continue (2);
            }
            $value = $value[$key];
          }
          if (!is_array($value)){
            if ($key == 'lock_time' || $key == 'unlock_time') {
                // Format time to Jeedom numeric.
                // $value = str_replace(':', '', $value);
            }
            log::add('surepetcare', 'debug', 'Mise à jour commande ' . $cmd->getName() . ' nouvelle valeur ' . $value);
            $this->checkAndUpdateCmd($cmd,$value);
            $updatedValue = true;
          } else {
              log::add('surepetcare', 'debug', 'applyData new value is an array '. print_r($value, true) . ' for key '. $key);
          }
        }
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
                // Heure dernier repas (info).
                $feedingtime = $this->getCmd(null, 'pet.feedingtime');
                if (!is_object($feedingtime)) {
                    $feedingtime = new surepetcareCmd();
                    $feedingtime->setIsVisible(0);
                    $feedingtime->setName(__('Dernier repas', __FILE__));
                    $feedingtime->setConfiguration('historizeMode', 'none');
                    $feedingtime->setIsHistorized(0);
                }
                $feedingtime->setDisplay('generic_type', 'DONT');
                $feedingtime->setEqLogic_id($this->getId());
                $feedingtime->setType('info');
                $feedingtime->setSubType('string');
                $feedingtime->setLogicalId('pet.feedingtime');
                $feedingtime->save();
                // Dernière mangeoire (info).
                $feedingdevice = $this->getCmd(null, 'pet.feedingdevice');
                if (!is_object($feedingdevice)) {
                    $feedingdevice = new surepetcareCmd();
                    $feedingdevice->setIsVisible(0);
                    $feedingdevice->setName(__('Mangé dans', __FILE__));
                    $feedingdevice->setConfiguration('historizeMode', 'none');
                    $feedingdevice->setIsHistorized(0);
                }
                $feedingdevice->setDisplay('generic_type', 'DONT');
                $feedingdevice->setEqLogic_id($this->getId());
                $feedingdevice->setType('info');
                $feedingdevice->setSubType('string');
                $feedingdevice->setLogicalId('pet.feedingdevice');
                $feedingdevice->save();
                // Poids repas bol 0
                $feedingweight0 = $this->getCmd(null, 'pet.feedingweight0');
                if (!is_object($feedingweight0)) {
                    $feedingweight0 = new surepetcareCmd();
                    $feedingweight0->setIsVisible(0);
                    $feedingweight0->setUnite('g');
                    $feedingweight0->setName(__('Poids bol 1', __FILE__));
                    $feedingweight0->setConfiguration('historizeMode', 'none');
                    $feedingweight0->setIsHistorized(1);
                }
                $feedingweight0->setDisplay('generic_type', 'DONT');
                $feedingweight0->setEqLogic_id($this->getId());
                $feedingweight0->setType('info');
                $feedingweight0->setSubType('numeric');
                $feedingweight0->setLogicalId('pet.feedingweight0');
                $feedingweight0->save();
                // Poids repas bol 1
                $feedingweight1 = $this->getCmd(null, 'pet.feedingweight1');
                if (!is_object($feedingweight1)) {
                    $feedingweight1 = new surepetcareCmd();
                    $feedingweight1->setIsVisible(0);
                    $feedingweight1->setUnite('g');
                    $feedingweight1->setName(__('Poids bol 2', __FILE__));
                    $feedingweight1->setConfiguration('historizeMode', 'none');
                    $feedingweight1->setIsHistorized(1);
                }
                $feedingweight1->setDisplay('generic_type', 'DONT');
                $feedingweight1->setEqLogic_id($this->getId());
                $feedingweight1->setType('info');
                $feedingweight1->setSubType('numeric');
                $feedingweight1->setLogicalId('pet.feedingweight1');
                $feedingweight1->save();
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
        $replace['#' . $cmd->getLogicalId() . '_unite#'] = $cmd->getUnite();
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
        log::add('surepetcare','debug','Replace ' . print_r($replace, true));
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
                    $locktime = cache::byKey('surepetcare::lock_time::'.$eqLogic->getId())->getValue();
                    // $locktime = $eqLogic->getConfiguration('lock_time', '');
                    $unlocktime = cache::byKey('surepetcare::unlock_time::'.$eqLogic->getId())->getValue();
                    // $unlocktime = $eqLogic->getConfiguration('unlock_time', '');
                    if ($locktime != '' && $unlocktime != '') {
                        $parameters[$keyValue[0]] = array(
                            'enabled' => true,
                            'lock_time' => $eqLogic::formatTime($locktime),
                            'unlock_time' => $eqLogic::formatTime($unlocktime)
                        );
                    } else {
                        log::add('surepetcare','error','Il faut fixer les heures de début et de fin de couvre-feu par les commandes');
                        throw new Exception(__('Heures de couvre-feu incorrectes', __FILE__));
                    }
                } else {
                    $parameters[$keyValue[0]] = array('enabled' => false);
                }
                if ($eqLogic->getConfiguration('product_id') == 6) {
                    // For the Microchip cat door connect curfew is now an array of curfews.
                    $parameters[$keyValue[0]] = array(0 => $parameters[$keyValue[0]]);
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
            } else if($keyValue[0] =='setlocktime'){
                log::add('surepetcare','debug','Heure de verrouillage : ' . $parameters[$keyValue[0]]);
                cache::set('surepetcare::lock_time::'.$eqLogic->getId(), $parameters[$keyValue[0]], '');
                // $eqLogic->setConfiguration('lock_time', $parameters[$keyValue[0]]);
                $eqLogic->save();
                return;
            } else if($keyValue[0] =='setunlocktime'){
                log::add('surepetcare','debug','Heure de déverrouillage : ' . $parameters[$keyValue[0]]);
                cache::set('surepetcare::unlock_time::'.$eqLogic->getId(),$parameters[$keyValue[0]], '');
                // $eqLogic->setConfiguration('unlock_time', $parameters[$keyValue[0]]);
                $eqLogic->save();
                return;
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
