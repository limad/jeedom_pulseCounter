<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
if (init('object_id') == '') {
	$_GET['object_id'] = $_SESSION['user']->getOptions('defaultDashboardObject');
}
$object = jeeObject::byId(init('object_id'));
if (!is_object($object)) {
	$object = jeeObject::rootObject();
}
if (!is_object($object)) {
	throw new Exception('{{Aucun objet racine trouvé}}');
}

sendVarToJs('object_id', $object->getId());
sendVarToJs('type', init('type', 'gas'));
sendVarToJs('groupBy', init('groupBy', 'day'));
if (init('groupBy', 'day') == 'day') {
	$date = array(
		'start' => init('startDate', date('Y-m-d', strtotime('-31 days ' . date('Y-m-d')))),
		'end' => init('endDate', date('Y-m-d')),
	);
}
if (init('groupBy', 'day') == 'month') {
	$date = array(
		'start' => init('startDate', date('Y-m-d', strtotime('-1 year ' . date('Y-m-d')))),
		'end' => init('endDate', date('Y-m-d', strtotime('+1 days' . date('Y-m-d')))),
	);
}
?>
<div style="position : fixed;height:100%;width:15px;top:50px;left:0px;z-index:998;background-color:#f6f6f6;" id="bt_displayObjectList"><i class="fa fa-arrow-circle-o-right" style="color : #b6b6b6;"></i></div>


<div class="row row-overflow" id="div_pulseCounter">
  <div class="col-xs-2" id="sd_objectList" style="z-index:999">
    <div class="bs-sidebar">
      <ul id="ul_object" class="nav nav-list bs-sidenav">
        <li class="nav-header">{{Liste objets}}</li>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
$allObject = jeeObject::buildTree();
foreach ($allObject as $object_li) {
	if ($object_li->getIsVisible() == 1) {
		$margin = 15 * $object_li->parentNumber();
		if ($object_li->getId() == $object->getId()) {
			echo '<li class="cursor li_object active" ><a href="index.php?v=d&m=pulseCounter&p=panel2&object_id=' . $object_li->getId() . '&type=' . init('type', 'electricity') . '" style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</a></li>';
		} else {
			echo '<li class="cursor li_object" ><a href="index.php?v=d&m=pulseCounter&p=panel2&object_id=' . $object_li->getId() . '&type=' . init('type', 'electricity') . '" style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</a></li>';
		}
	}
}
?>
   </ul>
 </div>
</div>

<div class="col-xs-10" id="div_graphiqueDisplay">
 <legend style="height: 40px;">
  <i class="fa fa-picture-o"></i>  <span class="objectName"></span> {{du}}
  <input class="form-control input-sm in_datepicker" id='in_startDate' style="display : inline-block; width: 150px;" value='<?php echo $date['start']?>'/> {{au}}
  <input class="form-control input-sm in_datepicker" id='in_endDate' style="display : inline-block; width: 150px;" value='<?php echo $date['end']?>'/>
  <a class="btn btn-success btn-sm tooltips" id='bt_validChangeDate' title="{{Attention une trop grande plage de dates peut mettre très longtemps à être calculée ou même ne pas s'afficher}}">{{Ok}}</a>
  <center style="display:inline-block;">
    <?php if (init('type', 'electricity') == 'electricity') {?>
    <span class='label label-success' style="font-size: 0.9em;"><span class="pulseCounterAttr" data-l1key="total" data-l2key="power"></span> W</span>
    <span class='label label-primary' style="font-size: 0.9em;"><span class="pulseCounterAttr" data-l1key="total" data-l2key="consumption"></span> <span class="pulseCounterAttr" data-l1key="consumptionUnite"></span></span>
    <span class='label label-default' style="font-size: 0.9em;"><span class="pulseCounterAttr" data-l1key="total" data-l2key="cost"> </span> <span class="pulseCounterAttr" data-l1key="currency"></span>
    <?php } else {
	?>
     <span class='label label-primary' style="font-size: 0.9em;"><span class="pulseCounterAttr" data-l1key="total" data-l2key="consumption"></span> <span class="pulseCounterAttr" data-l1key="consumptionUnite"></span></span>
     <span class='label label-default' style="font-size: 0.9em;"><span class="pulseCounterAttr" data-l1key="total" data-l2key="cost"> </span> <span class="pulseCounterAttr" data-l1key="currency"></span>
     <?php }
?>

   </center>


   <span class="pull-right">
    <?php
if (init('groupBy', 'day') == 'day') {
	echo '<a class="btn btn-primary btn-sm" href="index.php?v=d&m=pulseCounter&p=panel2&groupBy=day&type=' . init('type', 'electricity') . '&object_id=' . $object->getId() . '">{{Jour}}</a> ';
} else {
	echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=pulseCounter&p=panel2&groupBy=day&type=' . init('type', 'electricity') . '&object_id=' . $object->getId() . '">{{Jour}}</a> ';
}
if (init('groupBy', 'day') == 'month') {
	echo '<a class="btn btn-primary btn-sm" href="index.php?v=d&m=pulseCounter&p=panel2&groupBy=month&type=' . init('type', 'electricity') . '&object_id=' . $object->getId() . '">{{Mois}}</a> ';
} else {
	echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=pulseCounter&p=panel2&groupBy=month&type=' . init('type', 'electricity') . '&object_id=' . $object->getId() . '">{{Mois}}</a> ';
}
?>
   <span class="pull-right">
    <?php
////////////////////
	if (init('type', 'electricity') == 'electricity') {
		echo '<a class="btn btn-success btn-sm" href="index.php?v=d&m=pulseCounter&p=panel2&groupBy=' . init('groupBy', 'day') . 'type=electricity&object_id=' . $object->getId() . '"><i class="fa fa-bolt"></i> Electricité</a> ';
	} else {
		echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=pulseCounter&p=panel2&groupBy=' . init('groupBy', 'day') . '&type=electricity&object_id=' . $object->getId() . '"><i class="fa fa-bolt"></i> Electricité</a> ';
	}

////////////////////
	if (init('type', 'electricity') == 'water') {
		echo '<a class="btn btn-success btn-sm" href="index.php?v=d&m=pulseCounter&groupBy=' . init('groupBy', 'day') . '&p=panel2&type=water&object_id=' . $object->getId() . '"><i class="fa fa-tint"></i> Eau</a> ';
	} else {
		echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=pulseCounter&groupBy=' . init('groupBy', 'day') . '&p=panel2&type=water&object_id=' . $object->getId() . '"><i class="fa fa-tint"></i> Eau</a> ';
	}
////////////////////
	
////////////////////
	if (init('type', 'electricity') == 'gas') {
		echo '<a class="btn btn-success btn-sm" href="index.php?v=d&m=pulseCounter&groupBy=' . init('groupBy', 'day') . '&p=panel2&type=gas&object_id=' . $object->getId() . '"><i class="fa fa-fire"></i> Gaz</a> ';
	} else {
		echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=pulseCounter&groupBy=' . init('groupBy', 'day') . '&p=panel2&type=gas&object_id=' . $object->getId() . '"><i class="fa fa-fire"></i> Gaz</a> ';
	}
////////////////////
	
?>
</span>
</legend>

<?php if (init('type', 'electricity') == 'electricity') {
	?>

<div class="row">
  <div class="col-lg-6">
   <legend><i class="icon techno-courbes3"></i>  {{Puissance (15min/1h)}} <input id="cb_powerLissage" data-href="<?php echo 'index.php?v=d&m=pulseCounter&groupBy=' . init('groupBy', 'day') . '&p=panel2&type=' . init('type', 'electricity') . '&object_id=' . init('object_id', 'day')?>" type="checkbox" data-off-color="warning" <?php if (init('powerDisplay', '1h') == '1h') {echo 'checked';}
	?> class="pull-right"/></legend>
   <div id='div_graphPower'></div>
 </div>
 <div class="col-lg-6">
   <legend><i class="fa fa-eur"></i>  {{Coût}}</legend>
   <div id='div_graphCost'></div>
 </div>
</div>
<div class="row">
  <div class="col-lg-6">
    <legend><i class="icon techno-courbes4"></i>  {{Consommation par objet}}</legend>
    <div id='div_graphDetailConsumptionByObject'></div>
  </div>
  <div class="col-lg-6">
    <legend><i class="icon techno-courbes2"></i>  {{Consommation par catégorie}}</legend>
    <div id='div_graphDetailConsumptionByCategorie'></div>
  </div>
</div>
<?php } else {?>
<legend><i class="fa fa-eur"></i>  {{Coût}}</legend>
<div id='div_graphCost'></div>
<legend><i class="icon techno-courbes2"></i>  {{Consommation}}</legend>
<div id='div_graphDetailConsumption'></div>
<?php }
?>
</div>
</div>

<?php include_file('desktop', 'panel2', 'js', 'pulseCounter');?>