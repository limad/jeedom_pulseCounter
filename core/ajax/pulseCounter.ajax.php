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
  	if (init('action') == 'jsonToHist') {
      	$cmd_id = init('cmd_id');
      	$cmd = cmd::byId($cmd_id);
      	
        if (!is_object($cmd)) {
            throw new Exception(__("Unknown cmd : $cmd_id ", __FILE__) . init('cmd_id'));
        }
  		if (!$cmd->getIsHistorized()) {
            throw new Exception(__("La commande cible $cmd_id n'est pas historisée ", __FILE__) . init('cmd_id'));
        }
      	$targetSubType = $cmd->getSubType();
      	$data = init('data');
        $histories = json_decode($data, true);
      	log::add('pulseCounter', 'warning', __FUNCTION__ . "  histories: ".$histories);
      	ksort($histories);
      	if (!$histories || !is_array($histories)) {
            throw new Exception(__("Le json des données est invalide $histories Impossible de lire les données ", __FILE__) . init('cmd_id'));
        }
      	$firstDate = array_key_first($histories);
      	$lastDate = array_key_last($histories);
      	log::add('pulseCounter', 'warning', __FUNCTION__ . " import histories: from: ". date('Y-m-d H:i:s', strtotime($firstDate)) 
                 . " to " .date('Y-m-d H:i:s', strtotime($lastDate)) );
      	
      	$now = strtotime('now');
      	$c = 0;
        foreach ($histories as $date => $value) {
            $time = strtotime($date);
            if( $now - $time > 315619200){
                throw new Exception(__("Les données remontent à plus de dix ans, c'est trop pour moi ! ", __FILE__) . $date);
            }
            $cmd->event($value, date('Y-m-d H:i:s', $time));
          	$c++;
        }
      	$msg = count($histories).'/'.$c;
      	ajax::success($msg);
    }
    
	if (init('action') == 'HistToJson') {
		/*$eqLogic = pulseCounter::byId(init('id'));
      	if (!is_object($eqLogic)) {
			throw new Exception(__('Equipement pulseCounter non trouvé : ', __FILE__) . init('eqLogic_id'));
		}*/
      	$cmd_id = init('cmd_id');
      	$cmd = cmd::byId($cmd_id);
      	
        if (!is_object($cmd)) {
            log::add('pulseCounter', 'warning', __FUNCTION__ . '  unknown cmd : '.$cmd_id);
          	throw new Exception(__('Unknown cmd : '.$cmd_id, __FILE__) . init('eqLogic_id'));
        }
  		if (!$cmd->getIsHistorized()) {
            log::add('pulseCounter', 'warning', __FUNCTION__ . "  Cette commande $cmd_id n'est pas historisée");
          	throw new Exception(__("  Cette commande $cmd_id n'est pas historisée", __FILE__) . init('eqLogic_id'));
        }
		$histories = $cmd->getHistory();
  		if (!is_array($histories)) {
			$histories = array($histories);
		}
  		$return = [];
  		foreach ($histories as $history) {
          	$hist_time = $history->getDatetime();
          	$hist_value = $history->getValue();
          	if($hist_time && $hist_value){
              	$return[$hist_time] = round($hist_value, 2);
              	//echo "<br>$hist_time => $hist_value";
            }
        }
      	ajax::success(json_encode($return, JSON_PRETTY_PRINT));
	}
  
  	if (init('action') == 'SyncCmds') {
		$eqLogic = pulseCounter::byId(init('id'));
      	if (!is_object($eqLogic)) {
			throw new Exception(__('Equipement pulseCounter non trouvé : ', __FILE__) . init('eqLogic_id'));
		}
      	$return = $eqLogic->makeCmd();
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
        $eqLogic = pulseCounter::byId(init('eqLogic_id'));
		if (!is_object($eqLogic)) {
			throw new Exception(__('Equipement pulseCounter non trouvé : ', __FILE__) . init('eqLogic_id'));
		}
		foreach($datas as $values){
          	$start = strtotime($values['date_debut']);
            $end = strtotime($values['date_fin']);
          	$cmd_conso = $eqLogic->getCmd(null, 'conso_jour');
          	$cmd_vol = $eqLogic->getCmd(null, 'vol_jour');
          	$cmd_coef = $eqLogic->getCmd(null, 'coef_conv');
          
          	foreach($values as $cmdLogId => $value){
            	if ($cmdLogId != 'vol_jour' && $cmdLogId != 'conso_jour') continue;//
              	$start = strtotime($values['date_debut']);
            	$end = strtotime($values['date_fin']);
                $coef = $values['coef_conv']/100;
          		if ($cmdLogId == 'vol_jour') $value = $value*1000;// to litre
              	if ($cmdLogId == 'conso_jour') $value = $value*1000;//to Wh
            	if ($value != '' && !is_nan($value)) {
				$cmd = $eqLogic->getCmd(null, $cmdLogId);
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
                $eqLogic->cleanData($cmdLogId);
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
		
      	$eqLogic = pulseCounter::byId(init('eqLogic_id'));
		if (!is_object($eqLogic)) {
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
				$cmd = $eqLogic->getCmd(null, $cmdLogId);
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
                $eqLogic->cleanData($cmdLogId);
            }
        }
      
      
      
      
      
      
      
      
      /*
		if ($values['consumption'] != 0 && !is_nan($values['consumption'])) {
			$consumptionByDay = round(($values['consumption'] / ($end - $start)) * 3600 * 24, 2);
			$current = $start;
			$consumption = $eqLogic->getCmd(null, 'consumption');
			while ($current <= $end) {
				history::removes($consumption->getId(), date('Y-m-d H:i:s', $current), date('Y-m-d H:i:s', $current + 86399));
				$consumption->addHistoryValue($consumptionByDay, date('Y-m-d H:i:s', $current));
				$current += 3600 * 24;
			}
			$consumption->execCmd();
			if (strtotime($consumption->getCollectDate()) < $end || strtotime($consumption->getCollectDate()) == strtotime('now')) {
				$consumption->event($consumptionByDay, date('Y-m-d H:i:s', $end));
			}
			$eqLogic->cleanData('consumption');
		}
		if ($values['cost'] != 0 && !is_nan($values['cost'])) {
			$costByDay = round(($values['cost'] / ($end - $start)) * 3600 * 24, 2);
			$current = $start;
			$cost = $eqLogic->getCmd(null, 'cost');
			while ($current <= $end) {
				history::removes($cost->getId(), date('Y-m-d H:i:s', $current), date('Y-m-d H:i:s', $current + 86399));
				$cost->addHistoryValue($costByDay, date('Y-m-d H:i:s', $current));
				$current += 3600 * 24;
			}
			$cost->execCmd();
			if (strtotime($cost->getCollectDate()) < $end || strtotime($cost->getCollectDate()) == strtotime('now')) {
				$cost->event($costByDay, date('Y-m-d H:i:s', $end));
			}
			$eqLogic->cleanData('cost');
		}
        */
		$eqLogic->setComment($values['comment']);
		$eqLogic->save();
		ajax::success();
	}
	
	throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayException($e), $e->getCode());
}