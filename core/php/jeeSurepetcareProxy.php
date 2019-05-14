<?php
/* This file is part of Plugin openzwave for jeedom.
*
* Plugin openzwave for jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Plugin openzwave for jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Plugin openzwave for jeedom. If not, see <http://www.gnu.org/licenses/>.
*/
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');
log::add('surepetcare','debug','jeeSurepetcareProxy');
if (!isConnect('admin')) {
  echo '401 - Accès non autorisé';
  die();
}
ajax::init();
try {
  $data = init('data',null);
  if($data != null){
    $data = json_decode($data,true);
  }
  $result = surepetcare::request(init('gateway'),str_replace('//', '/', init('request')),$data,init('type','POST'));
  log::add('surepetcare','debug',json_encode($result,true));
  echo json_encode(array('state' => 'ok','result' => $result));
} catch (Exception $e) {
  http_response_code(500);
  die($e->getMessage());
}
