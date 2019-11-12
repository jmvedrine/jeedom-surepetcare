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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function surepetcare_install() {
    config::save('autorefresh', '* * * * *', 'surepetcare');
}

function surepetcare_update() {
    log::add('surepetcare', 'debug', 'Surepetcare update function');
    $autorefresh = config::byKey('autorefresh','surepetcare');
    if($autorefresh =='') {
        config::save('autorefresh', '* * * * *', 'surepetcare');
    }
    foreach (surepetcare::byType('surepetcare') as $eqLogic) {
        if ($eqLogic->getConfiguration('type') == 'device') {
            if ($eqLogic->getConfiguration('device_id') == 3) {
                // Suppress pofile commands for the Microchip pet door connect.
                $cmd = $eqLogic->getCmd(null, 'dev.profile::2');
                if (is_object($cmd)) {
                    $cmd->remove();
                }
                $cmd = $eqLogic->getCmd(null, 'dev.profile::3');
                if (is_object($cmd)) {
                    $cmd->remove();
                }
            }
            foreach ($eqLogic->getCmd('info') as $cmd) {
                $logicalId = $cmd->getLogicalId();
                log::add('surepetcare', 'debug', 'On traite la commande  logicalId ' . $logicalId);
                $epClusterPath = explode('.', $logicalId);
                $path = explode('::', $epClusterPath[1]);
                if ($path[0] != 'status' && $path[0] != 'control') {
                    log::add('surepetcare', 'debug', 'logicalId à mettre à jour');
                    if ($path[0] == 'curfew' {
                        $newpath = 'control';
                    } else {
                        $newpath = 'status';
                    }
                    $newlogicalId = $epClusterPath[0] . '.' . $newpath .  '::' . $epClusterPath[1];
                }
                log::add('surepetcare', 'debug', 'Mise à jour logicalId ' . $logicalId . ' nouvelle valeur ' . $newlogicalId);
                $cmd->setLogicalId($newlogicalId);
                $cmd->save();
            }
        }
		$eqLogic->save();
	}
}


function surepetcare_remove() {
    
}

?>
