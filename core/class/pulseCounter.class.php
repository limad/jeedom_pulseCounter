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
require_once __DIR__ . '/../../../../core/php/core.inc.php';
if (!trait_exists('pulseCounterTrait') && file_exists(__DIR__ . '/pulseCounterTrait.php')) {
	require_once dirname(__FILE__) . '/pulseCounterTrait.php';
}
class pulseCounter extends eqLogic {
  use pulseCounterTrait;
/*     * *************************Attributs****************************** */
	private static $_eqConfig = null;
	public static $_period = [
		'D' => array(
			'name' => 'J',
			'start' => 'midnight',
			'end' => 'now',
		),
		'D-1' => array(
			'name' => 'J-1',
			'start' => '-1 day midnight +1 second',
			'end' => 'today midnight -1 second',
		),
		'W' => array(
			'name' => 'S',
			'start' => 'monday this week midnight',
			'end' => 'now',
		),
		'W-1' => array(
			'name' => 'S-1',
			'start' => 'monday this week midnight -7 days',
			'end' => 'last sunday 23:59:59',
		),
		'M' => array(
			'name' => 'M',
			'start' => 'first day of this month midnight',
			'end' => 'now',
		),
		'M-1' => array(
			'name' => 'M-1',
			'start' => 'first day of previous month midnight',
			'end' => 'last day of previous month 23:59:59',
		),
		'Y' => array(
			'name' => 'A',
			'start' => 'first day of january this year midnight',
			'end' => 'now',
		),
		'Y-1' => array(
			'name' => 'A-1',
			'start' => 'first day of january last year midnight',
			'end' => 'last day of december last year 23:59:59',
		),
	];
/*     * ***********************Methode static*************************** */
/* ************************************************************************** */
	public function testPulse() {
    	log::add('pulseCounter', 'debug',__FUNCTION__ . '  Starting ****************');
		$eqName = $this->getName();
      	$eqId = $this->getId();
    	$countType = $this->getConfiguration('countType');
      if($countType == "instant" ){
        	$testValue = 1;
      }elseif($countType == "total"){
        	$input_pulses_cmd = $this->getCmd('info', 'input_pulses');
      		$input_pulses = $input_pulses_cmd->execCmd();
        	$testValue = $input_pulses + 1;
      }
      $pulse_listener = listener::byClassAndFunction('pulseCounter', 'pulseEvent', array('pulseCounter_id' => $eqId));
      $listener_id =  $pulse_listener->getId();        
      $event = [
            "test" => true,
            "background" => false,
            "pulseCounter_id" => $eqId,
            "event_id" => $cmdId,
            "value" => $testValue,
            "datetime" => date('Y-m-d H:i:s', strtotime('now')), //"2022-08-12 13:01:10",
            "listener_id" => $listener_id
      ];
      self::pulseEvent($event);
    }  
/* ************************************************************************** */
	public function refresh() {
		log::add('pulseCounter', 'debug',__FUNCTION__ . '  Starting ****************');
		$eqName = $this->getName();
      	$pulseRatio = $this->getConfiguration('pulseRatio');
        $indexPulseAdd = $this->getConfiguration('indexPulseAdd');
        log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] pulseRatio: $pulseRatio - indexPulseAdd: $indexPulseAdd");
          
        $input_pulses_cmd = $this->getCmd('info', 'input_pulses');
      	$input_pulses = $input_pulses_cmd->execCmd();
      	$totPulses_cmd = $this->getCmd('info', 'total_pulses');
      	$old_totPulses = $totPulses_cmd->execCmd();
      	
      
      	$new_totPulses = $input_pulses + $indexPulseAdd;
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] input_pulses : ".$input_pulses);
        log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] old_totPulses : ".$old_totPulses);
        log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] new_totPulses : ".$new_totPulses);
          
      	if($new_totPulses == $old_totPulses){
             log::add(__CLASS__, 'debug',__FUNCTION__ . '  aucun changement ');
             return;
        }
        $vol_index =  round($new_totPulses / $pulseRatio , 2);
      	$conso_index = round($vol_index * $coefConv, 2);
      	
      	$cmds_values = [
          'total_pulses'=> $new_totPulses,
          'vol_index' => $vol_index,
          'conso_index'=> $conso_index,
          ];
      	foreach($cmds_values as $cmdLogId => $cmd_value){
			$cmd = $this->getCmd(null, $cmdLogId);
			if (!is_object($cmd) || $cmd_value === false) {
              	continue;
			}
			log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] set cmd:  ".$cmdLogId." to ".$cmd_value);
			if (!$test){
      			$cmd->event($cmd_value, $newValueDate);
			}
		}
      
      
   	}

/* ************************************************************************** */
	public function updateInfos() {
		log::add('pulseCounter', 'debug',__FUNCTION__ . '  Starting ****************');
		 
      	$eqName = $this->getName();
      	$pulseRatio = $this->getConfiguration('pulseRatio');
        $indexPulseAdd = $this->getConfiguration('indexPulseAdd');
      	$coefType = 'coef_'.$this->getConfiguration('coefType');
      	$coefConv = floatval(jeedom::evaluateExpression($this->getConfiguration($coefType)));
        $coefConv = ($coefConv > 0) ? $coefConv : 1;
        log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] pulseRatio: $pulseRatio - indexPulseAdd: $indexPulseAdd - coefConv: $coefConv");
          
        
      	if($countType == "total"){
          	$input_pulses = jeedom::evaluateExpression($eqLogic->getConfiguration('pulse'));
        }else{
          	$input_pulses_cmd = $this->getCmd('info', 'input_pulses');
      		$input_pulses = $input_pulses_cmd->execCmd();
        }
      
      	//$totPulses_cmd = $this->getCmd('info', 'total_pulses');
      	//$old_totPulses = $totPulses_cmd->execCmd();
      	$new_totPulses = $input_pulses + $indexPulseAdd;
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] input_pulses : ".$input_pulses);
        $vol_index = round($new_totPulses / $pulseRatio , 2);
      	$conso_index = round($vol_index * $coefConv, 2);
         
      	$cmds_values =[
          'input_pulses' => $input_pulses,
          'total_pulses' => $new_totPulses,
          'vol_index' => $vol_index,
          'conso_index' => $conso_index,
        ];
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] cmds_values : ".json_encode($cmds_values));
      	foreach($cmds_values as $cmdLogId => $cmd_value){
			$cmd = $this->getCmd(null, $cmdLogId);
			if (!is_object($cmd) || $cmd_value === false  || $cmd_value < 0) {
              	continue;
			}
			//$cmd->event($cmd_value, $newValueDate);
          	$this->checkAndUpdateCmd($cmd, round($cmd_value, 2));
        }
    }
/* ************************************************************************** */
	public static function pulseEvent($_option) {
    	$eqLogic = pulseCounter::byId($_option['pulseCounter_id']);
      	if (!is_object($eqLogic) || $eqLogic->getIsEnable() == 0) {
			return;
		}
      	$eqName = $eqLogic->getName();
      	log::add(__CLASS__, 'info','=> '.__FUNCTION__ . ' Start for: ['.$eqName.']  _option: '.json_encode($_option));
      	$modeTeste ="";
      	$test = false;
      	if (isset($_option['test'])) {
			$test = true;
          	$modeTeste = "test!";
		}
      	$eqConfig = $eqLogic->getConfiguration();
      	$coefConv = floatval(jeedom::evaluateExpression($eqConfig['coef_'.$eqConfig['coefType']]));
      	$coefConv = $coefConv > 0 ? $coefConv : 1;
        $indexPulseAdd = $eqConfig['indexPulseAdd'] ?? 0;
      	$pulseUnitBig = $eqConfig['pulseUnitBig'];//($pulseUnit == "l") ? "m³" : (($pulseUnit == "Wh") ? "kWh" : $pulseUnit);
      	$pulseUnitSmall = $eqConfig['pulseUnitSmall'];//($pulseUnitBig == "kWh") ? "Wh" : "l";
      	$pulseRatio = $eqConfig['pulseRatio'];
      	$countType = $eqConfig['countType'];
      	$totPulses_cmd = $eqLogic->getCmd('info', 'total_pulses');
      	$old_totPulses = $totPulses_cmd->execCmd();
      	$input_pulses_cmd = $eqLogic->getCmd('info', 'input_pulses');
      	$old_input_pulses = $input_pulses_cmd->execCmd();
      	$oldValueDate = ($totPulses_cmd->getValueDate() != "") ? $input_pulses_cmd->getValueDate() : $eqLogic->getConfiguration('createtime');
      
      	if($countType == "instant"){
          	if($_option['value'] == null){
                log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] Nouvelle valeur null => aucun changement : ".$_option['value']);
                return;
            }
          	$new_input_pulses = $old_input_pulses + 1;//
        }
      	else {//$countType == "total"
          	$new_input_pulses = $_option['value'];
      		if (!$test) $new_input_pulses = jeedom::evaluateExpression($eqLogic->getConfiguration('pulse'));
        }
      	$newValueDate = $_option['datetime'];
      	$new_totPulses = $new_input_pulses + $indexPulseAdd;
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] countType: $countType - pulseRatio: $pulseRatio - indexPulseAdd: $indexPulseAdd");
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] old_input_pulses : $old_input_pulses at $oldValueDate");
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] new_input_pulses : $new_input_pulses at $newValueDate");
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] old_totPulses : $old_totPulses");
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] * new_totPulses : $new_totPulses at $newValueDate");
      	
      	if($new_totPulses == $old_totPulses){
			log::add(__CLASS__, 'debug',__FUNCTION__ . '  aucun changement ');
			return;
		}
		elseif($new_totPulses < $old_totPulses){
          	$pulseAdd_date = $eqConfig['pulseAdd_date'];
          	log::add(__CLASS__, 'error',"	".__FUNCTION__ . " [$eqName] pulseAdd_date : $pulseAdd_date -- oldValueDate : $oldValueDate");
          	if(strtotime($pulseAdd_date) > strtotime($oldValueDate)){
              	$new_indexPulse = $indexPulseAdd + $new_totPulses;
              	log::add(__CLASS__, 'error',"	".__FUNCTION__ . " [$eqName] new_totPulses set from: ".$_option['value']. " to : . $new_indexPulse");
				
          		return;
            }else{
              	$err_msg =  " [$eqName] Le nouvel index d'impulsions '$new_totPulses' est inferieur à la valeur actuelle de la commande '$old_totPulses'" ." Corriger 'Index correcteur'";
              	log::add(__CLASS__, 'error',"	".__FUNCTION__ ." ". $err_msg);
				throw new Exception(__($err_msg, __FILE__));
            }
		}
      	if (!$test){
      		$input_pulses_cmd->event($new_input_pulses, $_option['datetime']);
            $totPulses_cmd->event($new_totPulses, $_option['datetime']);
      	}
      	if ($old_totPulses == ""){
      		return;
        }
      	$consoTimeLength_min = round((strtotime($newValueDate) - strtotime($oldValueDate))/60, 2);
      	$diffPulse = $new_totPulses - $old_totPulses;
      	$vol_inst = round($diffPulse * 1000/ $pulseRatio );
      	$conso_inst = round(($diffPulse * 1000 / $pulseRatio) * $coefConv , 2);/// $pulseRatio
      	$debit = round($vol_inst/$consoTimeLength_min*60, 2);
      	$puiss = round($vol_inst/$consoTimeLength_min*60 * $coefConv, 2);;
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] vol_inst : $vol_inst $pulseUnitSmall soit $conso_inst Wh");
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] consoTimeLength : $consoTimeLength_min minutes");
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] debit : $debit $pulseUnitSmall/heure => puiss : $puiss Wh/heure" );
      	
      	$vol_index =  round($new_totPulses / $pulseRatio , 2);
      	$conso_index = round($vol_index * $coefConv, 2);
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] index vol: $vol_index $pulseUnitBig => conso: $conso_index kWh coef: ".$coefConv );
		$totPulses_thishour = $eqLogic->getValueForPreviousDate('H', 'total_pulses');
      	$vol_heure = round(($new_totPulses - $totPulses_thishour) / $pulseRatio * 1000, 2);
      	
      	$conso_heure =  round($vol_heure * $coefConv, 2); 
      	$cmds_values = [
				"vol_inst"		=> $vol_inst, //"Conso Horaire"
				"conso_inst"	=> $conso_inst, //"Index_conso"	
				"debit"			=> $debit, //"Dernier debit"Puissance_inst
          		"power"			=> $puiss, //"Dernier puissance"
          		"vol_index"		=> $vol_index, // "Index_vol"	
				"conso_index"	=> $conso_index, //"Index_conso"	
				"vol_heure"		=> $vol_heure, //"Volume Horaire"
				"conso_heure"	=> $conso_heure, //"Conso Horaire"
          		"last_pulse"	=> $_option['datetime'], 
		];
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] cmds_values : ".json_encode($cmds_values) );
      	foreach($cmds_values as $cmdLogId => $cmd_value){
			$cmd = $eqLogic->getCmd(null, $cmdLogId);
			if (!is_object($cmd) || $cmd_value === false  || $cmd_value < 0) {
              	continue;
			}
			log::add(__CLASS__, 'debug',"	".__FUNCTION__ . "$modeTeste [$eqName] set cmd:  ".$cmdLogId." to ".$cmd_value);
			if (!$test){
      			$cmd->event($cmd_value, $newValueDate);
			}
		}
      	
      	
    }
  /* ************************************************************************** */

	public static function cronHourly($eqLogicid=null, $from =__FUNCTION__) {
      	if($eqLogicid == null){
          	$eqLogics = self::byType(__CLASS__, true);
        }else{
          $eqLogics = [pulseCounter::byId($eqLogicid)];
      	}
      	$cmdsData = [];
        foreach ($eqLogics as $eqLogic) {
          	$eqName = $eqLogic->getName();
          	$eqConfig = $eqLogic->getConfiguration();
            $coefConv = floatval(jeedom::evaluateExpression($eqConfig['coef_'.$eqConfig['coefType']]));
      		$coefConv = $coefConv > 0 ? $coefConv : 1;
            $cmdsData['coef_conv'] = $coefConv;
          	$cmd_coefConv = $eqLogic->getCmd(null, 'coef_conv');
          	
          	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] * coefConv ".$coefConv);
            $pulseRatio = $eqConfig['pulseRatio'];
      		
          	$cmd_totPulses = $eqLogic->getCmd(null, 'total_pulses');
          	$totPulses_now = $cmd_totPulses->execCmd();
          	
          	if ($totPulses_now == ""){
                return;
            }
          
          	$totPulses_thishour = $eqLogic->getValueForPreviousDate('H', 'total_pulses');
          	$totPulses_lasthour = $eqLogic->getValueForPreviousDate('H-1', 'total_pulses');
          	$totPulses_thisday = $eqLogic->getValueForPreviousDate('D', 'total_pulses');
          
          	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] pulse_now :" . $totPulses_now);
          	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] getValueForPreviousDate D :" .$totPulses_thisday);
          	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] getValueForPreviousDate H :" .$totPulses_thishour);
          	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] getValueForPreviousDate H-1 :" .$totPulses_lasthour);
          	
          	$cmd_totPulsesDate = ($cmd_totPulses->getValueDate() != "") ? $cmd_totPulses->getValueDate() : $eqLogic->getConfiguration('createtime');
           	if($cmd_totPulsesDate < date('Y-m-d H:i', strtotime('-12 hours')) ){
              	log::add(__CLASS__, 'warning',"	".__FUNCTION__ . " [$eqName] L'index n'a pas evoluer depuis le : ".$cmd_totPulsesDate);
            }
          	
          	if($cmd_totPulses->getValueDate() < date('Y-m-d H:00:00') ){
              	
            }
          	$cmdsData['vol_heure'] = 0;
            $cmdsData['conso_heure'] = 0;
          
          if($totPulses_thisday != ""){
              	$cmdsData['vol_jour'] = round(($totPulses_now - $totPulses_thisday) / $pulseRatio * 1000, 2);
          		$cmdsData['conso_jour'] = round(($totPulses_now - $totPulses_thisday) / $pulseRatio * 1000 * $coefConv, 2);
          	}
          
            $cmdsData['vol_heure_last'] = round(($totPulses_thishour - $totPulses_lasthour) / $pulseRatio * 1000, 2);
          	$cmdsData['conso_heure_last'] = round(($totPulses_thishour - $totPulses_lasthour) / $pulseRatio * 1000 * $coefConv, 2);
          	
          	
          /*********************/
          	$conso_jour = round($eqLogic->getConsoFromIndex('conso_index', date('Y-m-d 00:00:00')) * 1000, 2);
          $conso_jour = round($eqLogic->getConsoFromIndex('total_pulses', date('Y-m-d 00:00:00')) / $pulseRatio * 1000 * $coefConv, 2);
          
          	if($conso_jour != $cmdsData['conso_jour']){
              log::add(__CLASS__, 'error',"	".__FUNCTION__ . " [$eqName] conso_jour !! $conso_jour //". $cmdsData['conso_jour'] );
            }else log::add(__CLASS__, 'warning',"	".__FUNCTION__ . " [$eqName] conso_jour == : $conso_jour");
          
          
          	foreach($cmdsData as $cmdLogId => $cmd_value){
              	$valueDate="";
                if ($cmdLogId == "vol_heure_last") {
					$cmd = $eqLogic->getCmd(null, "vol_heure");
                  	$valueDate = date('Y-m-d H:00:00', strtotime('-1 hour'));
                    $eqLogic->removeHistoryData("vol_heure", date('Y-m-d H:00:00', strtotime('-1 hour')), date('Y-m-d H:59:59', strtotime('-1 hour')));
					$target_valName = $cmdLogId."_last";
				}elseif ($cmdLogId == "conso_heure_last") {
					$cmd = $eqLogic->getCmd(null, "conso_heure");
                  	$valueDate = date('Y-m-d H:00:00', strtotime('-1 hour'));
                    $eqLogic->removeHistoryData("conso_heure", date('Y-m-d H:00:00', strtotime('-1 hour')), date('Y-m-d H:59:59', strtotime('-1 hour')));
					$target_valName = $cmdLogId."_last";
					$target_val = $$target_valName;
                }else {
                  	$cmd = $eqLogic->getCmd(null, $cmdLogId);
                  	$valueDate = date('Y-m-d H:00:00');
                }
              	if (!is_object($cmd) || $cmd_value === false  || $cmd_value < 0) {
                    continue;
                }
                log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] set cmd:  $cmdLogId to $cmd_value at $valueDate");
              	$cmd->event(round($cmd_value, 2), $valueDate);
			
			}
			log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] cmdValue ".json_encode($cmdsData));
    	}
      	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] end ");
            
    }
/* ************************************************************************** */
	public static function cronDaily($eqLogicid=null, $from =__FUNCTION__) {
      	log::add(__CLASS__, 'info',__FUNCTION__ . '  Starting  ');
      	if($eqLogicid == null){
          	$eqLogics = self::byType(__CLASS__, true);
        }else{
          	$eqLogics = [pulseCounter::byId($eqLogicid)];
      	}
      	$cmdsData = [];
        foreach ($eqLogics as $eqLogic) {
          	if(strtotime($eqLogic->getConfiguration('createtime')) > strtotime('-1 day')){
                log::add(__CLASS__, 'info',"	".__FUNCTION__ . " [$eqName] rien à faire aujourd'hui");
                return;
            }
          	$eqName = $eqLogic->getName();
          	
          	$eqConfig = $eqLogic->getConfiguration();
            $coefConv = floatval(jeedom::evaluateExpression($eqConfig['coef_'.$eqConfig['coefType']]));
          	$coefConv = $coefConv > 0 ? $coefConv : 1;
            log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] coefConv $coefConv");
            $pulseRatio = $eqConfig['pulseRatio'];
      
          	$cmdLogId = 'total_pulses';
          	$cmd_totPulses = $eqLogic->getCmd(null, $cmdLogId);
          	//$cmd_id = $cmd_totPulses->getId();
          	$totPulses_now = $cmd_totPulses->execCmd();
          	if ($totPulses_now == ""){
                return;
            }
          	$cmdsData['now'] = $totPulses_now;
          	
          	$cmd_totPulsesDate = ($cmd_totPulses->getValueDate() != "") ? $cmd_totPulses->getValueDate() : $eqLogic->getConfiguration('createtime');
           	
          	if($cmd_totPulsesDate < date('Y-m-d 00:00:00') ){
              	$cmdsData['vol_jour'] = 0;
            	$cmdsData['conso_jour'] = 0;
            }
          
          	if($cmd_totPulsesDate < date('Y-m-d H:i', strtotime('-24 hours')) ){
              	log::add(__CLASS__, 'warning',"	".__FUNCTION__ . " [$eqName] n'a pas evoluer depuis le : ".$cmd_totPulsesDate);
            }
          	
          	$totPulses_today = $eqLogic->getValueForPreviousDate('D', $cmdLogId);
          	$totPulses_yesterday = $eqLogic->getValueForPreviousDate('D-1', $cmdLogId);
          	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] totPulses_today: $totPulses_today - totPulses_yesterday: $totPulses_yesterday");
          	if($totPulses_yesterday != ""){
              	$cmdsData['vol_hier'] = $totPulses_yesterday ? ($totPulses_today - $totPulses_yesterday) * 1000 / $pulseRatio: false;
            	$cmdsData['conso_hier'] = $conso_jour_last = $totPulses_yesterday ? round($cmdsData['vol_hier'] * $coefConv, 2): false;
              	$cmdsData['vol_jour_last'] = round($cmdsData['vol_hier'],2);
              	$cmdsData['conso_jour_last'] = round($cmdsData['conso_hier'],2);
              
            }
          	
          	$totPulses_week = $eqLogic->getValueForPreviousDate('W', $cmdLogId);
          	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] pulse_week $totPulses_week");
          	$cmdsData['vol_sem'] = $totPulses_week ? round(($totPulses_now - $totPulses_week) / $pulseRatio, 2): false;
          	$cmdsData['conso_sem'] = $totPulses_week ? round($cmdsData['vol_sem'] * $coefConv, 2): false;
            
          	$totPulses_month = $eqLogic->getValueForPreviousDate('M', $cmdLogId);
          	if($totPulses_month != ""){
              	$cmdsData['vol_mois'] = $totPulses_month ? round(($totPulses_now - $totPulses_month) / $pulseRatio, 2): false;
              	$cmdsData['conso_mois'] = $totPulses_month ? round($cmdsData['vol_mois'] * $coefConv, 2): false;
            }
          	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] cmdsValue: ".json_encode($cmdsData));
          
          	foreach($cmdsData as $cmdLogId => $cmd_value){
              	$valueDate="";
                if ($cmdLogId == "vol_jour_last") {
					$cmd = $eqLogic->getCmd(null, "vol_jour");
                  	$valueDate = date('Y-m-d 00:00:00', strtotime('-1 days'));
                    $eqLogic->removeHistoryData("vol_jour", date('Y-m-d 00:00:00', strtotime('-1 days')), date('Y-m-d 23:59:59', strtotime('-1 days')));
					$target_valName = $cmdLogId."_last";
				}elseif ($cmdLogId == "conso_jour_last") {
					$cmd = $eqLogic->getCmd(null, "conso_jour");
                  	$valueDate = date('Y-m-d 00:00:00', strtotime('-1 days'));
                    $eqLogic->removeHistoryData("conso_jour", date('Y-m-d 00:00:00', strtotime('-1 days')), date('Y-m-d 23:59:59', strtotime('-1 days')));
					$target_valName = $cmdLogId."_last";
					$target_val = $$target_valName;
                }else {
                  	$cmd = $eqLogic->getCmd(null, $cmdLogId);
                  	$valueDate = date('Y-m-d 00:00:00');
                }
              	if (!is_object($cmd) || $cmd_value === false  || $cmd_value < 0) {
                    continue;
                }
                log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] set cmd: $cmdLogId to $cmd_value");
              	$cmd->event(round($cmd_value, 2), $valueDate );
            }
        }
    }

/* ************************************************************************** */
	
/* ************************************************************************** */
	public function getValueForPreviousDate($_period, $_cmdLogId) {
		$cmd_id = $this->getCmd(null, $_cmdLogId)->getId();
		if($_period == 'H'){
            $date = date('Y-m-d H:00:00');
		} 
		else if($_period== 'H-1'){
            $date = date('Y-m-d H:00:00', strtotime('-1 hour'));
            ////$end = date('Y-m-d H:59:59', strtotime('now -1 hour'));
        } 
		else if($_period== 'D'){
        $date = date('Y-m-d 00:00:00');
		} 
		else if($_period== 'D-1'){
        $date = date('Y-m-d H:i:s', strtotime('-1 day'));
      } 
		else if($_period== 'W'){
            $date = date('Y-m-d H:i:s', strtotime('monday this week midnight'));
		} 
		else if($_period== 'W-1'){
            $date = date('Y-m-d H:i:s', strtotime('monday this week midnight -7 days'));
            //$end = date('Y-m-d H:i:s', strtotime('last sunday 23:59:59'));
        } 
		else if($_period== 'M'){
            $date = date('Y-m-d H:i:s', strtotime('first day of this month midnight'));
            //$end = date('Y-m-d H:i:s', strtotime('now'));
        } 
		else if($_period== 'M-1'){
            $date = date('Y-m-d H:i:s', strtotime('first day of previous month midnight'));
            //$end = date('Y-m-d H:i:s', strtotime('last day of previous month 23:59:59'));
        } 
		else if($_period== 'Y'){
            $date = date('Y-m-d H:i:s', strtotime('first day of january this year midnight'));
            //$end = date('Y-m-d H:i:s', strtotime('now'));
        } 
		else if($_period== 'Y-1'){
            $date = date('Y-m-d H:i:s', strtotime('first day of january last year midnight'));
            //$end = date('Y-m-d H:i:s', strtotime('last day of december last year 23:59:59'));
        }
		else if($_period != ''){
            $date = date('Y-m-d H:i:s', strtotime($_period));
        }
		else{
          return 0;
      }
		/*$history = history::byCmdIdAtDatetime($cmd_id, $date);
		if (!is_object($history)) return false;
		$return = round($history->getValue(), 2);
        */
      	$return = $this->_rqstHist($cmd_id, $date);
       	return $return;
		
	}

/* ************************************************************************** */
	public function getValueForPreviousPeriod($_period, $_type) {
		$period = 'None';
		if (strpos($_period, '-1') === false) {
			$period = $_period . '-1';
			$start = date('Y-m-d 00:00:00', strtotime(self::$_period[$period]['start']));
			$end = date('Y-m-d H:i:s', strtotime(self::$_period[$period]['end']));
		} else {
			if (strpos($_period, 'D') !== false) {
				$start = date('Y-m-d 00:00:00', strtotime(self::$_period[$_period]['start'] . ' -1 day'));
				$end = date('Y-m-d H:i:s', strtotime(self::$_period[$_period]['end'] . ' -1 day'));
			} else if (strpos($_period, 'W') !== false) {
				$start = date('Y-m-d 00:00:00', strtotime(self::$_period[$_period]['start'] . ' -1 week'));
				$end = date('Y-m-d H:i:s', strtotime(self::$_period[$_period]['end'] . ' -1 week'));
			} else if (strpos($_period, 'M') !== false) {
				$start = date('Y-m-d 00:00:00', strtotime(self::$_period[$_period]['start'] . ' -1 month'));
				$end = date('Y-m-d H:i:s', strtotime(self::$_period[$_period]['end'] . ' -1 month'));
			} else if (strpos($_period, 'Y') !== false) {
				$start = date('Y-m-d 00:00:00', strtotime(self::$_period[$_period]['start'] . ' -1 year'));
				$end = date('Y-m-d H:i:s', strtotime(self::$_period[$_period]['end'] . ' -1 year'));
			}
		}
		if (!isset($start)) {
			return 0;
		}
		return $this->_getValueForPeriod($_type, $start, $end);
	}
	
/* ************************************************************************** */
	public function _getValueForPeriod($_type, $_startTime, $_endTime) {
		$values = array(
			'cmd_id' => $this->getCmd(null, $_type)->getId(),
			'startTime' => $_startTime,
			'endTime' => $_endTime,
		);
		$sql = 'SELECT  CAST(value AS DECIMAL(12,2)) as result
		FROM (
			SELECT *
			FROM history
			WHERE cmd_id=:cmd_id
			AND `datetime`>=:startTime
			AND `datetime`<=:endTime
			GROUP BY date(`datetime`)
			UNION ALL
			SELECT *
			FROM historyArch
			WHERE cmd_id=:cmd_id
			AND `datetime`>=:startTime
			AND `datetime`<=:endTime
			GROUP BY date(`datetime`)
		) as dt';
		$result = DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);
		return $result['result'];
	}
	


/* ************************************************************************** */
	public static function generatePanel($_version = 'dashboard', $_period = 'D') {
		log::add(__CLASS__, 'debug',__FUNCTION__ . '  Starting ****************');
      	if ($_period == '') {
			$_period = 'D';
		}
		config::save('savePeriod', $_period, 'pulseCounter');
		$data = array('totalCost' => 0, 'pulseCounter' => array(), 'avgPreviousTotalCost' => 0);
		foreach (self::byType('pulseCounter',true) as $eqLogic) {
			if ($eqLogic->getConfiguration('addToTotal', 0) == 0) {
				continue;
			}
			$data['pulseCounter'][$eqLogic->getName()] = array(
				'unite' => $eqLogic->getCmd('info', 'consumption')->getUnite(),
				'pulseCounter' => $eqLogic,
				'cost_id' => $eqLogic->getCmd('info', 'consumption')->getId(),
				'consumption_id' => $eqLogic->getCmd('info', 'consumption')->getId()
			);
			if ($_period == 'D') {
				$data['pulseCounter'][$eqLogic->getName()]['cost'] = $eqLogic->getCmd('info', 'cost')->execCmd();
				$data['pulseCounter'][$eqLogic->getName()]['consumption'] = $eqLogic->getCmd('info', 'consumption')->execCmd();
			} elseif (isset(self::$_period[$_period])) {
				$data['pulseCounter'][$eqLogic->getName()]['cost'] = $eqLogic->_getValueForPeriod('cost', date('Y-m-d 00:00:00', strtotime(self::$_period[$_period]['start'])), date('Y-m-d H:i:s', strtotime(self::$_period[$_period]['end'])));
				$data['pulseCounter'][$eqLogic->getName()]['consumption'] = $eqLogic->_getValueForPeriod('consumption', date('Y-m-d 00:00:00', strtotime(self::$_period[$_period]['start'])), date('Y-m-d H:i:s', strtotime(self::$_period[$_period]['end'])));
			} else {
				throw new Exception(__('Période non trouvée : ', __FILE__) . $_period);
			}
			$data['pulseCounter'][$eqLogic->getName()]['avgPreviousCost'] = $eqLogic->getValueForPreviousPeriod($_period, 'cost');
			$data['pulseCounter'][$eqLogic->getName()]['avgPreviousConsumption'] = $eqLogic->getValueForPreviousPeriod($_period, 'consumption');
			$data['totalCost'] += $data['pulseCounter'][$eqLogic->getName()]['cost'];
			$data['avgPreviousTotalCost'] += $data['pulseCounter'][$eqLogic->getName()]['avgPreviousCost'];
		}
		$return = array(
			'html' => '',
			'data' => array('graphData' => array(), 'division' => array()),
		);
		$return['data']['graphData']['day'] = array('start' => date('Y-m-d H:i:s', strtotime(self::$_period[$_period]['start'])), 'end' => date('Y-m-d H:i:s', strtotime(self::$_period[$_period]['end'])));
		if ($_version == 'dashboard') {
			$return['html'] = '<div class="row">';
			$return['html'] .= '<div class="col-lg-6">';
		}
		$return['html'] .= '<center>';
		foreach (self::$_period as $key => $value) {
			if ($_period == $key) {
				$return['html'] .= '<a class="btn btn-success ui-btn-raised ui-btn-inline bt_changePeriod" data-period="' . $key . '">' . $value['name'] . '</a> ';
			} else {
				$return['html'] .= '<a class="btn btn-default ui-btn ui-btn-inline bt_changePeriod" data-period="' . $key . '">' . $value['name'] . '</a> ';
			}
		}
		$return['html'] .= '</center>';
		$return['html'] .= '<span style="font-size:2em;color:#ff9f55"><center>Ma consommation totale</center></span>';
		$vText = '';
		if ($data['totalCost'] > $data['avgPreviousTotalCost']) {
			$variation = '<i style="color:#e60000" class="fas fa-arrow-alt-circle-up"></i>';
			$vText = '+' . round($data['totalCost'] - $data['avgPreviousTotalCost'], 2) . ' € par rapport à la période précedente';
		} elseif ($data['totalCost'] < $data['avgPreviousTotalCost']) {
			$variation = '<i style="color:#60d63b" class="fas fa-arrow-alt-circle-down"></i>';
			$vText = round($data['totalCost'] - $data['avgPreviousTotalCost'], 2) . ' € par rapport à la période précedente';
		} else {
			$variation = '<span style="color:#000000;font-weight:bold;font-size:1.2em;">=</span>';
		}
		$return['html'] .= '<span style="font-size:3em;color:#60d63b"><center>' . $data['totalCost'] . ' € ' . $variation . '</center></span>';
		$return['html'] .= '<span style="font-size:0.8em;"><center>' . $vText . '</center></span>';
		$return['html'] .= '<div id="graph_division"></div>';
		if ($_version == 'dashboard') {
			$return['html'] .= '</div>';
			$return['html'] .= '<div class="col-lg-6">';
		}
		foreach ($data['pulseCounter'] as $name => $value) {
			$division_cost =  $value['cost'] / $data['totalCost'] * 100;
			if(is_nan($division_cost)){
				$division_cost = 0;
			}
			$return['data']['division'][] = array('name' => $name, 'y' => $division_cost);
			$performance = $value['pulseCounter']->getCmd('info', 'performance_dju');
			if(is_object($performance)){
				$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_performance'] = array('id' => $performance->getId());
			}
			if ($_period == 'D' || $_period == 'D-1') {
				$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_instant'] = $value['pulseCounter']->getCmd('info', 'instant')->getId();
				$value['pulseCounter']->getCmd('info', 'instant')->setDisplay('graphType', 'area');
				$value['pulseCounter']->getCmd('info', 'instant')->save();
				if(is_object($performance)){
					$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_performance']['startDate'] = date('Y-m-d 00:00:00',strtotime('now -1 month'));
					$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_performance']['endDate'] = date('Y-m-d H:i:s',strtotime('now'));
				}
			} elseif ($_period == 'W' || $_period == 'W-1') {
				$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_day_cost'] = $value['pulseCounter']->getCmd('info', 'cost')->getId();
				$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_day_consumption'] = $value['pulseCounter']->getCmd('info', 'consumption')->getId();
				$value['pulseCounter']->getCmd('info', 'consumption')->setDisplay('groupingType', 'high::day');
				$value['pulseCounter']->getCmd('info', 'consumption')->setDisplay('graphType', 'column');
				$value['pulseCounter']->getCmd('info', 'consumption')->save();
				$value['pulseCounter']->getCmd('info', 'cost')->setDisplay('groupingType', 'high::day');
				$value['pulseCounter']->getCmd('info', 'cost')->setDisplay('graphType', 'column');
				$value['pulseCounter']->getCmd('info', 'cost')->save();
				if(is_object($performance)){
					$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_performance'] = array('id' => $performance->getId()	);
				}
			} elseif ($_period == 'M' || $_period == 'M-1') {
				$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_day_cost'] = $value['pulseCounter']->getCmd('info', 'cost')->getId();
				$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_day_consumption'] = $value['pulseCounter']->getCmd('info', 'consumption')->getId();
				$value['pulseCounter']->getCmd('info', 'consumption')->setDisplay('groupingType', 'high::day');
				$value['pulseCounter']->getCmd('info', 'consumption')->setDisplay('graphType', 'column');
				$value['pulseCounter']->getCmd('info', 'consumption')->save();
				$value['pulseCounter']->getCmd('info', 'cost')->setDisplay('groupingType', 'high::day');
				$value['pulseCounter']->getCmd('info', 'cost')->setDisplay('graphType', 'column');
				$value['pulseCounter']->getCmd('info', 'cost')->save();
			} else {
				$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_day_cost'] = $value['pulseCounter']->getCmd('info', 'cost')->getId();
				$return['data']['graphData'][$value['pulseCounter']->getId()]['cmd_day_consumption'] = $value['pulseCounter']->getCmd('info', 'consumption')->getId();
				$value['pulseCounter']->getCmd('info', 'consumption')->setDisplay('groupingType', 'sum::month');
				$value['pulseCounter']->getCmd('info', 'consumption')->setDisplay('graphType', 'column');
				$value['pulseCounter']->getCmd('info', 'consumption')->save();
				$value['pulseCounter']->getCmd('info', 'cost')->setDisplay('groupingType', 'sum::month');
				$value['pulseCounter']->getCmd('info', 'cost')->setDisplay('graphType', 'column');
				$value['pulseCounter']->getCmd('info', 'cost')->save();
			}
			if ($_version == 'dashboard') {
				$return['html'] .= '<div class="panel panel-default">';
				$return['html'] .= '<div class="panel-heading">';//
				$return['html'] .= '<div class="panel-title">';
			} else {
				$return['html'] .= '<br/><div class="nd2-card" style="max-width:none !important;">';
				$return['html'] .= '<div class="card-title">';
			}
			if ($value['pulseCounter']->getConfiguration('type') == 'electricity') {
				$return['html'] .= '<i class="fas fa-bolt"></i> ';
			} else if ($value['pulseCounter']->getConfiguration('type') == 'water') {
				$return['html'] .= '<i class="fas fa-tint"></i> ';
			} else if ($value['pulseCounter']->getConfiguration('type') == 'gas') {
				$return['html'] .= '<i class="fas fa-gas-pump"></i> ';
			}
			$dpe_txt = '';
			if ($value['pulseCounter']->getConfiguration('calculDpe') == 1) {
				$dpe = $value['pulseCounter']->calculDpe($value['consumption'], $value['pulseCounter']->getConfiguration('type'), $_period);
				$dpe_txt = ' - <span style="font-style: italic;">' . $dpe['value'] . ' kWh/m²/an (' . $dpe['category'] . ')</span>';
			}
			$performance_html = '';
			if(is_object($performance)){
				$performance_html = ' - <div style="display:inline-block">'.$performance->toHtml($_version).'</div>';
			}
			$return['html'] .= '<span style="font-weight:bold;">' . $name . '</span>' . $dpe_txt;
			$return['html'] .= $performance_html;
			$return['html'] .= ' <a class="btn btn-xs btn-default displayEnergyDetail pull-right ui-btn ui-mini ui-btn-inline ui-btn-raised" data-id="' . $value['pulseCounter']->getId() . '"><i class="fas fa-info"></i></a>';
			$return['html'] .= '</div>';
			if ($_version == 'dashboard') {
				$return['html'] .= '</div>';
			}
			if ($_version == 'dashboard') {
				$return['html'] .= '<div class="panel-body">';
			} else {
				$return['html'] .= '<div class="card-supporting-text" style="font-size: 12px !important;">';
			}
			$return['html'] .= '<div class="row">';
			if ($value['pulseCounter']->getConfiguration('instant') != '') {
				$return['html'] .= '<div class="col-xs-4">';
				$return['html'] .= '<center>Instantanné</center>';
				$return['html'] .= $value['pulseCounter']->getCmd('info', 'instant')->toHtml($_version);
				
				$return['html'] .= '</div>';
			}
			if ($value['pulseCounter']->getConfiguration('consumption') != '') {
				$return['html'] .= '<div class="col-xs-4">';
				if ($value['consumption'] > $value['avgPreviousConsumption']) {
					$variation = '<i style="color:#e60000" class="fas fa-arrow-alt-circle-up"></i>';
				} elseif ($value['consumption'] < $value['avgPreviousConsumption']) {
					$variation = '<i style="color:#60d63b" class="fas fa-arrow-alt-circle-down"></i>';
				} else {
					$variation = '<span style="color:#000000;font-weight:bold;font-size:1.2em;">=</span>';
				}
				$return['html'] .= '<center>' . $variation . ' Consommation</center>';
				if ($_period == 'D') {
					$return['html'] .= $value['pulseCounter']->getCmd('info', 'consumption')->toHtml($_version);
				} else {
					$return['html'] .= '<center><strong class="state" style="font-size: 12px;">' . $value['consumption'] . '</strong> ' . $value['pulseCounter']->getCmd('info', 'consumption')->getUnite() . '</center>';
				}
				$return['html'] .= '</div>';
			}
			if ($value['pulseCounter']->getConfiguration('cost') != '') {
				$return['html'] .= '<div class="col-xs-4">';
				if ($value['cost'] > $value['avgPreviousCost']) {
					$variation = '<i style="color:#e60000" class="fas fa-arrow-alt-circle-up"></i>';
				} elseif ($value['cost'] < $value['avgPreviousCost']) {
					$variation = '<i style="color:#60d63b" class="fas fa-arrow-alt-circle-down"></i>';
				} else {
					$variation = '<span style="color:#000000;font-weight:bold;font-size:1.2em;">=</span>';
				}
				$return['html'] .= '<center>' . $variation . ' Coût</center>';
				if ($_period == 'D') {
					$return['html'] .= $value['pulseCounter']->getCmd('info', 'cost')->toHtml($_version);
				} else {
					$return['html'] .= '<center><strong class="state" style="font-size: 12px;">' . $value['cost'] . '</strong> ' . $value['pulseCounter']->getCmd('info', 'cost')->getUnite() . '</center>';
				}
				$return['html'] .= '</div>';
			}
			$return['html'] .= '</div>';
			$return['html'] .= '<div class="energyDetail" data-id="' . $value['pulseCounter']->getId() . '" style="display:none;">';
			$return['html'] .= '<div id="div_chartDay' . $value['pulseCounter']->getId() . '"></div>';
			if($value['pulseCounter']->getConfiguration('temperature_outdoor') != ''){
				$return['html'] .= '<div id="div_chartPerformanceDay' . $value['pulseCounter']->getId() . '"></div>';
			}
			$return['html'] .= '</div>';
			$return['html'] .= '</div>';
			$return['html'] .= '</div>';
			if ($_version != 'dashboard') {
				$return['html'] .= '</div>';
			}
		}
		if ($_version == 'dashboard') {
			$return['html'] .= '</div>';
			$return['html'] .= '</div>';
		}
		return $return;
	}
  


/* ************************************************************************** */
	public function cleanData($_cmdLogId) {
		//log::add(__CLASS__, 'debug',__FUNCTION__ . '  Starting ****************');
      	$cmd = $this->getCmd('info', $_cmdLogId);
		$historys = $cmd->getHistory();
		$datas = array();
		foreach ($historys as $history) {
			$date = date('Y-m-d', strtotime($history->getDatetime()));
			if (!isset($datas[$date])) {
				$datas[$date] = $history;
				continue;
			}
			if (date('Gi', strtotime($history->getDatetime())) > 2300 && date('Gi', strtotime($history->getDatetime())) < 10) {
				if ($history->getValue() > ($datas[$date]->getValue() * 2)) {
					log::add(__CLASS__, 'debug',__FUNCTION__ . '  remove1');
                  	//$history->remove();
				} else {
					log::add(__CLASS__, 'debug',__FUNCTION__ . '  remove2');
                  	//$datas[$date]->remove();
					$datas[$date] = $history;
				}
				continue;
			}
			if (strtotime($history->getDatetime()) > strtotime($datas[$date]->getDatetime())) {
				log::add(__CLASS__, 'debug',__FUNCTION__ . " $date remove3 ".$history->getDatetime() .' > '.$datas[$date]->getDatetime());
                  	//$datas[$date]->remove();
				$datas[$date] = $history;
			} else {
				log::add(__CLASS__, 'debug',__FUNCTION__ . '  remove4');
                  	//$history->remove();
			}
		}
	}

/* ************************************************************************** */
	public function removeHistoryData($_cmdLogId, $_start, $_end) {
		$cmd = $this->getCmd('info', $_cmdLogId);
      	if(is_object($cmd)){
			$cmd_id = $cmd->getId();
			//log::add(__CLASS__, 'debug',"	".__FUNCTION__ ." cmd_id $cmd_id du $_start au $_end");
			$historys = history::all($cmd_id, $_start, $_end);
          	foreach ($historys as $history) {
              	$history->remove();
            }
		}else {
          //log::add(__CLASS__, 'warning',"	".__FUNCTION__ ." cmd $_cmdLogId Not exist");
    	}
	}

	
	/*     * *********************Méthodes d'instance************************* */

/* ************************************************************************** */
	public function preSave() {
      	$eqType = $this->getConfiguration('type', "");
        log::add(__CLASS__, 'info',"	".__FUNCTION__ . " [$eqName] start $eqType");
      	$this->setCategory('energy', 1);
      	
      	if($this->getConfiguration('pulseWeight') == ''){
          	$this->setConfiguration('pulseWeight', 10);
      	}
      	$pulseUnit = $this->getConfiguration('pulseUnit', '');
      	if($pulseUnit == ''){
          	$pulseUnit = ($eqType == "electricity") ? 'Wh' : 'l';
        	$this->setConfiguration('pulseUnit', $pulseUnit);
        }
		$pulseUnitBig = ($pulseUnit == "l") ? "m³" : ($pulseUnit == "Wh" ? "kWh" : $pulseUnit);
      	log::add(__CLASS__, 'warning',"	".__FUNCTION__ . " [$eqName] pulseUnit $pulseUnit $pulseUnitBig");
		$pulseUnitSmall = ($pulseUnitBig == "kWh") ? "Wh" : "l";
		$this->setConfiguration('pulseUnitBig', $pulseUnitBig);
		$this->setConfiguration('pulseUnitSmall', $pulseUnitSmall);
        
      
      	if($eqType != ''){// && $this->getConfiguration('cmdsMaked', '') == ''
			if($this->getConfiguration('cmdsMaked', '') != true) $this->makeCmd();
			$pulseUnit = $this->getConfiguration('pulseUnit', 'l');//$eqConfig[''];
			$pulseWeight_conv = ($pulseUnit == 'l' || $pulseUnit == 'Wh') ? 1000 : 1;
			$pulseRatio = $pulseWeight_conv/$this->getConfiguration('pulseWeight');//$this->getConfiguration('pulseWeight')/$pulseWeight_conv;
			log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName]pulseRatio: $pulseRatio");
			$this->setConfiguration('pulseRatio', $pulseRatio);
				
          	if($this->getConfiguration('cmdsMaked') == true){  
				$input_pulses_cmd = $this->getCmd(null, 'input_pulses');
				$input_pulses_cmdVal = $input_pulses_cmd->execCmd();
				if (!is_numeric($input_pulses_cmdVal)) {
                  	$countType = $this->getConfiguration('countType');
              		$input_pulses = ($countType == 'total') ? jeedom::evaluateExpression($this->getConfiguration('pulse')) : 0;
                  	log::add(__CLASS__, 'debug', __FUNCTION__ . ' set input_pulses to '.$input_pulses);
            		$input_pulses_cmd->event($input_pulses);
            	}else $input_pulses = $input_pulses_cmdVal;
              
              
                
              	$indexVolCible = $this->getConfiguration('indexVolCible', '');
                if($indexVolCible != ''){
                  	log::add(__CLASS__, 'debug',"	".__FUNCTION__ . " [$eqName] ** start conversion indexVolCible ($indexVolCible) to indexPulseAdd... ");
                    $old_indexPulseAdd = $this->getConfiguration('indexPulseAdd', 0);
                    $VolToPulseAdd = $indexVolCible * $pulseRatio;
                    log::add(__CLASS__, 'warning',"	".__FUNCTION__ . " [$eqName] VolToPulseAdd: $VolToPulseAdd");

                  	$indexPulseAdd = $VolToPulseAdd - $input_pulses;
                    $this->setConfiguration('indexPulseAdd', $indexPulseAdd);
                  	$this->setConfiguration('indexVolCible', '');
                  
                    log::add(__CLASS__, 'warning',"	".__FUNCTION__ . " [$eqName] input_pulses : $input_pulses -- indexPulseAdd from : $old_indexPulseAdd to $indexPulseAdd");

                 }
              	$this->updateInfos();
            }
        }
      	if( $this->getConfiguration('coef_'.$this->getConfiguration('coefType'), '') == ''){
        	$coefConv = ($eqType == 'gas') ? 10.91 : 1;
            $this->setConfiguration('coef_'.$this->getConfiguration('coefType'), $coef);
        }   
      	
	}
/* ************************************************************************** */
	public function postInsert() {
      log::add(__CLASS__, 'debug',__FUNCTION__ . '  Starting ****************');
      	
    }
  /* ************************************************************************** */
	public function postSave() {
      	$eqType = $this->getConfiguration('type', "");
      	$eqName = $this->getName();
          	
      	if($eqType == "") return;
      	$eqId = intval($this->getId());
        log::add(__CLASS__, 'debug',__FUNCTION__ . '  Starting ****************');
      	
      	$pulseConfig = $this->getConfiguration('pulse','') ;   //pulse etConfiguration('pulse'
		if($pulseConfig != ''){
            log::add(__CLASS__, 'debug', __FUNCTION__ . '  pulse cmd CONFIG Avalaible ... check listener');
          	preg_match_all('/#(?<cmds_id>[0-9]*)#/mi', $this->getConfiguration('pulse'), $pulse_matches);      
      		$target_cmd_id = null;
            foreach ($pulse_matches['cmds_id'] as $cmd_id) {
                $cmd = cmd::byId($cmd_id);
                    if (is_object($cmd)) {
                        $pulse_target = '#' . $cmd_id . '#';
                      	$target_cmd_id = $cmd_id;
                      	//log::add(__CLASS__, 'warning', __FUNCTION__ . '  pulse_target cmd : '.$pulse_target);          
          				break;
                    }
            }
          	if(count($pulse_matches['cmds_id']) > 1){
              	log::add(__CLASS__, 'warning', __FUNCTION__ . '  Attention une seule commande <pulse> est possible choix : '.$cmd->getHumanName());
            }
          
          	if($target_cmd_id){
                $listener_function  = 'pulseEvent';
                $pulse_listener = listener::byClassAndFunction('pulseCounter', $listener_function, array('pulseCounter_id' => $eqId));
                if (!is_object($pulse_listener)) {
                    log::add(__CLASS__, 'debug', __FUNCTION__ . '  creating pulse_listener... ');          
                    $pulse_listener = new listener();
                }
                $pulse_listener->setClass('pulseCounter');
                $pulse_listener->setFunction($listener_function);
                $pulse_listener->setOption(array('pulseCounter_id' => $eqId));
                $pulse_listener->emptyEvent();
              	$pulse_listener->addEvent($target_cmd_id);
				$pulse_listener->save();
            }
        }
		else{
          $err_msg = "Pas possible de sauvegarder l'équipement sans renseigner une commande 'Compteur impulsions'! c'est compris ?";
          throw new Exception(__($err_msg, __FILE__));
        }
    }

  /* ************************************************************************** */
	public function postRemove() {
		log::add(__CLASS__, 'debug',__FUNCTION__ . '  Starting **************** '.$this->getName());
		$listener = listener::byClassAndFunction('pulseCounter', 'pulseEvent', array('pulseCounter_id' => intval($this->getId()) ));
		if (is_object($listener)) {
			$listener->remove();
		}
    }

  /* ************************************************************************** */
	public function getConsoFromIndex($_cmd_id, $_startTime, $_endTime=null) {
        if($_endTime == null){
          	$_endTime = date('Y-m-d H:i:s');
        }
  		$val_start = $this->_rqstHist($_cmd_id, $_startTime);
        $val_end = $this->_rqstHist($_cmd_id, $_endTime);
      	$return = null;
        if($val_start != "" && $val_end > $val_start){
          	$return = round($val_end - $val_start, 2);
    	}
        log::add(__CLASS__, 'info','	'.__FUNCTION__ . " $_cmd_id	val_start: $val_start($_startTime) -- val_end: $val_end( $_endTime ) => $return");
        return $return;
    }

  /* ************************************************************************** */
	public function getConsoFromInst($_cmd_id, $_startTime, $_endTime) {
		$return = $this->_rqstHist($_cmd_id, $_startTime, $_endTime, 'sum');
		return $return;
	}
  
  /* ************************************************************************** */
	public function _rqstHist($_cmd_id, $_startTime, $_endTime = null, $_groupingType = null) {
  		$values = array(
			'startTime' => $_startTime,
			'endTime' => $_endTime,
		);
  		
  
  		if(is_numeric($_cmd_id)){
          	$values['cmd_id'] = $_cmd_id;
        }elseif(is_string($_cmd_id)){
          	$eqLogic = pulseCounter::byId($this->getId());
          	if( is_object( $eqLogic->getCmd(null, $_cmd_id) ) ){
          		$values['cmd_id'] = $eqLogic->getCmd(null, $_cmd_id)->getId();
            }
        }
  		else throw new Exception(__('Unknown cmd : ', __FILE__) . $_cmd_id);
  		 
  		$sql = 'SELECT CAST(value AS DECIMAL(12,2)) as result ';
  		try{
			if($_endTime != null){
				if($_groupingType != null){
					if($_groupingType == 'diff'){
						$sql = 'SELECT MAX(CAST(value as DECIMAL(12,2))) - MIN(CAST(value as DECIMAL(12,2))) as result ';
					}
					else{	$sql = 'SELECT '.strtoupper($_groupingType).'(CAST(value AS DECIMAL(12,2))) as result ';
					}
					$values['groupingType'] = $_groupingType;
				}	
				$sql .= 'FROM (
                          SELECT *
                          FROM history
                          WHERE cmd_id=:cmd_id
                          AND `datetime`>=:startTime
                          AND `datetime`<=:endTime
                          UNION ALL
                          SELECT *
                          FROM historyArch
                          WHERE cmd_id=:cmd_id
                          AND `datetime`>=:startTime
                          AND `datetime`<=:endTime
                      ) as dt ORDER BY datetime ASC';
			}
          	else{
				if($_groupingType != null){
					return 'Error:: endTime is mandatory for groupingType';
				}
                $sql .= 'FROM (
                          SELECT *
                          FROM history
                          WHERE cmd_id=:cmd_id
                          AND `datetime`<=:startTime
                          UNION ALL
                          SELECT *
                          FROM historyArch
                          WHERE cmd_id=:cmd_id
                          AND `datetime`<=:startTime
                      ) as dt ORDER BY `datetime` DESC LIMIT 1';//ORDER BY `datetime` DESC LIMIT 1
            }
          	$result = DB::Prepare($sql, $values, DB::FETCH_TYPE_ROW);
          	foreach ($result as $key => &$value) {
                if ($value === '') {
                    $value = 0;
                }
                $result[$key] = round($value, 2);
            }
          	$result['data'] = $values;
		}catch (Exception $ex) {
			log::add(__CLASS__, 'error', ' Erreur '.__FUNCTION__ .' : '.$ex);
		}
		return $result['result'];
}

  	
	/*     * **********************Getteur Setteur*************************** */
}

class pulseCounterCmd extends cmd {
	/*     * *************************Attributs****************************** */
	
	/*     * ***********************Methode static*************************** */
	
	/*     * *********************Methode d'instance************************* */
	
	public function execute($_options = array()) {
		$eqLogic = $this->getEqLogic();
		log::add('pulseCounter', 'warning', __FUNCTION__ . ' : '.$this->getHumanName()." ".$this->getLogicalId());
       	if ($this->getLogicalId() == 'pulse2') {
          	//log::add('pulseCounter', 'warning', '	'.__FUNCTION__ . ' pulse2 target_Value: '.$this->getValue());
          	//$return = round(jeedom::evaluateExpression($eqLogic->getConfiguration('pulse')), 0);
			$return = round(jeedom::evaluateExpression($this->getValue()), 0);
			log::add('pulseCounter', 'warning', '	'.__FUNCTION__ . ' pulse2 target_Value: '.$this->getValue() ." => ".$return);
          	return $return;
		}
      	else if ($this->getLogicalId() == 'temperature_outdoor') {
			return round(jeedom::evaluateExpression($eqLogic->getConfiguration('temperature_outdoor')), 1);
		}
      	else if ($this->getLogicalId() == 'refresh') {
          	return $eqLogic->refresh();
        }
	}
	
	/*     * **********************Getteur Setteur*************************** */
}