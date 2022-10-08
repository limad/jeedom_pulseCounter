
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
var configEqType = $('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value();
    

$('#bt_healthpulseCounter').on('click', function () {
    $('#md_modal').dialog({title: "{{Santé pulseCounter}}"});
    $('#md_modal').load('index.php?v=d&plugin=pulseCounter&modal=health').dialog('open');
});


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});


function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    	var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
		tr += '<legend><i class="fas fa-info"></i> Commandes Infos</legend>';
		tr += '<td>';
		tr += '<center><span class="cmdAttr" data-l1key="id" title="'+ init(_cmd.logicalId) +'" ></center></span>';
		tr += '</td>';
		
		tr += '<td>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width: 90%;" >';
		tr += '</td>';
	   
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="subType" style="width: 90%;"></span>';
		tr += '</td>';
	   
		tr += '<td>';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label> </span> ';
		if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
			tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		}
	  	if (init(_cmd.subType) == 'numeric'){
            tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="{{Unite}}" style="width:30%; max-width:80px;display:inline-block; margin-left:15px;"> ';
        }
        tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure" title="Configurer '+ init(_cmd.name) +'"><i class="fas fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"  title="Evaluer '+ init(_cmd.name) +'"><i class="fas fa-rss"></i> {{Evaluer}}</a>';
		}
    
    	tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    	tr += '</tr>';
    
   		if (init(_cmd.type) == 'info') {
    		$('#table_cmdi tbody').append(tr);
    		$('#table_cmdi tbody tr:last').setValues(_cmd, '.cmdAttr');
    	}
    
    
    //var lgid = init(_cmd.logicalId);
    //init(_cmd.logicalid).substring(init(_cmd.logicalid).length-6, init(_cmd.logicalid).length)
    else if ( init(_cmd.logicalId).substring(init(_cmd.logicalId).length-6, init(_cmd.logicalId).length) !== 'mobile'){
		
    
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '"  title="'+ init(_cmd.logicalId) +'">';
		tr += '<legend><i class="fas fa-info"></i> Commandes mobiles</legend>';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" ></span>';
		tr += '</td>';
		
		tr += '<td>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width: 90%;" >';
		tr += '</td>';
	   
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="subType"></span>';
		tr += '</td>';
	   
		tr += '<td>';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
		if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
			tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		}
	  
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"  title="Configurer '+ init(_cmd.name) +'"><i class="fas fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"  title="Tester '+ init(_cmd.logicalId) +'"><i class="fas fa-rss"></i> {{Tester}}</a>';
		}
    
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmda tbody').append(tr);
    $('#table_cmda tbody tr:last').setValues(_cmd, '.cmdAttr');
		
    } 
  	else {
		
    
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
		tr += '<legend><i class="fas fa-info"></i> Commandes mobiles</legend>';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" ></span>';
		tr += '</td>';
		
		tr += '<td>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" >';
		tr += '</td>';
	   
		tr += '<td>';
		//tr += '<span class="cmdAttr" data-l1key="type"></span>';
		//tr += '   /   ';
		tr += '<span class="cmdAttr" data-l1key="subType"></span>';
		tr += '</td>';
	   
		tr += '<td>';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
		if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
			tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		}
	  
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fas fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
		}
    
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmdam tbody').append(tr);
    $('#table_cmdam tbody tr:last').setValues(_cmd, '.cmdAttr');
		
    }
    
}

$(".listCmdInfoNumeric").off('click').on('click', function () {
  var el = $(this).closest('.input-group').find('input');
  jeedom.cmd.getSelectModal({cmd: {type: 'info', subType : 'numeric'}}, function (result) {
    console.log('data-mono: ' + el.attr('data-mono'));
    if (el.attr('data-mono') == "1") {
      el.value(result.human);
    } else {
      el.atCaret('insert', result.human);
    }
  });
});

$(".eqLogic").off('click','.listCmdInfo').on('click','.listCmdInfo', function () {
  var el = $(this).closest('.form-group').find('.eqLogicAttr');
  jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
    console.log('listCmdInfo: ');
    if (el.attr('data-mono') == "1") {
      el.value(result.human);
    } else {
      el.atCaret('insert', result.human);
    }
  });
});






$('#bt_accessUrls').on('click', function () {
  	$("#md_modalPimp").dialog({
        title: "{{URL à appeler}}",
      	closeText: '',
        autoOpen: false,
        modal: true,
        minHeight: 200,
        height: 200,
        minWidth: 700,
        width: 700,
        position: { my: 'top', at: 'top+150' },
        open: function () {
          $("body").css({overflow: 'hidden',display: 'block'});
        },
        beforeClose: function (event, ui) {
          $("body").css({overflow: 'inherit'});
        }
    });
  	$('#md_modalPimp').dialog('open');
});
$('#bt_networkTab').on('click', function() {
  var tableBody = $('#networkInterfacesTable tbody')
  if (tableBody.children().length == 0) {
    jeedom.network.getInterfacesInfo({
      error: function(error) {
        $.fn.showAlert({
          message: error.message,
          level: 'danger'
        })
      },
      success: function(_interfaces) {
        var div = ''
        for (var i in _interfaces) {
          div += '<tr>'
          div += '<td>' + _interfaces[i].ifname + '</td>'
          div += '<td>' + (_interfaces[i].addr_info && _interfaces[i].addr_info[0] ? _interfaces[i].addr_info[0].local : '') + '</td>'
          div += '<td>' + (_interfaces[i].address ? _interfaces[i].address : '') + '</td>'
          div += '</tr>'
        }
        console.log(_interfaces)
        tableBody.empty().append(div)
      }
    })
  }
});


$('#bt_SyncCmds').on('click', function () {
	$.ajax({
		type: "POST", // méthode de transmission des données au fichier php
		url: "plugins/pulseCounter/core/ajax/pulseCounter.ajax.php", 
		data: {
			action: "SyncCmds",
			id: $('.eqLogicAttr[data-l1key=id]').value()
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) { 
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: "SyncCmds:: " + data.result, level: 'danger'});
                return;
            }
            $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
            setTimeout( function() {location.reload();}, 1000);
                
       }
	});
});
	
$('#bt_cmdConfigureCopyHistory').off('click').on('click', function() {
  	var targetid= $(this).attr('data-cmd_id')
    jeedom.cmd.byId({ id: targetid,
		success:  function(cmdi){
            console.log('cmd name: ' + cmdi.subType);
          	
            jeedom.cmd.getSelectModal({
                cmd: {type: 'info',subType: cmdi.subType}}, function(source) {
              		console.log('source: ' + JSON.stringify(cmdi));
                    var source_id = source.cmd.id
                    var	source_name = source.human
                    var	boottext = '{{Êtes-vous sûr de vouloir copier l\'historique de}} <strong>' + source.human + '</strong> {{vers}} <strong>' + cmdi.name + '</strong> ? </br> {{Il est conseillé de vider l\'historique de la commande}} : <strong>' + cmdi.name + '</strong> {{avant la copie}}'

                    bootbox.confirm(boottext, function(result) {
                        if (result) {
                            jeedom.history.copyHistoryToCmd({
                            	source_id: source.cmd.id,
                            	target_id: targetid,
                            	error: function(error) {
                              	$('#md_displayCmdConfigure').showAlert({
                                	message: error.message,
                                	level: 'danger'
                              	})
                            },
                            success: function(data) {
                              $('#md_displayCmdConfigure').showAlert({
                                message: '{{Historique copié avec succès}}',
                                level: 'success'
                              })
                            }
                          })
                        }
                    })
            })
          
          	jeedom.eqLogic.byId ({
            id: cmdi.eqLogic_id, // id objet
                success:  function(eqlogic) {
                    //console.log("eq lastCommunication: " + eqlogic.status.lastCommunication)  
                }
            })
        }

	})
    /*var target = [];
    cmd.name = $(this).attr('data-name');
    cmd.id = $(this).attr('data-cmd_id');
    cmd.subType = $(this).attr('data-subType');*/
   
	
});


$('#bt_HistlValue').on('click', function () {//bt_HistlValue
  $('#md_modal').dialog({title: "{{Valeur manuelle}}"});
  $('#md_modal').load('index.php?v=d&plugin=pulseCounter&modal=HistlValue&id='+$('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
});

$('#bt_setIndex').on('click', function () {
  	let eqid= $('.eqLogicAttr[data-l1key=id]').value();
  	let redir_url = encodeURIComponent('index.php?v=d&p=pulseCounter&m=pulseCounter&id='+eqid);
    				 
    
	$('#md_modalIndex').dialog({
    	title: "{{Correcteur Impulsions}}",
      	closeText: '',
        autoOpen: false,
        modal: true,
        minHeight: 300,
        height: 300,
        minWidth: 700,
        width: 700,
        position: { my: 'top', at: 'top+150' },
      	open: function () {
          $("body").css({overflow: 'hidden',display: 'block'});
        },
        beforeClose: function (event, ui) {
          $("body").css({overflow: 'inherit'});
        }
    });
  $('#md_modalIndex').load('index.php?v=d&plugin=pulseCounter&modal=setPulseIndex&id='+eqid+'&redir_url='+redir_url).dialog('open');
	//$('#md_modalIndex').load('index.php?v=d&plugin=pulseCounter&modal=setPulseIndex&id='+$('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
});


var cmda = '.cmdAction.expressionAttr[data-l1key=cmd]';
$('body').off('focusout', cmda).on('focusout', cmda,function(event) {
  var type = $(this).attr('data-type')
  var expression = $(this).closest('.' + type).getValues('.expressionAttr')
  var el = $(this)
  	jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function(html) {
  })

})

var old_indexVolCible = $('.eqLogicAttr[data-l1key=configuration][data-l2key=indexVolCible]').value();//
function printEqLogic(_eqLogic) {
	if (!isset(_eqLogic.configuration)) {
    	_eqLogic.configuration = {}
  	}
  	let typeEq = _eqLogic.configuration.type;
  	console.log('typeEq : ' + typeEq )
  	const convJSON = '{"gas":"Gaz", "water":"Eau", "electricity":"Electricité"}';
    if (typeEq != undefined && typeEq != '') {
      	//let htmleqType = '<input disabled="" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type" value="'+JSON.parse(convJSON)[typeEq]+'">';
        //$('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').replaceWith( htmleqType );
      	$('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').attr('disabled', '');
      	$('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').attr('title', 'La modification du type est impossible ! Ok');
      	$('#div_advancedParams').show();
    }
  	if (typeEq == 'gas') {
    }else if (typeEq == 'water') {
    }else if (typeEq == 'electricity') {
    }
  
  
  	console.log('printEqLogic:: old_indexVolCible : ' + old_indexVolCible )
  	old_indexVolCible = _eqLogic.configuration.indexVolCible;
  	var eqLogId = $('.eqLogicAttr[data-l1key=id]').value();
  	$(".eqName").empty().append($('.eqLogicAttr[data-l1key=name]').value());
    $("#internalNetworkAccess").append(eqLogId);
  	$("#externalNetworkAccess").append(eqLogId);
  	
  ///////////////////////////	
  	$('.eqLogicAttr[data-l1key=configuration][data-l2key=countType]').on('change',function(){
        let countType =  $(this).value();

        var ciblebtn = $('.eqLogicAttr[data-l1key=configuration][data-l2key=pulse]').closest('.input-group').find('.listCmdI');
        if (countType == "instant"){
            ciblebtn.removeClass('listCmdInfoNumeric');
            ciblebtn.addClass('listCmdInfo');
        }else {
            ciblebtn.removeClass('listCmdInfo');
            ciblebtn.addClass('listCmdInfoNumeric');
		}
	});
  ///////////////////////////  
  	

  	
} 
function saveEqLogic(_eqLogic) {
  if (!isset(_eqLogic.configuration)) {
    _eqLogic.configuration = {}
  }
  var new_indexVolCible = _eqLogic.configuration.indexVolCible;//
  if (new_indexVolCible != undefined && new_indexVolCible != "" &&  old_indexVolCible != new_indexVolCible) {
    const d = new Date();
	console.log('saveEqLogic indexVolCible_config : ' + old_indexVolCible + "=>" + new_indexVolCible);
	_eqLogic.configuration.indexVolCible_date = d.toLocaleString().replaceAll("/", "-");
    console.log('indexVolCible_date end ' + _eqLogic.configuration.indexVolCible_date)
    //var pulses = (new_indexVolCible/_eqLogic.configuration.pulseRatio);
    //console.log('saveEqLogic pulseAdd : ' + pulses + " ---- " + _eqLogic.configuration.pulseRatio)
    //_eqLogic.configuration.pulseAdd = Math.round(pulses);
  }
  console.log('saveEqLogic end ' )  
  return _eqLogic
}

$('.eqLogicAttr[data-l1key=configuration][data-l2key=coefType]').on('change',function(){
        let val =  $(this).value();
        if (val == "perso"){
            $('#div_cmd_coef').hide();
            $('#coef_perso').show().attr('data-l1key', 'configuration').attr('data-l2key', 'coef_perso');
        }else if (val == "cmd"){
            $('#coef_perso').hide();
            $('#div_cmd_coef').show();
            $('#div_cmd_coef > input').attr('data-l1key', 'configuration').attr('data-l2key', 'coef_cmd');
        }
    }); 

var eqtype = $(".eqLogicAttr[data-l1key=configuration][data-l2key=type]");//=energieType
eqtype.on("change",function(){
  	
    
  	if($(this).value() != ""){
      $('#img_pulseCounterModel').attr('src','plugins/pulseCounter/core/img/room_'+$(this).value()+'.png');
    }else{
      $('#img_pulseCounterModel').attr('src','plugins/pulseCounter/plugin_info/pulseCounter_icon.png');
    }
    var eqtype = $(this).value();
  	if( eqtype == "Home"){
		console.log("type h : " + $(this).value())
		$('#areaheat').show();
		$('#temperature_ext').show();
      	$('#extBoilerState').show();
		$('#is_Boostable').hide();
		$('#AdvancedConfig').hide();
		$('#LiAdvancedConfig').hide();
      	$('#spm_duaration').removeAttr('disabled','');
		$('#away_duaration').removeAttr('disabled','');
		$('#hg_duaration').removeAttr('disabled','');
      	
        $('#showPlannings').hide();
        $('#showHomeMode').hide();
        $('#showRoomModtech').hide();
      
      
    }
  	else {//if(eqtype == "NATherm1" || eqtype == "NRV" || eqtype == "OTM")
      	$('#naEqCcategory').hide();	
    	$('#extBoilerState').hide();
      	//$('#AdvancedConfig').show();
		
  	}
  
});

  

$(".eqLogic").off('click','.listCmdInfo').on('click','.listCmdInfo', function () {
  var el = $(this).closest('.form-group').find('.eqLogicAttr');
  jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
    if (el.attr('data-concat') == 1) {
      el.atCaret('insert', result.human);
    } else {
      el.value(result.human);
    }
  });
});
$('#bt_eqConfigRaw').off('click').on('click',function(){
  let eqid= $('.eqLogicAttr[data-l1key=id]').value();
	$('#md_modal2').dialog({title: "{{Informations brutes}}"});
	$("#md_modal2").load('index.php?v=d&modal=object.display&class=eqLogic&id='+eqid).dialog('open');
});

$('#bt_removeAll').on('click', function () {
	 console.log('init removeAll action');
	 bootbox.confirm('{{Etes-vous sûr de vouloir supprimer tous les équipements ?}}', function (result) {
	        if (result) {
	            $.ajax({
	                type: "POST", // méthode de transmission des données au fichier php
	                url: "plugins/pulseCounter/core/ajax/pulseCounter.ajax.php", 
	                data: {
	                    action: "removeAll",
	                    id: $('.eqLogicAttr[data-l1key=id]').value()
	                },
	                dataType: 'json',
	                global: false,
	                error: function (request, status, error) {
	                    handleAjaxError(request, status, error);
	                },
	                success: function (data) { 
	                    if (data.state != 'ok') {
	                        $('#div_alert').showAlert({message: "removeAll:: " + data.result, level: 'danger'});
                          	location.reload();
	                        return;
	                    }
	                    $('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
	                    setTimeout( function() {location.reload();}, 1000);
                      //$('.li_eqLogic[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
	                }
	            });
	        }
	    });
	 console.log('end removeAll action');
 });