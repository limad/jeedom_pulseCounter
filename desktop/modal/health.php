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
$plugin = plugin::byId('pulseCounter');
$eqLogics = eqLogic::byType($plugin->getId());
?>

<table class="table table-condensed tablesorter" id="table_healthpulseCounter">
	<thead>
		<tr>
			<th style="width: 40px;">{{}}</th>
			<th style="width: 60px;">{{ID}}</th>
			<th>{{Equipement}}</th>
			<th>{{Type}}</th>
			<th>{{Statut}}</th>
			<th>{{Visible}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($eqLogics as $eqLogic) {
			$isenable = $eqLogic->getIsEnable();
          	$opacity = ($isenable) ? '' : 'disableCard';
          	$eqType = $eqLogic->getConfiguration('type', '');
         	echo '<tr>';
         ///////////////
			$imgPath =  __DIR__  . '/../../core/img/'. $eqType . '.png';
			if (file_exists($imgPath)) {
				echo '<td><img class="eqLogicDisplayCard  ' . $opacity . '" height="25" src="plugins/pulseCounter/core/img/'. $eqType . '.png"/></td>';
			}else{
				echo '<td><img class="eqLogicDisplayCard  ' . $opacity . '" height="25" src="' . $plugin->getPathImgIcon() . '"/></td>';
			}
         ///////////////
			echo '<td><span class="label label-info" style="">' . $eqLogic->getId() . '</span></td>';
         ///////////////
			$name = explode('<br/>', $eqLogic->getHumanName(true, true));
           echo '<td>' .str_replace('<br/>', '', $name[0]).'<a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $name[1]. '</a></td>';
         ///////////////
			echo '<td><span class="label label-info" style="">' . $eqType . '</span></td>';
         ///////////////
			if (!$isenable) {
				$status = '<span class="label label-danger" style="">{{Désactivé}}</span>';
			}elseif ($eqLogic->getStatus('state') == 'nok') {
				$status = '<span class="label label-danger" style="">{{NOK}}</span>';
			}else $status = '<span class="label label-success" style="">{{OK}}</span>';
			echo '<td>' . $status . '</td>';
         ///////////////
			if (!$eqLogic->getIsVisible()) {
				 $status = '<span class="label label-warning" style="width: 32px;" ><i class="fas fa-times"></i></span>';
			}else $status = '<span class="label label-success" style="width: 32px;"><i class="fas fa-check"></i></span>';
			echo '<td>' . $status . '</td>';
         ///////////////
			echo '<td><span class="label label-info" style="">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
         ///////////////
			echo '<td><span class="label label-info" style="">' . $eqLogic->getConfiguration('createtime') . '</span></td>';
			echo '</tr>';
		}
		?>
	</tbody>
</table>