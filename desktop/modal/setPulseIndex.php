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

if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
sendVarToJs('eqLogic_id', init('id'));

$eqLogic = pulseCounter::byId(init('id'));
$pulse_cmd = $eqLogic->getCmd(null, 'pulse');
$pulse_value = $pulse_cmd->execCmd();
$vol_index_cmd = $eqLogic->getCmd(null, 'vol_index');
if (!is_object($vol_index_cmd)) {
    $vol_index_cmd = $eqLogic->getCmd(null, 'conso_index');
}
$vol_index = $vol_index_cmd->execCmd();



$indexVolCible = $eqLogic->getConfiguration('indexVolCible');
$pulseRatio = $eqLogic->getConfiguration('pulseRatio');
//
$indexPulseAdd = $eqLogic->getConfiguration('indexPulseAdd', 0);
$basepulse_cmd = $eqLogic->getCmd(null, 'basepulse');
if (is_object($basepulse_cmd)) {
    $basepulse = $basepulse_cmd->execCmd();
}  


$lastValueDate = $pulse_cmd->getCollectDate();//date('Y-m-d', strtotime($pulse_cmd->getCollectDate()));
//log::add('pulseCounter', 'debug',__FUNCTION__ . ' lastValue: '. $lastValueDate );
if ($lastValueDate == false) {
	$lastValueDate = date('Y-01-01');
}else $lastValueDate = date('Y-m-d', strtotime($lastValueDate));



?>
<div id='div_alertSetPulse' style="display: none;"></div>
<legend>
	<center class="title_cmdtable">{{<?php echo '[pulseCounter]['.$eqLogic->getName();?>]</span>}}
	</center>
</legend>
      
 <center class="alert alert-info">
     <span id="internalNetworkAccess">{{Corriger l'index de l'équipement jusqu'à ce qu'il corrésponde au compteur réel. Les impulsions additionnelles seront automatiquement détérminées }} 
     </span>                                          
 </center>                                                                                              
    
    
<br>                        
<form class="form-horizontal" id="form_manuelValue">
	<fieldset>
      <!--
		<div class="form-group">
			<label class="col-sm-4 control-label">{{Dates}}</label>
			<div class="col-xs-5 col-sm-3">
				<input type="date" class="DateAttr form-control" data-l1key="start" value="<?php echo $lastValueDate; ?>" />
			</div>
      		<label class="col-xs-1 col-sm-1 control-label"><center>{{Au}}</center></label>
      
      
      		<div class="col-xs-5 col-sm-3">
				<input type="date" class="DateAttr form-control" data-l1key="end" placeholder="AAAA/MM/JJ"/>
			</div>
		</div>
		      
      
      -->
      
		
<!-- ************************** -->
      
  		<div class="form-group">
      		<label class="col-sm-4 control-label">Infos actuelles</label>
			<div class="col-xs-6 col-sm-7">
				<span class="label label-info label-xs eqCmdAttr" id="basepulse" title="basepulse" ><?php echo $basepulse; ?></span>
                  <span>+</span>
				<span class="label label-info label-xs eqAttr" id="indexPulseAdd" title="Impulsions additionnelles"><?php echo $indexPulseAdd; ?></span>
				<span>=</span>
                <span class="label label-info label-xs eqAttr" id="old_pulseIndex" title="Index impulsions"><?php echo $pulse_value; ?></span>
				<i class="icon kiko-arrow-right " style="top: 2px;position: inherit;"></i>
                <span class="label label-success label-sm eqAttr" id="now_IndexVol" title="Index en volume"><?php echo $vol_index; ?></span>
                
      		</div>
            <div class=""></div>
      		<div class="col-xs-5 col-sm-2">
                 <span class="label label-info label-xs eqAttr hidden" id="old_indexVolCible" title="Volume correcteur"><?php echo $indexVolCible; ?></span>
           	</div>        
      		
      	</div>      
      
<!-- ************************** -->      
  		<div class="form-group">
      		<label class="col-sm-4 control-label">Correction des impulsions</label>
			<div class="col-xs-5 col-sm-3">
      			<div class="content-sm ">
    <div class="input-group buttons">
      <span class="input-group-btn" style="position: relative;">
        <a class="btn btn-default btn-sm bt_minus roundedLeft" id="bt_minus"><i class="fas fa-minus"></i></a>
      </span>
      <input type="text" class="input-sm in_value form-control" id="in_value" value="" />
      <span class="input-group-btn" style="position: relative;">
        <a class="btn btn-default btn-sm bt_plus roundedRight" id="bt_plus"><i class="fa fa-plus"></i></a>
      </span>
    </div>
  </div>
  <template><div>step : 1 ({{Pas utilisé pour le changement de valeur}}).</div></template>
      		</div>
      		<div class="col-xs-1 col-sm-1"></div>
      		<div class="col-xs-5 col-sm-2"></div>
      	</div>
  		
        <div class="form-group">
      		<label class="col-sm-4 control-label">Résultats</label>
			<div class="col-xs-6 col-sm-7" id="result" style="display: none;">
				<span class="label label-info label-xs eqAttr" title="basepulse" id="basepulse_r"></span>    
				<span>+</span>
                <span class="label label-warning label-xs eqAttr" id="new_indexPulseAdd" title="new_indexPulseAdd"></span>
				<span>=</span>
                <span class="label label-primary eqAttr" id="indexPulse_cible" title="indexPulse_cible"></span>
				<i class="icon kiko-arrow-right " style="top: 2px;position: inherit;"></i>
                <span class="label label-success label-sm eqAttr" id="indexVol_cible" title="indexVol_cible"></span>
				
                    
      		</div>
            <div class=""></div>
      		<div class="col-xs-1 col-sm-1">
                   
      		</div>
      		
      	</div>
        <br/>
<!-- ************************** -->
      	<div class="form-group">
      		<label class="col-sm-4 control-label"></label>
			<div class="col-xs-6 col-sm-7" >
				<a class="btn btn-success pull-right btn-sm disabled" data-action="save" id="bt_save_eq"><i class="fas fa-check-circle"></i> Sauvegarder</a>  
				
                    
      		</div>
            <div class=""></div>
      		<div class="col-xs-1 col-sm-1">
                   
      		</div>
      		
      	</div>
	</fieldset>
</form>

                   
                   
                   

  
  <script>
//////////////////////
    $('#bt_plus').on('click', function () {
		if ($('#in_value').value() == '' ) {
         	$('#in_value').value(parseFloat($('#now_IndexVol').value()))
			return;
        }  
		if (parseFloat($('#in_value').value()) !== '' ) {
            $('#in_value').value(parseInt((parseFloat($('#in_value').value())+1)*1000)/1000)
        	$('#in_value').trigger('change')
        }
    })
/////////////////////////////////////////////////
    $('#bt_minus').on('click', function () {
        if ($('#in_value').value() == '' ) {
			$('#in_value').value(parseFloat($('#now_IndexVol').value()));
			return;
        }
   		if (parseFloat($('#in_value').value()) !== '') {
			$('#in_value').value(parseInt((parseFloat($('#in_value').value())+1)*1000)/1000)
        	$('#in_value').trigger('change')
      	}
    })
/////////////////////////////////////////////////
    $('#in_value').on('keyup',function(){
		let indexVol_cible = parseFloat($('#in_value').value().replace("#unite#", "").replace(" ", "")).toFixed(3);
    	if (isNaN(indexVol_cible)){
			$(this).value('');
			return;
        }
		//$('#in_value').value(parseInt(($('#in_value').value()) * 1000)/1000);//(parseFloat($('#in_value').value()) - step).toFixed(3)
        $('#in_value').trigger('change')
    })
    
/////////////////////////////////////////////////////////////////////////////
    $('#in_value').on('change', function () {
      if (typeof timerHandle !== 'undefined') {
        clearTimeout(timerHandle)
      }
      timerHandle = setTimeout(function() {
        var indexVol_cible = parseFloat($('#in_value').value().replace("#unite#", "").replace(" ", "")).toFixed(3);
        	
        if (isNaN(indexVol_cible)){
			console.error("change:: La valeur saisie n'est pas un nombre : " + indexVol_cible);
			$('#new_indexPulseAdd').empty();
			$('#indexPulse_cible').empty();
			$('#indexVol_cible').empty();
			$('#basepulse_r').empty();
          	$('#result').hide();
			return;
        }
        console.log("change:: indexVol_cible:  " + indexVol_cible);        
      	var	basepulse = parseInt($('#basepulse').value());
        		console.log("basepulse:  " + basepulse);
        
        var	pulseRatio = parseFloat("<?php echo $pulseRatio; ?>").toFixed(2);
        		console.log("pulseRatio:  " + pulseRatio);
		
        var indexPulse_cible = parseInt(indexVol_cible * pulseRatio);
        	console.log("indexPulse_cible:  " + indexPulse_cible);
        
        var new_indexPulseAdd = indexPulse_cible - basepulse;
      			console.log("old_indexPulseAdd:  " + new_indexPulseAdd);
        
        var old_indexPulseAdd = parseInt($('#indexPulseAdd').value());
      			console.log(" * indexPulseAdd ***   " + old_indexPulseAdd +" => " + new_indexPulseAdd);
		
        var old_indexVol = $('#now_IndexVol').value();
        		console.log(" * IndexVol ***   " + old_indexVol +" => " + indexVol_cible);
        
        
        $('#new_indexPulseAdd').value(new_indexPulseAdd);
      		$('#result').show();
        	$('#indexPulse_cible').value(indexPulse_cible);
      		$('#indexVol_cible').value(indexVol_cible);
      		$('#basepulse_r').value(basepulse);
        	$('#bt_save_eq').removeClass('disabled');
        
        
      }, 500)
    })
	 
         
	$('#bt_save_eq').on('click',function(){
      	var indexVol_cible = $('#indexVol_cible').value();
    	var config = {
          //indexPulse_cible:$('#indexVol_cible').value(), 
          indexPulseAdd:$('#new_indexPulseAdd').value(),
          PulseAdd_date:new Date().toLocaleString().replaceAll("/", "-"), 
        };
      	console.log("bt_save_eq:: indexVol_cible:  " + indexVol_cible );
		$.ajax({
			type: "POST",
			url: "plugins/pulseCounter/core/ajax/pulseCounter.ajax.php",
			dataType: 'json',
			data: {
				action: 'setEqConfig',
				eqLogic_id : eqLogic_id,
              	dataj: config,
              	configKey : 'indexVolCible',
              	value : indexVol_cible
            },
			error: function (request, status, error) {
				handleAjaxError(request, status, error,$('#div_alertSetPulse'));
			},
			success: function (data) {
				if (data.state != 'ok') {
					$('#div_alertSetPulse').showAlert({message: "save_eq " + data.result, level: 'danger'});
					return;
				}
				$('#div_alertSetPulse').showAlert({message: '{{Ajout réussi}}', level: 'success'});
              	location.reload();
            }
		});
	});


</script>