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
  public function request($_household,$_request = '',$_data = null,$_type='POST', $_headers = array()){
    if(!is_array($_household)){
      $households = config::byKey('households','surepetcare',array());
      foreach ($households as $household) {
        if($_household == $household['id']){
          $_household = $household;
          break;
        }
      }
    }
    if(!is_array($_household)){
      throw new \Exception(__('Impossible de trouver le foyer',__FILE__));
    }
    $url = 'https://app.api.surehub.io/api';
    if($_request != ''){
      $url .= '/'.$_household['id'];
    }
    $url .= $_request;
    log::add('surepetcare','debug','url='.$url);
    log::add('surepetcare','debug','request='.$_request);
    log::add('surepetcare','debug', 'data='.json_encode($_data));
    $request_http = new com_http($url);
    $request_http->setNoSslCheck(true);
    $requestHeaders = array(
            'Connection: keep-alive',
            'Origin: https://surepetcare.io',
            'Referer: https://surepetcare.io/',
    );
    if(count($_headers) > 0) {
            $requestHeaders = array_merge($requestHeaders, $_headers);
    }
    log::add('surepetcare','debug','headers='.$requestHeaders);
    $request_http->setHeader($requestHeaders);
    $request_http->setUserAgent('Mozilla/5.0 (Linux; Android 7.0; SM-G930F Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/64.0.3282.137 Mobile Safari/537.36');
    if($_data !== null){
      if($_type == 'POST'){
        $request_http->setPost(json_encode($_data));
      }elseif($_type == 'PUT'){
        $request_http->setPut(json_encode($_data));
      }
    }
    $result = $request_http->exec();
    log::add('surepetcare','debug','result='.$result);
    $result = is_json($result, $result);
    if(isset($result[0]) && is_array($result[0]) && isset($result[0]['error']) && is_array($result[0]['error'])){
      throw new \Exception(__('Erreur lors de la requete : ',__FILE__).$url.'('.$_type.'), data : '.json_encode($_data).' erreur : '.$result[0]['error']['type'].' => '.$result[0]['error']['description']);
    }
    if(isset($result[0]['success'])){
      return $result[0]['success'];
    }
    return $result;
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
  /*  foreach ($households as $household) {
        $result = surepetcare::request($household, '/device', null, 'GET', array('Authorization: Bearer ' . $token));
        log::add('surepetcare','debug','GetDevices result : '.$result);
    } */
        
  }

  public static function findProduct($_device,$_gatewayId) {
    $create = false;
    $deviceIdList = explode('-', $_device['uniqueid'],2);
    $eqLogic = self::byLogicalId($deviceIdList[0], 'surepetcare');
    if(!is_object($eqLogic)){
      event::add('jeedom::alert', array(
        'level' => 'warning',
        'page' => 'surepetcare',
        'message' => __('Nouveau module detecté', __FILE__),
      ));
      $create = true;
      $eqLogic = new surepetcare();
      $eqLogic->setName($_device['name']);
    }
    $eqLogic->setEqType_name('surepetcare');
    $eqLogic->setIsEnable(1);
    $eqLogic->setLogicalId($deviceIdList[0]);
    $eqLogic->setConfiguration('gateway', $_gatewayId);
    if(isset($_device['category'])){
      $eqLogic->setConfiguration('category', $_device['category']);
    }
    if(isset($_device['swversion'])){
      $eqLogic->setConfiguration('swversion', $_device['swversion']);
    }
    if(isset($_device['product_id'])){
      $eqLogic->setConfiguration('product_id', $_device['product_id']);
    }
    if(isset($_device['manufacturername'])){
      $eqLogic->setConfiguration('manufacturername', $_device['manufacturername']);
    }
    $eqLogic->setConfiguration('surepetcare_id', $_device['surepetcare_id']);
    $types = $eqLogic->getConfiguration('types',array());
    if (!in_array($_device['type'],$types)){
      $types[]=$_device['type'];
    }
    if ($eqLogic->getConfiguration('iconProduct','') == ''){
      $eqLogic->setConfiguration('iconProduct',$eqLogic->getProductList($types,true));
    }
    $eqLogic->setConfiguration('types', $types);
    $eqLogic->save();
    if(file_exists(__DIR__.'/../config/types/device'.$_device['type'].'.json')){
      $types = json_decode(file_get_contents(__DIR__.'/../config/types/device'.$_device['type'].'.json'),true);
      $link_cmds = array();
      foreach ($types['commands'] as $type) {
        $cmd = $eqLogic->getCmd(null,$deviceIdList[1].'.'.$type['logicalId']);
        if(is_object($cmd)){
          continue;
        }
        $cmd = new surepetcareCmd();
        utils::a2o($cmd,$type);
        $cmd->setLogicalId($deviceIdList[1].'.'.$type['logicalId']);
        $cmd->setEqLogic_id($eqLogic->getId());
        $cmd->save();
        if (isset($type['value'])) {
          $link_cmds[$cmd->getId()] = $type['value'];
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
    log::add('surepetcare', 'debug', 'applyModuleConfiguration import');
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


