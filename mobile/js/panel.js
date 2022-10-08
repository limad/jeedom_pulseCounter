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

function initPulseCounterPanel(_object_id) {
  jeedom.object.all({
    onlyHasEqLogic : 'pulseCounter',
    searchOnchild : '0',
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (objects) {
      var li = ' <ul data-role="listview">';
      for (var i in objects) {
        if (objects[i].isVisible != 1) {
          continue;
        }
        var icon = '';
        if (isset(objects[i].display) && isset(objects[i].display.icon)) {
          icon = objects[i].display.icon;
        }
        li += '<li></span><a href="#" class="link" data-page="panel" data-plugin="pulseCounter" data-title="' + icon.replace(/\"/g, "\'") + ' ' + objects[i].name + '" data-option="' + objects[i].id + '"><span>' + icon + '</span> ' + objects[i].name + '</a></li>';
      }
      li += '</ul>';
      panel(li);
    }
  });
  displayPulseCounter(_object_id,'');
  
}

function displayPulseCounter(_object_id,_period) {
  var graphOption = {
    showNavigator : false,
    showLegend : false,
    showScrollbar : false,
    showTimeSelector : false,
    option : {displayAlert:false}
  }
  setBackgroundImage('plugins/pulseCounter/core/img/panel.jpg');
  $.showLoading();
  $.ajax({
    type: 'POST',
    url: 'plugins/pulseCounter/core/ajax/pulseCounter.ajax.php',
    data: {
      action: 'getPanel',
      period : _period
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
      $('#div_displayEquipementPulseCounter').empty();
      $('#div_displayEquipementPulseCounter').append(data.result.html).trigger('create');
      $('#div_displayEquipementPulseCounter .displayEnergyDetail').off('click').on('click',function(){
        $('.energyDetail[data-id='+$(this).attr('data-id')+']').toggle();
      });
      $('.bt_changePeriod').off('click').on('click',function(){
        displayPulseCounter(_object_id,$(this).attr('data-period'))
      });
      jeedom.history.chart = [];
      for(var i in data.result.data.graphData){
        if (isNaN(i)) {
          continue;
        }
        graphOption.el = 'div_chartDay'+i;
        graphOption.height = $('#'+graphOption.el).closest('.row').find('table').height();
        graphOption.dateStart = data.result.data.graphData.day.start;
        graphOption.dateEnd = data.result.data.graphData.day.end;
        if(isset(data.result.data.graphData[i].cmd_day_consumption)){
          graphOption.cmd_id = data.result.data.graphData[i].cmd_day_consumption;
          jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
        }
        if(isset(data.result.data.graphData[i].cmd_day_cost)){
          graphOption.cmd_id = data.result.data.graphData[i].cmd_day_cost;
          jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
        }
        if(isset(data.result.data.graphData[i].cmd_instant)){
          graphOption.cmd_id = data.result.data.graphData[i].cmd_instant;
          jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
        }
        if(isset(data.result.data.graphData[i].cmd_performance)){
          graphOption.el = 'div_chartPerformanceDay'+i;
          graphOption.cmd_id = data.result.data.graphData[i].cmd_performance.id;
          if(isset(data.result.data.graphData[i].cmd_performance.startDate)){
            graphOption.dateStart =data.result.data.graphData[i].cmd_performance.startDate;
          }
          if(isset(data.result.data.graphData[i].cmd_performance.endDate)){
            graphOption.dateEnd = data.result.data.graphData[i].cmd_performance.endDate;
          }
          jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
        }
      }
      new Highcharts.Chart({
        chart: {
          zoomType: 'x',
          renderTo: 'graph_division',
          alignTicks: false,
          spacingBottom: 5,
          spacingTop: 5,
          spacingRight: 5,
          spacingLeft: 5,
          type: 'pie',
          backgroundColor:'transparent'
        },
        title: {text: ''},
        credits: {text: '', href: '' },
        exporting: {enabled: $.mobile},
        tooltip: {
          pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
          valueDecimals: 2,
        },
        plotOptions: {
          pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: true,
              format: '<b>{point.name}</b>: {point.percentage:.1f} %',
              style: {color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'}
            },
            showInLegend: false
          }
        },
        series: [{name: 'pointer',data: data.result.data.division}]
      });
      $.hideLoading();
    }
  });
}