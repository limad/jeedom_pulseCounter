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
$pulseCounter = pulseCounter::byId(init('id'));
$pulse_cmd = $pulseCounter->getCmd(null, 'pulse');
$pulse_cmd->execCmd();
$lastValue = $pulse_cmd->getCollectDate();//date('Y-m-d', strtotime($pulse_cmd->getCollectDate()));
log::add('pulseCounter', 'debug',__FUNCTION__ . ' lastValue: '. $lastValue );
if ($lastValue == false) {
	$lastValue = date('Y-01-01');
}else $lastValue = date('Y-m-d', strtotime($lastValue));

?>
<div id='div_alertHistlValues' style="display: none;"></div>
<legend>
	<center class="title_cmdtable">{{remplacer l'historique des commandes <?php echo 'pulseCounter : '.$pulseCounter->getName();?><span class="hidden-sm hidden-md" '></span>}}
	</center>
</legend>
                          
<br>                        
<form class="form-horizontal" id="form_manuelValue">
	<fieldset>
      
      <div class="form-group">
			<label class="col-sm-3 control-label">{{Dates}}</label>
			<div class="col-xs-5 col-sm-3">
				<input type="date" class="DateAttr form-control" data-l1key="start" value="<?php echo $lastValue; ?>" />
			</div>
      		<label class="col-xs-1 col-sm-1 control-label"><center>{{Au}}</center></label>
      
      
      		<div class="col-xs-5 col-sm-3">
				<input type="date" class="DateAttr form-control" data-l1key="end" placeholder="AAAA/MM/JJ"/>
			</div>
		</div>
		      
      
      
      
		
		<div class="form-group">
			<label class="col-sm-3 control-label">{{Volume Jour (m³)}}</label>
			<div class="col-xs-11 col-sm-7">
				<input type="number" class="manuelValueAttr form-control" data-l1key="vol_jour" placeholder="m³"/>
			</div>
		</div>
  
  		<div class="form-group">
			<label class="col-sm-3 control-label">{{Conso Jour (kWh)}}</label>
			<div class="col-xs-11 col-sm-7">
				<input type="number" class="manuelValueAttr form-control" data-l1key="conso_jour" placeholder="kWh"/>
			</div>
		</div>
  		<div class="form-group">
			<label class="col-sm-3 control-label">{{coef_conversion (kWh/m³)}}</label>
			<div class="col-xs-11 col-sm-7">
				<input type="number" class="manuelValueAttr form-control" data-l1key="coef_conv" placeholder="11.3"/>
			</div>
		</div>
  
  
  
  		<div class="form-group">
			<label class="col-sm-3 control-label">{{Consommation (kWh)}}</label>
			<div class="col-xs-11 col-sm-7">
				<input type="number" class="manuelValueAttr form-control" data-l1key="consumption" placeholder="kWh"/>
			</div>
		</div>
  
  
  
  
		
        <div class="form-group">
			<label class="col-sm-3 control-label"></label>
			<div class="col-xs-11 col-sm-7">
				<a class="btn btn-success pull-right btn-sm" id="bt_saveHistlValue"><i class="fas fa-check-circle"></i> {{Valider}}</a>

			</div>
		</div>              
                      
                      
		
	</fieldset>
</form>

<script type="text/javascript">
	$('#bt_saveHistlValue').on('click',function(){//setHistlValue
		$.ajax({
			type: "POST",
			url: "plugins/pulseCounter/core/ajax/pulseCounter.ajax.php",
			data: {
				action: "setHistlValue",
				dates : json_encode($('#form_manuelValue').getValues('.DateAttr')[0]),
              	values : json_encode($('#form_manuelValue').getValues('.manuelValueAttr')[0]),
				eqLogic_id : eqLogic_id
			},
			dataType: 'json',
			error: function (request, status, error) {
				handleAjaxError(request, status, error,$('#div_alertHistlValues'));
			},
			success: function (data) {
				if (data.state != 'ok') {
					$('#div_alertHistlValues').showAlert({message: data.result, level: 'danger'});
					return;
				}
				$('#div_alertHistlValues').showAlert({message: '{{Ajout réussi}}', level: 'success'});
			}
		});
	});
</script>