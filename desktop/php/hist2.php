<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

$cmdLogId="vol_hier";
$vol_hier_last = 325;
$target_valName = $cmdLogId."_last";

//$start = date('Y-m-d H:00:00', strtotime('-1 hours'));
//$end = date('Y-m-d H:i:s', strtotime('-1 hours'));
$start = date('Y-m-d 00:00:00');
$end = date('Y-m-d H:i:s');

echo "<br>start: $start -- end: $end";

$eqId = "325";
$eqLogic = pulseCounter::byId($eqId);
if (!is_object($eqLogic) || $eqLogic->getIsEnable() == 0) {
	return;
}

$eqName = $eqLogic->getName();

$cmd_logId = 'total_pulses';
$cmd = $eqLogic->getCmd(null, $cmd_logId);
$cmdId = $cmd->getId();
$nbPulse = $cmd->execCmd();
$pulseRatio = $eqLogic->getConfiguration('pulseRatio');
$coefType = 'coef_'.$eqLogic->getConfiguration('coefType');
$coefConv = floatval(jeedom::evaluateExpression($eqLogic->getConfiguration($coefType)));
        
//$nbPulseBase_cmd = $eqLogic->getCmd(null, 'basepulse');
//$nbPulseBase = $nbPulseBase_cmd->execCmd();
      	
echo '<br><br><label class="alert alert-info">eqName:'." $eqName($eqId) <span> => cmd : $cmd_logId($cmdId)</span></label><br>";


echo '<br>nbPulse: 						'.$nbPulse;
//$totPulses_thishour = $eqLogic->getValueForPreviousDate('H', 'total_pulses');
//$totPulses_lasthour = $eqLogic->getValueForPreviousDate('H-1', 'total_pulses');
$totPulses_thisday = $eqLogic->getValueForPreviousDate('D', 'total_pulses');
//echo '<br>getValueForPreviousPeriod H: '.$totPulses_thishour;
//echo '<br>getValueForPreviousPeriod H-1: '.$totPulses_lasthour;
echo '<br>getValueForPreviousPeriod D: '.$totPulses_thisday;
echo '<br>diff: '.round($nbPulse - $totPulses_thisday, 2)*1000;

$conso_jour1 = round($eqLogic->getConsoFromIndex('total_pulses', $start)/ $pulseRatio* 1000* $coefConv, 2);//   
echo '<br>getConsoFromIndex total_pulses: '.$conso_jour1;
//$conso_jour3 = round($eqLogic->getConsoFromIndex('conso_index', $start)/ $pulseRatio* 1000* $coefConv, 2);//   
//echo '<br>getConsoFromIndex conso_index: '.$conso_jour3;



$conso_jour2 = round($eqLogic->getConsoFromIndex('conso_index', $start) * 1000, 2);
echo '<br>getConsoFromIndex conso_index: '.$conso_jour2;


echo '<p>getConsoFromInst conso_heure: '.	round($eqLogic->getConsoFromInst('conso_heure', $start, $end), 2);
echo "<br>getConsoFromInst conso_inst: ".	round($eqLogic->getConsoFromInst('conso_inst', $start, $end), 2);
$hourNow = $end = date('H');
for ($i = 0; $i < $hourNow; $i++) {
  	$hs = $i;
  	$h_end = $i+1;
  	$dateStart = date("Y-m-d $hs:00:00");
    $hourStartTxt = date("$hs:00");
    $dateEnd = date("Y-m-d $h_end:00:00");
   	//echo '<br>'." $i  => $dateStart to $dateEnd";
  	$conso_index = round($eqLogic->getConsoFromIndex('conso_index', $dateStart, $dateEnd) * 1000, 2);
  	$conso_inst = round($eqLogic->getConsoFromInst('conso_inst', $dateStart, $dateEnd), 2);
  	$conso_pulses = round($eqLogic->getConsoFromIndex('total_pulses', $dateStart, $dateEnd)/ $pulseRatio* 1000* $coefConv, 2);
  	$conso_hour = round($eqLogic->_rqstHist('conso_heure', $dateStart), 2);
  	echo '<br>'." $hourStartTxt  => getConsoFromIndex/conso_index: $conso_index - conso_inst: $conso_inst - conso_pulses: $conso_pulses - conso_hour: $conso_hour";
  

}
?>