<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
if (init('object_id') == '') {
	$object = jeeObject::byId($_SESSION['user']->getOptions('defaultDashboardObject'));
} else {
	$object = jeeObject::byId(init('object_id'));
}
if (!is_object($object)) {
	$object = jeeObject::rootObject();
}
$allObject = jeeObject::buildTree();
if (count($object->getEqLogic(true, false, 'pulseCounter')) == 0) {
	foreach ($allObject as $object_sel) {
		if (count($object_sel->getEqLogic(true, false, 'pulseCounter')) > 0) {
			$object = $object_sel;
			break;
		}
	}
}
if (is_object($object)) {
	$_GET['object_id'] = $object->getId();
}
sendVarToJs('object_id', init('object_id'));

$pulseCounters = $object->getEqLogic(true, false, 'pulseCounter');
$graphdata = array();
sendVarToJs('jeedomBackgroundImg', 'plugins/pulseCounter/core/img/panel.jpg');
$graphData['day'] = array('start' => date('Y-m-d', strtotime('now -3 month')), 'end' => date('Y-m-d', strtotime('now')));
?>
<div class="row row-overflow" id="div_pulseCounter">
	<div class="col-lg-2 reportModeHidden">
		<div class="bs-sidebar">
			<ul id="ul_object" class="nav nav-list bs-sidenav">
				<li class="nav-header">{{Liste objets}}</li>
				<li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
				<?php
				foreach ($allObject as $object_li) {
					if ($object_li->getIsVisible() != 1 || count($object_li->getEqLogic(true, false, 'pulseCounter', null, true)) == 0) {
						continue;
					}
					$margin = 5 * $object_li->parentNumber();
					if ($object_li->getId() == init('object_id')) {
						echo '<li class="cursor li_object active" ><a data-object_id="' . $object_li->getId() . '" href="index.php?v=d&p=panel&m=pulseCounter&object_id=' . $object_li->getId() . '" style="padding: 2px 0px;"><span style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</span></a></li>';
					} else {
						echo '<li class="cursor li_object" ><a data-object_id="' . $object_li->getId() . '" href="index.php?v=d&p=panel&m=pulseCounter&object_id=' . $object_li->getId() . '" style="padding: 2px 0px;"><span style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</span></a></li>';
					}
				}
				?>
			</ul>
		</div>
	</div>
	<?php
	if (init('report') != 1) {
		echo '<div class="col-lg-10">';
	} else {
		echo '<div class="col-lg-12">';
	}
	$panel = pulseCounter::generatePanel('dashboard', init('period', config::byKey('savePeriod', 'pulseCounter')));
	echo $panel['html'];
	?>
</div>
</div>
<?php sendVarToJs('data', $panel['data']);?>
<?php include_file('desktop', 'panel', 'js', 'pulseCounter');?>
