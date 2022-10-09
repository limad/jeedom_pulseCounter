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

  $('.displayEnergyDetail').off('click').on('click',function(){
    $('.energyDetail[data-id='+$(this).attr('data-id')+']').toggle();
  });
  
  $('.bt_changePeriod').on('click',function(){
    var url = document.URL
    var newAdditionalURL = '';
    var tempArray = url.split("?");
    var baseURL = tempArray[0];
    var aditionalURL = tempArray[1];
    var temp = '';
    if(aditionalURL)  {
      var tempArray = aditionalURL.split('&');
      for ( var i in tempArray ){
        if(tempArray[i].indexOf('period') == -1){
          newAdditionalURL += temp+tempArray[i];
          temp = "&";
        }
      }
    }
    jeedom.history.chart = [];
    var url = baseURL+'?'+newAdditionalURL+temp+'period='+$(this).attr('data-period')
    loadPage(url.replace('#', ''));
  });
  
  var graphOption = {
    showNavigator : false,
    showLegend : false,
    showScrollbar : false,
    showTimeSelector : false,
    option : {displayAlert:false}
  }
  function initGraph(){
    jeedom.history.chart = [];
    var colors = Highcharts.getOptions().colors
    for(var i in data.graphData){
      if (isNaN(i)) {
        continue;
      }
      nbSerie = 0;
      graphOption.el = 'div_chartDay'+i;
      graphOption.height = $('#'+graphOption.el).closest('.row').find('table').height();
      graphOption.dateStart = data.graphData.day.start;
      graphOption.dateEnd = data.graphData.day.end;
      graphOption.option = {graphColor:colors[nbSerie]}
      if(isset(data.graphData[i].cmd_day_consumption)){
        graphOption.cmd_id = data.graphData[i].cmd_day_consumption;
        jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
        nbSerie++
      }
      graphOption.option = {graphColor:colors[nbSerie]}
      if(isset(data.graphData[i].cmd_day_cost)){
        graphOption.cmd_id = data.graphData[i].cmd_day_cost;
        jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
        nbSerie++
      }
      graphOption.option = {graphColor:colors[nbSerie]}
      if(isset(data.graphData[i].cmd_instant)){
        graphOption.cmd_id = data.graphData[i].cmd_instant;
        jeedom.history.drawChart(JSON.parse(JSON.stringify(graphOption)));
        nbSerie++
      }
      if(isset(data.graphData[i].cmd_performance)){
        graphOption.el = 'div_chartPerformanceDay'+i;
        graphOption.cmd_id = data.graphData[i].cmd_performance.id;
        if(isset(data.graphData[i].cmd_performance.startDate)){
          graphOption.dateStart =data.graphData[i].cmd_performance.startDate;
        }
        if(isset(data.graphData[i].cmd_performance.endDate)){
          graphOption.dateEnd = data.graphData[i].cmd_performance.endDate;
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
        series: {
          animation: {
            duration : (getUrlVars('report') == 1) ? 0 : 1000
          }
        },
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
      series: [{name: 'pointer',data: data.division}]
    });
  }
  
  initGraph();