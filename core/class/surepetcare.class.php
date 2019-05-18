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



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */
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
            throw new \Exception(__('Erreur lors de la requete : ',__FILE__).$url.'('.$method.'), data : '.json_encode($payload).' erreur : ' . $code);
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

    log::add('surepetcare','debug', 'device id='.$device_id);
    $data = array(
            'email_address' => $mailadress,
            'password' => $password,
            'device_id' => $device_id
    );
    $json = json_encode($data);
    log::add('surepetcare','debug', 'login data='.$json);
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
            $userid = $result['data']['user']['id'];
            return $token;
    }
        return false;
  }

  public static function getHouseholds($token){
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
    log::add('surepetcare', 'debug', 'Fonction sync appelee');
    $token = surepetcare::login();
    log::add('surepetcare','debug','dans login token='.$token);
    surepetcare::getHouseholds($token);
    $households = config::byKey('households','surepetcare',array());
    foreach ($households as $household) {
        $result = surepetcare::request('https://app.api.surehub.io/api/household/'. $household['id'].'/device', null, 'GET', array('Authorization: Bearer ' . $token));
        log::add('surepetcare','debug','GetDevices result : '.json_encode($result));

        if(isset($result['data'])) {
            $devices = $result['data'];
            foreach ($devices as $key => $device) {
                log::add('surepetcare','debug','Device '.$key. '='.json_encode($device));
                if(!isset($device['id']) || !isset($device['product_id'])){
                    log::add('surepetcare','debug','Missing device id or product id');
                    continue;
                }
                $found_eqLogics[] = self::findProduct($device,$household['id']);
                log::add('surepetcare','debug',json_encode($found_eqLogics));
            }
        }
    }

  }

  public function applyData($_data) {
    $updatedValue = false;
    if(!isset($_data['uniqueid'])){
      return $updatedValue;
    }

    $deviceIdList = explode('-', $_data['uniqueid'],2);
    foreach ($this->getCmd('info') as $cmd) {
      $logicalId = $cmd->getLogicalId();
      if ($logicalId == '') {
        continue;
      }
      $epClusterPath = explode('.', $logicalId);
      if ($epClusterPath[0] != $deviceIdList[1]) {
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
        $this->checkAndUpdateCmd($cmd,$value);
        $updatedValue = true;
      }
    }
    if(isset($_data['config'])) {
      $updatedValue = true;
      if ( isset($_data['config']['battery'])){
        $this->batteryStatus($_data['config']['battery']);
      }
    }
    return $updatedValue;
  }
  public static function findProduct($_device,$_householdid) {
    $create = false;
    $eqLogic = self::byLogicalId($_device['id'], 'surepetcare');
    if(!is_object($eqLogic)){
       log::add('surepetcare','debug','new device '.$_device['id']);
      event::add('jeedom::alert', array(
        'level' => 'warning',
        'page' => 'surepetcare',
        'message' => __('Nouveau produit detecté', __FILE__),
      ));
      $create = true;
      $eqLogic = new surepetcare();
      $eqLogic->setName($_device['name']);
    }
    $eqLogic->setEqType_name('surepetcare');
    $eqLogic->setIsEnable(1);
    $eqLogic->setLogicalId($_device['id']);
    $eqLogic->setConfiguration('household_id', $_householdid);
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
      $eqLogic->save();
      $link_cmds = array();
      foreach ($products['commands'] as $product) {
         log::add('surepetcare','debug','Commande : '.json_encode($product));
        $cmd = $eqLogic->getCmd(null,$deviceIdList[1].'.'.$product['logicalId']);
        if(is_object($cmd)){
          continue;
        }
        $cmd = new surepetcareCmd();
        utils::a2o($cmd,$product);
        $cmd->setLogicalId($deviceIdList[1].'.'.$product['logicalId']);
        $cmd->setEqLogic_id($eqLogic->getId());
        $cmd->save();
        if (isset($product['value'])) {
          $link_cmds[$cmd->getId()] = $product['value'];
        }
      }
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
    if($create){
      event::add('surepetcare::includeDevice', $eqLogic->getId());
    }
    return $eqLogic;
  }

  public static function devicesParameters($_device = '') {
    log::add('surepetcare', 'debug', 'debut de devicesParameters');
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
    /*     * *********************Méthodes d'instance************************* */
  public function postSave() {
    log::add('surepetcare', 'debug', 'debut de postSave');
    if ($this->getConfiguration('applyProductId') != $this->getConfiguration('product_id')) {
      log::add('surepetcare', 'debug', 'postSave envoi vers applyModuleConfiguration');
      $this->applyModuleConfiguration();
    }
  }

  public function getImage() {
    return 'plugins/surepetcare/core/config/images/' . $this->getConfiguration('iconProduct');
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
    log::add('surepetcare', 'debug', 'applyModuleConfiguration import' . print_r($device));
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
      public function toHtml($_version = 'dashboard') {

      }
     */

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

    public function execute($_options = array()) {

    }

    /*     * **********************Getteur Setteur*************************** */
}
