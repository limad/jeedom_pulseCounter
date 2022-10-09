
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

 $('#bt_addEnergyEqLogic').on('click', function () {
    addConfEnergy({});
});

 $('#bt_addEnergyCmd').on('click', function () {
    addCmdToTable({configuration: {period: 1}});
});

 $("#div_confEnergy").delegate('.bt_removeConfEnergy', 'click', function () {
    $(this).closest('.confEnergy').remove();
});

 $("#div_confEnergy").delegate(".listCmdInfo", 'click', function () {
    var el = $(this).closest('.confEnergy').find('.confEnergyAttr[data-l1key=' + $(this).attr('data-input') + ']');
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
        el.atCaret('insert', result.human);
    });
});

 $('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').on('change', function () {
    $('#div_confEnergy .confEnergy:not(.' + $(this).value() + ')').remove();
    if($(this).value()=='electricity'){
        $('.electricityOnly').show();
    }else{
        $('.electricityOnly').hide();
    }
});

 $('.eqLogic').delegate('.eqLogicAttr[data-l1key=configuration][data-l2key=totalCounter]','change', function () {
    setConsumptionCategory();
});

 $('#bt_emptyHistory').on('click', function () {
    bootbox.confirm('{{Êtes-vous sûr de vouloir Supprimer l\'historique de cet équipement ?}}', function (result) {
        if (result) {
            $.ajax({
                type: 'POST',
                url: 'plugins/pulseCounter/core/ajax/pulseCounter.ajax.php',
                data: {
                    action: 'emptyHistory',
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $('#div_alert').showAlert({message: 'Historique supprimé avec succès', level: 'success'});
                }
            });
        }
    });
});

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function saveEqLogic(_eqLogic) {
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {};
    }
    _eqLogic.configuration.confEnergy = $('#div_confEnergy .confEnergy').getValues('.confEnergyAttr');
    _eqLogic.configuration.costEnergy = $('#div_costEnergy .costEnergy').getValues('.costEnergyAttr');
    return _eqLogic;
}

function printEqLogic(_eqLogic) {
    $('#div_confEnergy').empty();
    $('#div_costEnergy').empty();
    if (isset(_eqLogic.configuration)) {
        if (isset(_eqLogic.configuration.confEnergy)) {
            for (var i in _eqLogic.configuration.confEnergy) {
                addConfEnergy(_eqLogic.configuration.confEnergy[i]);
            }
        }
        if (isset(_eqLogic.configuration.costEnergy)) {
            for (var i in _eqLogic.configuration.costEnergy) {
                addCostEnergy(_eqLogic.configuration.costEnergy[i]);
            }
        }
    }
}

function setConsumptionCategory(){
    if($('.eqLogicAttr[data-l1key=configuration][data-l2key=totalCounter]').value() == 1){
        $('.confEnergyAttr[data-l1key=category] option.notTotal').hide();
        $('.confEnergyAttr[data-l1key=category] option.total').show();
    }else{
        $('.confEnergyAttr[data-l1key=category] option.total').hide();
        $('.confEnergyAttr[data-l1key=category] option.notTotal').show();
    }
}

function addConfEnergy(_pulseCounter) {
    if (!isset(_pulseCounter)) {
        _pulseCounter = {};
    }
    var div = '<div class="confEnergy ' + $('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value() + '">';
    if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value() == 'electricity') {
        div += '<div class="form-group">';
        div += '<label class="col-sm-1 control-label">{{Catégorie}}</label>';
        div += '<div class="col-sm-2">';
        div += '<select class="confEnergyAttr form-control input-sm" data-l1key="category" >';
        div += '<option value="other" class="total notTotal">{{Autre}}</option>';
        div += '<option value="multimedia" class="notTotal">{{Multimedia}}</option>';
        div += '<option value="light" class="notTotal">{{Lumière}}</option>';
        div += '<option value="automatism" class="notTotal">{{Automatisme}}</option>';
        div += '<option value="electrical" class="notTotal">{{Electroménager}}</option>';
        div += '<option value="heating" class="notTotal">{{Chauffage}}</option>';
        div += '<option value="hp" class="total">{{HP}}</option>';
        div += '<option value="hc" class="total">{{HC}}</option>';
        div += '</select>';
        div += '</div>';
        div += '<label class="col-sm-1 control-label">{{Puissance}}</label>';
        div += '<div class="col-sm-2 has-success">';
        div += '<div class="input-group">';
        div += '<input class="confEnergyAttr form-control input-sm" data-l1key="power" />';
        div += '<span class="input-group-btn">';
        div += '<a class="btn btn-default btn-sm listCmdInfo btn-success" data-input="power"><i class="fa fa-list-alt"></i></a>';
        div += '</span>';
        div += '</div>';
        div += '</div>';
        div += '<label class="col-sm-1 control-label">{{Consommation}}</label>';
        div += '<div class="col-sm-2 has-warning">';
        div += '<div class="input-group">';
        div += '<input class="confEnergyAttr form-control input-sm" data-l1key="consumption" />';
        div += '<span class="input-group-btn">';
        div += '<a class="btn btn-default btn-sm listCmdInfo btn-warning"  data-input="consumption"><i class="fa fa-list-alt"></i></a>';
        div += '</span>';
        div += '</div>';
        div += '</div>';
        div += '<div class="col-sm-3">';
        div += '<i class="fa fa-minus-circle pull-right cursor bt_removeConfEnergy"></i>';
        div += '</div>';
        div += '</div>';
    } else if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value() == 'water') {//
        div += '<div class="form-group">';
        div += '<label class="col-sm-1 control-label">{{Quantité (L)}}</label>';
        div += '<div class="col-sm-3 has-success">';
        div += '<div class="input-group">';
        div += '<input class="confEnergyAttr form-control input-sm" data-l1key="consumption" />';
        div += '<span class="input-group-btn">';
        div += '<a class="btn btn-default btn-sm listCmdInfo btn-success" data-input="consumption"><i class="fa fa-list-alt"></i></a>';
        div += '</span>';
        div += '</div>';
        div += '</div>';
        div += '<div class="col-sm-1">';
        div += '<i class="fa fa-minus-circle pull-right cursor bt_removeConfEnergy"></i>';
        div += '</div>';
        div += '</div>';
    } else if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value() == 'gas') {
        div += '<div class="form-group">';
        div += '<label class="col-sm-1 control-label">{{Quantité}}</label>';
        div += '<div class="col-sm-3 has-success">';
        div += '<div class="input-group">';
        div += '<input class="confEnergyAttr input-sm" data-l1key="consumption" />';
        div += '<span class="input-group-btn">';
        div += '<a class="btn btn-default btn-sm listCmdInfo btn-success" data-input="consumption"><i class="fa fa-list-alt"></i></a>';
        div += '</span>';
        div += '</div>';
        div += '</div>';
        div += '<div class="col-sm-1">';
        div += '<i class="fa fa-minus-circle pull-right cursor bt_removeConfEnergy"></i>';
        div += '</div>';
        div += '</div>';
    }
    div += '</div>';
    $('#div_confEnergy').append(div);
    $('#div_confEnergy').find('.confEnergy:last').setValues(_pulseCounter, '.confEnergyAttr');
    setConsumptionCategory();
}