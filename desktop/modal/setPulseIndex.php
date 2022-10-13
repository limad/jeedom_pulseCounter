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
$pulse_cmd = $eqLogic->getCmd(null, 'total_pulses');
$pulse_value = $pulse_cmd->execCmd();
$vol_index_cmd = $eqLogic->getCmd(null, 'vol_index');
if (!is_object($vol_index_cmd)) {
    $vol_index_cmd = $eqLogic->getCmd(null, 'conso_index');
}
$vol_index = $vol_index_cmd->execCmd();

$pulseUnitBig = $eqLogic->getConfiguration('pulseUnitBig', '');
    	

$indexVolCible = $eqLogic->getConfiguration('indexVolCible');
$pulseRatio = $eqLogic->getConfiguration('pulseRatio');
//
$indexPulseAdd = $eqLogic->getConfiguration('indexPulseAdd', 0);
$input_pulses_cmd = $eqLogic->getCmd(null, 'input_pulses');
if (is_object($input_pulses_cmd)) {
    $input_pulses = $input_pulses_cmd->execCmd();
}  


$lastValueDate = $pulse_cmd->getCollectDate();//date('Y-m-d', strtotime($pulse_cmd->getCollectDate()));
//log::add('pulseCounter', 'debug',__FUNCTION__ . ' lastValue: '. $lastValueDate );
if ($lastValueDate == false) {
	$lastValueDate = date('Y-01-01');
}else $lastValueDate = date('Y-m-d', strtotime($lastValueDate));



?>
<div class="row row-overflow">
  <div id='div_alertSetPulse' style="display: none;"></div>

      
 <center class="alert alert-info">
     <span id="internalNetworkAccess">{{Corriger l'index de l'équipement <strong><?php echo '[pulseCounter]['.$eqLogic->getName();?>]</strong> jusqu'à ce qu'il corrésponde au compteur réel.<br>Les impulsions additionnelles seront automatiquement détérminées }} 
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
				<span class="label label-info label-xs eqCmdAttr" id="input_pulses" title="input_pulses" ><?php echo $input_pulses; ?></span>
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
				<span class="label label-info label-xs eqAttr" title="input_pulses" id="input_pulses_r"></span>    
				<span>+</span>
                <span class="label label-warning label-xs eqAttr" id="sp_new_indexPulseAdd" title="new_indexPulseAdd"></span>
				<span>=</span>
                <span class="label label-primary eqAttr" id="sp_indexPulse_cible" title="indexPulse_cible"></span>
				<i class="icon kiko-arrow-right " style="top: 2px;position: inherit;"></i>
                <span class="label label-success label-sm eqAttr" id="sp_indexVol_cible" title="indexVol_cible"></span>
				
                    
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
    var new_indexPulseAdd = parseInt((parseFloat($('#in_value').value())-1)*1000)/1000;
//////////////////////
    $('#bt_plus').on('click', function () {
		if ($('#in_value').value() == '' || $('#in_value').value() == NaN) {
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
        if ($('#in_value').value() == '' || $('#in_value').value() == NaN) {
			$('#in_value').value(parseFloat($('#now_IndexVol').value()));
			return;
        }
   		if (parseFloat($('#in_value').value()) !== '') {
			$('#in_value').value(parseInt((parseFloat($('#in_value').value())-1)*1000)/1000)
        	$('#in_value').trigger('change')
              
      	}
    })
/////////////////////////////////////////////////
    $('#in_value').on('keyup',function(){
		if (typeof timerHandle !== 'undefined') {
        	clearTimeout(timerHandle)
      	}
      	timerHandle = setTimeout(function() {
            let indexVol_set = parseInt(parseFloat($('#in_value').value().replace("#unite#", "").replace(" ", "")) * 1000)/1000;
            if (isNaN(indexVol_set)){
                $(this).value('');
                return;
            }
            $('#in_value').value(indexVol_set);//(parseFloat($('#in_value').value()) - step).toFixed(3)
            $('#in_value').trigger('change')
        }, 1000)
    })
    
/////////////////////////////////////////////////////////////////////////////
    $('#in_value').on('change', function () {
      if (typeof timerChange !== 'undefined') {
        clearTimeout(timerChange)
      }
      timerChange = setTimeout(function() {
        var indexVol_set = parseFloat($('#in_value').value().replace("#unite#", "").replace(" ", ""));
        	//indexVol_set = parseInt(parseFloat($('#in_value').value().replace("#unite#", "").replace(" ", "")) * 1000)/1000;
        if (isNaN(indexVol_set)){
			console.error("change:: La valeur saisie n'est pas un nombre : " + indexVol_set);
			$('#sp_new_indexPulseAdd').empty();
			$('#sp_indexPulse_cible').empty();
			$('#indexVol_set').empty();
			$('#input_pulses_r').empty();
          	$('#result').hide();
			return;
        }
        console.log("change:: indexVol_set:  " + indexVol_set);        
      	var	input_pulses = parseFloat(parseFloat($('#input_pulses').value())*1000).toFixed(0)/1000;
        		console.log("input_pulses:  " + input_pulses);
        
        var	pulseRatio = parseFloat("<?php echo $pulseRatio; ?>").toFixed(2);
        		console.log("pulseRatio:  " + pulseRatio);
		//parseInt((indexVol_set * pulseRatio)*100)/100;
        var indexPulse_cible = parseInt((indexVol_set * pulseRatio)*1000)/1000;//parseInt((parseFloat(indexVol_set * pulseRatio))*100)/100;// parseFloat(indexVol_set * pulseRatio).toFixed(1);
        	console.log("indexPulse_cible:  " + indexPulse_cible+ " ("+indexVol_set + " x " + pulseRatio + ")");
        
        new_indexPulseAdd = parseFloat((indexPulse_cible - input_pulses)*1000).toFixed(0)/1000;
        
        //indexPulse_cible - input_pulses;//parseFloat(parseInt(()*1000)/1000);//indexPulse_cible - input_pulses;
      			console.log(" *** new_indexPulseAdd ****:  " + new_indexPulseAdd + " ("+indexPulse_cible +" - " + input_pulses + ")");
        
        //var old_indexPulseAdd = parseInt($('#indexPulseAdd').value());
      			//console.log(" * old_indexPulseAdd: " + old_indexPulseAdd);
		
        //var old_indexVol = $('#now_IndexVol').value();
        		//console.log(" * old_indexVol " + old_indexVol +" to " + indexVol_set);
        
        var indexVol_cible = parseFloat((input_pulses + new_indexPulseAdd)*1000/pulseRatio).toFixed(0)/1000;//parseFloat((input_pulses + new_indexPulseAdd) * pulseRatio*);
        	console.log(" * indexVol_cible ***   " + indexVol_cible+ " ("+input_pulses +" + " + new_indexPulseAdd + ") x " + pulseRatio);
        
        
		$('#sp_new_indexPulseAdd').value(new_indexPulseAdd);
		$('#result').show();
		$('#sp_indexPulse_cible').value(indexPulse_cible);
		$('#sp_indexVol_cible').value(indexVol_cible + " " +"<?php echo $pulseUnitBig; ?>");
		$('#input_pulses_r').value(input_pulses);//input_pulses
		$('#bt_save_eq').removeClass('disabled');
        
        
      }, 200)
    })
	 
         
	$('#bt_save_eq').on('click',function(){
      	//var indexVol_cible = $('#sp_indexVol_cible').value();
    	var config = {
          indexPulseAdd: $('#sp_new_indexPulseAdd').value(),
          PulseAdd_date: new Date().toLocaleString().replaceAll("/", "-"), 
        };
      	console.log("bt_save_eq:: indexPulseAdd:  " + new_indexPulseAdd );
		$.ajax({
			type: "POST",
			url: "plugins/pulseCounter/core/ajax/pulseCounter.ajax.php",
			dataType: 'json',
			data: {
				action: 'setEqConfig',
				eqLogic_id : eqLogic_id,
              	dataj: config,
              	//configKey : 'indexVolCible',
              	//value : indexVol_cible
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
  
  </div>