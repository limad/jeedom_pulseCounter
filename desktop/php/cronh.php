<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
if(init('id') != ""){
	$eqLogics = [pulseCounter::byId(init('id'))];
}else{
	$eqLogics = pulseCounter::byType('pulseCounter', true);
}



$cmdValue = [];
foreach ($eqLogics as $eqLogic) {
	//log::add('pulseCounter', 'debug',__FUNCTION__ . '  Starting for : '.$eqLogic->getName());
  	echo '<br>cronh : '.$eqLogic->getName().'('.$eqLogic->getId().')';
    $eqLogic->cronHourly($eqLogic->getId());
}
          
          
          
//echo '<br>date1 : '.$start;
//echo '<br>date2 : '.$end;

?>