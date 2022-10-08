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

try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');
	
	if (!isConnect()) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}
	
	ajax::init();
	if (init('action') == 'SyncCmds') {
		$pulseCounter = pulseCounter::byId(init('id'));
      	if (!is_object($pulseCounter)) {
			throw new Exception(__('Equipement energie non trouvé : ', __FILE__) . init('eqLogic_id'));
		}
      	$return = $pulseCounter->makeCmd();
      	ajax::success($return);
	}
  
	if (init('action') == 'getPanel') {
		$period = init('period');
		if (trim($period) == '') {
			$period = config::byKey('savePeriod', 'pulseCounter');
		}
		$return = pulseCounter::generatePanel('mobile', $period);
		ajax::success($return);
	}
	
	
  	if (init('action') == 'manualValue') {
      	$configFile = __DIR__  . '/../../desktop/php/gazJour.json';
        if (!file_exists($configFile)) {
            throw new Exception(__(' Fichier de configuration introuvable ! ', __FILE__) . init('action'));
        }
      	$datas = json_decode(file_get_contents($configFile), true);
        if(!$datas) throw new Exception(__('Fichier de configuration inexploitable !', __FILE__));
        $pulseCounter = pulseCounter::byId(init('eqLogic_id'));
		if (!is_object($pulseCounter)) {
			throw new Exception(__('Equipement energie non trouvé : ', __FILE__) . init('eqLogic_id'));
		}
		foreach($datas as $values){
          	$start = strtotime($values['date_debut']);
            $end = strtotime($values['date_fin']);
          	$cmd_conso = $pulseCounter->getCmd(null, 'conso_jour');
          	$cmd_vol = $pulseCounter->getCmd(null, 'vol_jour');
          	$cmd_coef = $pulseCounter->getCmd(null, 'coef_conv');
          
          	foreach($values as $cmdLogId => $value){
            	if ($cmdLogId != 'vol_jour' && $cmdLogId != 'conso_jour') continue;//
              	$start = strtotime($values['date_debut']);
            	$end = strtotime($values['date_fin']);
                $coef = $values['coef_conv']/100;
          		if ($cmdLogId == 'vol_jour') $value = $value*1000;// to litre
              	if ($cmdLogId == 'conso_jour') $value = $value*1000;//to Wh
            	if ($value != '' && !is_nan($value)) {
				$cmd = $pulseCounter->getCmd(null, $cmdLogId);
                /*if (!is_object($cmd)) {
                    //log::add('pulseCounter', 'warning',__FUNCTION__ . ' Unknown cmd: '. $cmdLogId);
                  	continue;
                }*/
              	//log::add('pulseCounter', 'debug',__FUNCTION__ . ' cmd: '. $cmdLogId .'=>'.$value.' du '.$start.' au '.$end);
              	//continue;
                $nbday = (($end - $start) /86400) +1;
              	$valueByDay = round($value / $nbday , 2);
                $current = $start;
                //log::add('pulseCounter', 'debug',__FUNCTION__ . " cmd: $cmdLogId $value : ".$nbday .' => '.$valueByDay);
                //continue;
                while ($current <= $end) {
                    history::removes($cmd->getId(),  date('Y-m-d 00:00:00', $current), date('Y-m-d 23:59:59', $current));
                    $cmdDate = date('Y-m-d 00:00:00', $current);
                  	//$cmdDate = date('Y-m-d H:i:s', $current);
                  	$cmd->addHistoryValue($valueByDay, $cmdDate);
                  	//log::add('pulseCounter', 'debug',__FUNCTION__ . ' cmd: '. $cmdLogId .': '.$cmdDate .' => '.$valueByDay);
              	
                    $current += 86400;
                }
                //$cmd->execCmd();
                if (strtotime($cmd->getCollectDate()) < $end || strtotime($cmd->getCollectDate()) == strtotime('now')) {
                    $cmd->event($valueByDay, date('Y-m-d H:i:s', $end));
                }
                $pulseCounter->cleanData($cmdLogId);
            }
          }
        }
      
      
      
      
      ajax::success();
    }  
	
  	if (init('action') == 'setEqConfig') {
      	$eqLogic = pulseCounter::byId(init('eqLogic_id'));
      	if (!is_object($eqLogic)) {
			throw new Exception(__('Equipement pulseCounter non trouvé : ', __FILE__) . init('eqLogic_id'));
		}
      	$config = init('dataj');
      	log::add('pulseCounter', 'debug', "Ajax::setEqConfig ".$dataj);
        $i=0;
      	foreach ($config as $configKey=>$value){
			if($configKey != ""){
				log::add('pulseCounter', 'debug', "Ajax::setEqConfig [".$eqLogic->getName()."] : ". $configKey.'=>'.$value );
				$eqLogic->setConfiguration($configKey, $value);
				$i++;
			}
        }
      	if($i>0){ 
          	$eqLogic->save();
        	ajax::success();
      	}else throw new Exception(__('Ajax::setEqConfig ['.$eqLogic->getName().'] Echec parametre manquant: ', __FILE__));
    }
  
  	if (init('action') == 'setHistlValue') {
      	log::add('pulseCounter', 'debug', 'Ajax::manualValue2 values: '. init('values').' -- dates: '. init('dates') );
		
      	$pulseCounter = pulseCounter::byId(init('eqLogic_id'));
		if (!is_object($pulseCounter)) {
			throw new Exception(__('Ajax::Equipement pulseCounter non trouvé : ', __FILE__) . init('eqLogic_id'));
		}
		$values = json_decode(init('values'), true);
		$dates = json_decode(init('dates'), true);
		$start = strtotime($dates['start']);
		if ($start === false) {
			throw new Exception(__('Date de début invalide', __FILE__));
		}
		$end = strtotime($dates['end']);
		if ($end === false) {
			throw new Exception(__('Date de fin invalide', __FILE__));
		}
		$duration = $end - $start;
      	foreach($values as $cmdLogId => $value){
          	if ($value != '' && !is_nan($value)) {
				$cmd = $pulseCounter->getCmd(null, $cmdLogId);
                if (!is_object($cmd)) {
                    log::add('pulseCounter', 'warning', 'Ajax::manualValue2 Unknown cmd: '. $cmdLogId );
                  	continue;
                }
              	log::add('pulseCounter', 'debug', 'Ajax::manualValue2 cmd: '. $cmdLogId .'=>'.$value);
                $valueByDay = round(($value / ($end - $start)) * 3600 * 24, 2);
                $current = $start;
                while ($current <= $end) {
                    history::removes($cmd->getId(), date('Y-m-d H:i:s', $current), date('Y-m-d H:i:s', $current + 86399));
                    $cmdDate = date('Y-m-d H:i:s', $current);
                    $cmd->addHistoryValue($valueByDay, $cmdDate);
                  	log::add('pulseCounter', 'debug', 'Ajax::manualValue2 cmd: '. $cmdLogId .': '.$cmdDate .'=>'.$valueByDay);
                
                    $current += 3600 * 24;
                }
                //$cmd->execCmd();
                if (strtotime($cmd->getCollectDate()) < $end || strtotime($cmd->getCollectDate()) == strtotime('now')) {
                    $cmd->event($valueByDay, date('Y-m-d H:i:s', $end));
                }
                $pulseCounter->cleanData($cmdLogId);
            }
        }
      
      
      
      
      
      
      
      
      /*
		if ($values['consumption'] != 0 && !is_nan($values['consumption'])) {
			$consumptionByDay = round(($values['consumption'] / ($end - $start)) * 3600 * 24, 2);
			$current = $start;
			$consumption = $pulseCounter->getCmd(null, 'consumption');
			while ($current <= $end) {
				history::removes($consumption->getId(), date('Y-m-d H:i:s', $current), date('Y-m-d H:i:s', $current + 86399));
				$consumption->addHistoryValue($consumptionByDay, date('Y-m-d H:i:s', $current));
				$current += 3600 * 24;
			}
			$consumption->execCmd();
			if (strtotime($consumption->getCollectDate()) < $end || strtotime($consumption->getCollectDate()) == strtotime('now')) {
				$consumption->event($consumptionByDay, date('Y-m-d H:i:s', $end));
			}
			$pulseCounter->cleanData('consumption');
		}
		if ($values['cost'] != 0 && !is_nan($values['cost'])) {
			$costByDay = round(($values['cost'] / ($end - $start)) * 3600 * 24, 2);
			$current = $start;
			$cost = $pulseCounter->getCmd(null, 'cost');
			while ($current <= $end) {
				history::removes($cost->getId(), date('Y-m-d H:i:s', $current), date('Y-m-d H:i:s', $current + 86399));
				$cost->addHistoryValue($costByDay, date('Y-m-d H:i:s', $current));
				$current += 3600 * 24;
			}
			$cost->execCmd();
			if (strtotime($cost->getCollectDate()) < $end || strtotime($cost->getCollectDate()) == strtotime('now')) {
				$cost->event($costByDay, date('Y-m-d H:i:s', $end));
			}
			$pulseCounter->cleanData('cost');
		}
        */
		$pulseCounter->setComment($values['comment']);
		$pulseCounter->save();
		ajax::success();
	}
	
	throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayException($e), $e->getCode());
}