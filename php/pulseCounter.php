<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('pulseCounter');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span >{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span >{{Configuration}}</span>
			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes équipements de comptage}}</legend>
		<!-- Champ de recherche -->
		<div class="input-group" style="margin:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>
			<div class="input-group-btn">
				<a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
			</div>
		</div>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span>' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-success btn-sm eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<div class="row">
					<div class="col-lg-7">
						<form class="form-horizontal">
							<fieldset>
								<legend><i class="fas fa-wrench"></i> {{Général}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
									<div class="col-xs-11 col-sm-7">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label" >{{Objet parent}}</label>
									<div class="col-xs-11 col-sm-7">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php
											$options = '';
											foreach ((jeeObject::buildTree(null, false)) as $object) {
												$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
											}
											echo $options;
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Options}}</label>
									<div class="col-xs-11 col-sm-7">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
									</div>
								</div>

								<br/>
								<legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Type}}</label>
									<div class="col-xs-11 col-sm-7">
										<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type">
											<option value="electricity">{{Electricité}}</option>
											<option value="water">{{Eau}}</option>
											<option value="gas">{{Gaz}}</option>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Instantanné}}</label>
									<div class="col-xs-11 col-sm-7">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="instant"/>
											<span class="input-group-btn">
												<a class="btn btn-default listCmdInfoNumeric"><i class="fas fa-list-alt"></i></a>
											</span>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Consommation}}</label>
									<div class="col-xs-11 col-sm-7">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="consumption"/>
											<span class="input-group-btn">
												<a class="btn btn-default listCmdInfoNumeric"><i class="fas fa-list-alt"></i></a>
											</span>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Coût}}</label>
									<div class="col-xs-11 col-sm-7">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cost"/>
											<span class="input-group-btn">
												<a class="btn btn-default listCmdInfoNumeric"><i class="fas fa-list-alt"></i></a>
											</span>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Température extérieure}}</label>
									<div class="col-xs-11 col-sm-7">
										<div class="input-group">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="temperature_outdoor"/>
											<span class="input-group-btn">
												<a class="btn btn-default listCmdInfoNumeric"><i class="fas fa-list-alt"></i></a>
											</span>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Ajouter au total}}</label>
									<div class="col-xs-11 col-sm-7">
										<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="addToTotal"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Ceci est mon énergie de chauffage}}</label>
									<div class="col-xs-11 col-sm-7">
										<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="calculDpe"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{Renseigner des valeurs}}</label>
									<div class="col-xs-11 col-sm-7">
										<a class="btn btn-default" id="bt_manualValue"><i class="fas fa-plus-circle"></i> {{Renseigner}}</a>
									</div>
								</div>
					</fieldset>
				</form>
			</div>
		</div>
			</div>

			<div role="tabpanel" class="tab-pane" id="commandtab">
				<div class="table-responsive">
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{Nom}}</th><th>{{Type}}</th><th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			</div>
		</div>

	</div>
</div>

<?php include_file('desktop', 'pulseCounter', 'js', 'pulseCounter');?>
<?php include_file('core', 'plugin.template', 'js');?>
