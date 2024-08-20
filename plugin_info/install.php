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
    $autorefresh = config::byKey('autorefresh','surepetcare');
    if($autorefresh =='') {
        config::save('autorefresh', '* * * * *', 'surepetcare');
    }
    foreach (surepetcare::byType('surepetcare') as $eqLogic) {
        if ($eqLogic->getConfiguration('type') == 'device' && $eqLogic->getConfiguration('product_id') != 6) {
            $cmd = $eqLogic->getCmd(null, 'dev.forbidden');
            if (is_object($cmd)) {
                $cmd->remove();
                $eqLogic->save();
            }
        } else if ($eqLogic->getConfiguration('type') == 'pet') {
			$position = $eqLogic->getCmd(null, 'pet.position');
			// Fixer la position à l'extérieur (action)
			$setindoor_Off = $eqLogic->getCmd(null, 'pet.setindoor_Off');
			if (!is_object($setindoor_Off)) {
				$setindoor_Off = new surepetcareCmd();
				$setindoor_Off->setName(__('Interieur Off', __FILE__));
				$setindoor_Off->setIsVisible(1);
			}
			$setindoor_Off->setDisplay('generic_type', 'DONT');
			$setindoor_Off->setEqLogic_id($eqLogic->getId());
			$setindoor_Off->setType('action');
			$setindoor_Off->setSubType('other');
			$setindoor_Off->setLogicalId('pet.setindoor_Off');
			$setindoor_Off->setValue($position->getId());
			$setindoor_Off->setTemplate('dashboard', 'surepetcare::position');
			$setindoor_Off->setTemplate('mobile', 'surepetcare::position');
			$setindoor_Off->save();
			
			// Fixer la position à l'intérieur (action)
			$setindoor_On = $eqLogic->getCmd(null, 'pet.setindoor_On');
			if (!is_object($setindoor_On)) {
				$setindoor_On = new surepetcareCmd();
				$setindoor_On->setName(__('Interieur On', __FILE__));
				$setindoor_On->setIsVisible(1);
			}
			$setindoor_On->setDisplay('generic_type', 'DONT');
			$setindoor_On->setEqLogic_id($eqLogic->getId());
			$setindoor_On->setType('action');
			$setindoor_On->setSubType('other');
			$setindoor_On->setLogicalId('pet.setindoor_On');
			$setindoor_On->setValue($position->getId());
			$setindoor_On->setTemplate('dashboard', 'surepetcare::position');
			$setindoor_On->setTemplate('mobile', 'surepetcare::position');
			$setindoor_On->save();
			
			// Inverser la position (action)
			$toggleposition = $eqLogic->getCmd(null, 'pet.toggleposition');
			if (!is_object($toggleposition)) {
				$toggleposition = new surepetcareCmd();
				$toggleposition->setName(__('Inverser la position', __FILE__));
				$toggleposition->setIsVisible(0);
			}
			$toggleposition->setDisplay('generic_type', 'DONT');
			$toggleposition->setEqLogic_id($eqLogic->getId());
			$toggleposition->setType('action');
			$toggleposition->setSubType('other');
			$toggleposition->setLogicalId('pet.toggleposition');
			$toggleposition->setValue($position->getId());
			$toggleposition->save();

			$photolocation = $eqLogic->getCmd(null, 'pet.photolocation');
			if (!is_object($photolocation)) {
				$photolocation = new surepetcareCmd();
				$photolocation->setName(__('Animal', __FILE__));
				$photolocation->setIsVisible(1);
				$photolocation->setConfiguration('historizeMode', 'none');
				$photolocation->setIsHistorized(0);
				$photolocation->setEqLogic_id($eqLogic->getId());
				$photolocation->setType('info');
				$photolocation->setSubType('string');
				$photolocation->setLogicalId('pet.photolocation');
			}
			$photolocation->setTemplate('dashboard', 'surepetcare::petimage');
			$photolocation->setTemplate('mobile', 'surepetcare::petimage');
			$photolocation->save();
			$eqLogic->save();
        }
    }
}

function surepetcare_remove() {
    
}
?>
