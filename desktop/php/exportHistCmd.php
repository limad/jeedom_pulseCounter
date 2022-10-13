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

$lastValue = date('Y-01-01');
//exportToJson(5139);
function exportToJson($_cmd_id) {
	$cmd = cmd::byId($_cmd_id);
	if (!is_object($cmd)) {
		log::add(__CLASS__, 'error', __FUNCTION__ . '  unknown cmd : '.$_cmd_id);		  
		return false;
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
			  	$return[$hist_time] = $hist_value;
              	echo "<br>$hist_time => $hist_value";
            }
        }
  /*
  		if (!is_array($histories)) {
			$histories = array($histories);
		}
		$return = '"' . __('Commande', __FILE__) . '";"' . '"' . __('Date', __FILE__) . '";"' . __('Valeur', __FILE__) . '"' . "\n";
		foreach ($histories as $history) {
			$return .=	'"' . $history->getCmd()->getHumanName() . '";"' . '"' . $history->getDatetime() . '";"' . $history->getValue() . '"' . "\n";
		}*/
		return $return;
	}






?>
<div id='div_alertExportHistCmd' style="display: none;"></div>
<legend>
	<center class="title_cmdtable">{{remplacer l'historique des commandes <span class="hidden-sm hidden-md" '></span>}}
	</center>
</legend>
                          
<br>                        
<form class="form-horizontal" id="form_manuelValue">
	<fieldset>
      
      <div class="form-group">
			<label class="col-sm-3 control-label">{{Dates}}</label>
			<div class="col-xs-4 col-sm-2">
				<input type="date" class="DateAttr form-control" data-l1key="start" value="<?php echo $lastValue; ?>" />
			</div>
      		<label class="col-xs-1 col-sm-1 control-label"><center>{{Au}}</center></label>
      
      
      		<div class="col-xs-4 col-sm-2">
				<input type="date" class="DateAttr form-control" data-l1key="end" placeholder="AAAA/MM/JJ"/>
			</div>
		</div>
		      
      
      
      
		
		<div class="form-group">
			<label class="col-sm-3 control-label">{{Commande source}}</label>
			<div class="col-xs-4 col-sm-5">
				<div class="input-group">
					<input class="eqLogicAttr form-control" disabled data-mono="1"/>
					<span class="input-group-btn">
						<a class="btn btn-default listCmdI listCmdInfoNumeric cmd_cible" ><i class="fas fa-list-alt"></i></a>
      
					</span>
      			</div>
      			<div class="col-xs-4 col-sm-5 label label-info label-sm cmd_human"></div>
      		</div>
      	</div>
  		
  		  
  
  
		<!-- bt_histToJson ********************************* -->
        <div class="form-group">
			<label class="col-sm-3 control-label"></label>
			<div class="col-xs-4 col-sm-5">
				<a class="btn btn-info pull-right btn-sm" id="bt_histToJson"><i class="fas fa-check-circle"></i> {{Importer}}</a>
				 
			</div>
		</div> 
      <br><br>
      <!-- ********************************* -->
      
      
      
      <div id="exportForm" style="overflow-x: hidden; display: none;">
      <div class="form-group">
			<label class="col-sm-3 control-label">{{Commande cible}}</label>
			<div class="col-xs-4 col-sm-5">
				<div class="input-group">
					<input class="eqLogicAttr form-control" disabled data-mono="1"/>
					<span class="input-group-btn">
						<a class="btn btn-default listCmdI listCmdInfoNumeric cmd_cible" ><i class="fas fa-list-alt"></i></a>
      
					</span>
      			</div>
      			<div class="col-xs-4 col-sm-5 label label-warning label-sm cmd_human"></div>
      		</div>
      	</div>
      
      
  		
      	<div class="form-group">
			<label class="col-sm-3 control-label"></label>
			<div class="col-xs-4 col-sm-5">
				<a class="btn btn-warning pull-right btn-sm" id="bt_jsonToHist"><i class="fas fa-check-circle"></i> {{Exporter}}</a>
				 
			</div>
		</div>
     </div>
      
        <div class="form-group">
			<label class="col-sm-3 control-label"></label>
			<div class="col-xs-4 col-sm-5">
      			<style> 
                    textarea {
                      
                      font-family: "CamingoCode";
                      font-size: 13px;
                      line-height: 17px;
                    }
                    </style>
				<textarea id="code" name="code" style="height: 40em;width: 100%;color: #e6db74 !Important;display: none"></textarea>
			</div>
		</div>               
                      
		
	</fieldset>
</form>

<script>
	$(".listCmdInfoNumeric").off('click').on('click', function () {
		var el = $(this).closest('.input-group').find('input');
      	var el_hname = $(this).closest('.input-group').siblings('.cmd_human');
		console.log('cmd_human: ' + el_hname.value());
        jeedom.cmd.getSelectModal({cmd: {type: 'info', subType : 'numeric'}}, function (result) {
            if (el.attr('data-mono') == "1") {
              	el_hname.value(result.human);
              	el.value(result.cmd.id);
            } else {
              	el_hname.atCaret('insert', result.human);;
              	el.atCaret('insert', result.cmd.id);
            }
          	
		});
      	
	});

	let preJson = '123';

	$('#bt_histToJson').on('click',function(){
      	var inputcmd = $(this).closest('.form-group').parent().find('.listCmdInfoNumeric');
      	var el = inputcmd.closest('.input-group').find('input');
      	var cmd_id = el.value();
      	if(cmd_id == '') {
          	$('#div_alertExportHistCmd').showAlert({message: "la commande ne peut être vide " + cmd_id, level: 'danger'});
			console.error("la commande source ne peut être vide " + cmd_id);		
          	return;
          
        }
		console.log("importCmd cmd_id: " + cmd_id);
        $.ajax({
			type: "POST",
			url: "plugins/pulseCounter/core/ajax/pulseCounter.ajax.php",
			data: {
				action : "HistToJson",
				cmd_id : cmd_id,
            },
			dataType: 'json',
			error: function (request, status, error) {
				handleAjaxError(request, status, error,$('#div_alertExportHistCmd'));
			},
			success: function (data) {
				if (data.state != 'ok') {
					$('#div_alertExportHistCmd').showAlert({message: data.result, level: 'danger'});
					return;
				}
				$('#div_alertExportHistCmd').showAlert({message: '{{Import réussi}}', level: 'success'});
              	$('#exportForm').show();
              	//$('#code').show().html(data.result);
              	$('#code').show();
              	//var obj = JSON.parse(data.result);
                //var pretty = JSON.stringify(obj, undefined, 4);
              	//$('#code').value(pretty);
              	ImportJson = data.result;
              	$('#code').value(data.result);
			}
		});
        
	});//ExportHistCmd // ExportHistCmd
	$('#bt_jsonToHist').on('click',function(){
      	var inputcmd = $(this).closest('.form-group').parent().find('.listCmdInfoNumeric');
      	var el = inputcmd.closest('.input-group').find('input');
      	var cmd_id = el.value();
      	var jsondata = $('#code').value();
      	if(cmd_id == '') {
          	$('#div_alertExportHistCmd').showAlert({message: "la commande ne peut être vide " + cmd_id, level: 'danger'});
			console.error("la commande source ne peut être vide " + cmd_id);		
          	return;
        }
		console.log("cible cmd_id: " + cmd_id);
      	console.log("ImportJson: " + ImportJson);
      	console.log("code: " + jsondata);
        $.ajax({
			type: "POST",
			url: "plugins/pulseCounter/core/ajax/pulseCounter.ajax.php",
			data: {
				action : "jsonToHist",
				cmd_id : cmd_id,
              	data : jsondata,
			},
			dataType: 'json',
			error: function (request, status, error) {
				handleAjaxError(request, status, error,$('#div_alertExportHistCmd'));
			},
			success: function (data) {
				if (data.state != 'ok') {
					$('#div_alertExportHistCmd').showAlert({message: data.result, level: 'danger'});
					return;
				}
				$('#div_alertExportHistCmd').showAlert({message: '{{Json to History réussi : }}' + data.result + ' elements', level: 'success'});
              	$('#exportForm').show();
              	//$('#code').show().html(data.result);
			}
		});
        
	});
</script>