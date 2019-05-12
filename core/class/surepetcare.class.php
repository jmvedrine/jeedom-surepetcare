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
require_once dirname(__FILE__) . '/../../3rdparty/SurePetCareClient.php';

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

  public static function sync(){
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
    if(file_exists(__DIR__.'/../config/types/'.$_device['type'].'.json')){
      $types = json_decode(file_get_contents(__DIR__.'/../config/types/'.$_device['type'].'.json'),true);
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
    $return = array();
    foreach (ls(dirname(__FILE__) . '/../config/devices', '*') as $dir) {
      $path = dirname(__FILE__) . '/../config/devices/' . $dir;
      if (!is_dir($path)) {
        continue;
      }
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
    if ($this->getConfiguration('applyProductId') != $this->getConfiguration('product_id')) {
      $this->applyModuleConfiguration();
    }
  }

  public function getImage() {
    return 'plugins/surepetcare/core/config/images/' . $this->getConfiguration('iconProduct');
  }

  public function applyModuleConfiguration() {
    $this->setConfiguration('applyProductId', $this->getConfiguration('product_id'));
    $this->save();
    if ($this->getConfiguration('product_id') == '') {
      return true;
    }

    $device = self::devicesParameters($this->getConfiguration('product_id'));
    if (!is_array($device)) {
      return true;
    }
    $this->import($device);
  }

  public function applyData($_data) {
    $updatedValue = false;
    if(!isset($_data['uniqueid'])){
      return $updatedValue;
    }
    $path_file = __DIR__.'/../config/devices/'.$this->getConfiguration('product_id').'.php';
    if(file_exists($path_file)){
      require_once $path_file;
      $function = 'surepetcare_' . str_replace('.','_',$this->getConfiguration('product_id')).'_data';
      if (function_exists($function)) {
        $function($_data);
      }
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

  public function getProductList($_eqTypes = array(), $_giveOne = false) {
    if (count($_eqTypes) == 0){
      $eqTypes = $this->getConfiguration('types',array());
    } else {
      $eqTypes = $_eqTypes;
    }
    $currentIcon = $this->getConfiguration('iconProduct','');
    $productList = array();
    $files = ls(dirname(__FILE__) . '/../config/images', '*.png', false, array('files', 'quiet'));
    foreach ($files as $file) {
      $fileTypesTitle = explode('_',$file);
      $fileTypes = explode('&',$fileTypesTitle[0]);
      $numTypes = 0;
      foreach ($fileTypes as $fileType) {
        if (in_array($fileType,$eqTypes)){
          $numTypes += 1;
        }
      }
      if ($numTypes == count($fileTypes)){
        if ($_giveOne) {
          return $file;
        }
        $productList[substr($fileTypesTitle[1],0,-4)]['file'] = $file;
        $productList[substr($fileTypesTitle[1],0,-4)]['selected'] = 0;
        if ($file == $currentIcon) {
          $productList[substr($fileTypesTitle[1],0,-4)]['selected'] = 1;
        }
      }
    }
    if ($_giveOne && count($productList) == 0) {
      return '';
    }
    return $productList;
  }
    /*     * *********************Méthodes d'instance************************* */


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

    public function execute($_options = null) {
      if ($this->getType() != 'action') {
        return;
      }
      $eqLogic = $this->getEqLogic();
      $logicalId = $this->getLogicalId();
      $actionDatas = explode('.',$logicalId);
      $actionerId = $eqLogic->getLogicalId() . '-' . $actionDatas[0];
      $parameters = array();
      $datasList = explode(';',$actionDatas[1]);
      foreach ($datasList as $datas){
        $keyValue = explode('::',$datas);
        $type = self::datatype($keyValue[0]);
        if ($type == 'bool'){
          $parameters[$keyValue[0]] = ($keyValue[1] == '0') ? false : true;
        } else {
          $parameters[$keyValue[0]] = $keyValue[1];
        }
      }
      // TODO envoyer au serveur.
    }

    /*     * **********************Getteur Setteur*************************** */
}


