<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('pulseCounter');
sendVarToJS('eqType', $plugin->getId());
$plugName=$plugin->getName();
$eqLogics = eqLogic::byType($plugin->getId());
$eqId = init('id');
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend>{{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>Ajouter</span>
			</div>
		
  			<div class="cursor eqLogicAction logoDefault" id="bt_healthpulseCounter">
				<i class="fas fa-medkit" style=""></i>
				<br/>
				<span>{{Santé}}</span>
			</div>
  
  
  			<div class="cursor eqLogicAction logoDefault" data-action="removeAll" id="bt_removeAll">
				<i class="fas fa-minus-circle" style="color: #FA5858;"></i>
				<br/>
				<span>{{Supprimer tous}}</span>
			</div>
  			
  			<div class="cursor eqLogicAction logoDefault" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br/>
				<span>{{Configuration}}</span>
			</div>
		</div>
  
		
		<legend>{{Mes équipements de comptage}}</legend>
		
					<?php
			if (count($eqLogics) == 0) {
				echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>
                	{{Cliquez sur Ajouter pour commencer !}}</span></center>";
			} 
			else {
				//echo '<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />';
				echo '<div class="input-group" style="margin-bottom:5px;">';
				echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>';
				echo '<div class="input-group-btn">';
				echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
				echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
				echo '</div>';
				echo ' </div>';
				echo '<div class="eqLogicThumbnailContainer">';
				$sortEqLogs=[];
              	$Homes=[];
               	foreach ($eqLogics as $eqLogic) {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					$imgPath =  __DIR__  . '/../../core/img/'. $eqLogic->getConfiguration('type', '') . '.png';
					
					echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                  	if ( file_exists($imgPath) ) {
                      	echo '<img src="plugins/pulseCounter/core/img/'. $eqLogic->getConfiguration('type', '') . '.png"/>';
                   	} else {
						echo '<img src="plugins/pulseCounter/plugin_info/pulseCounter_icon.png" style="height : 100px"/>';
                      
					}
					echo '<br/>';
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
					
					
				}
			}
			
	?>	
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="pull-left">
                <a class="btn btn-sm btn-default roundedLeft" id="bt_eqConfigRaw"><i class="fas fa-info">  </i>  </a>
        	</span>
			<span class="input-group-btn">
				<a class="btn btn-sm btn-default eqLogicAction " data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
				<a class="btn btn-sm btn-success eqLogicAction" data-action="save" id="bt_save_eqlogic"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
				<a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
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
								<div id="div_Config">
      							<div class="form-group">
									<label class="col-sm-3 control-label">{{Nom du compteur}}</label>
									<div class="col-xs-11 col-sm-7">
										<input type="text" class="eqLogicAttr form-control" id="eqId" data-l1key="id" style="display : none;" />
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
							</div> <!-- div_Config -->
<!-- *************************************** -->
								<br/>
                                <legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>
								<div id="div_Params">
								             
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Type}}
                                    	<sup><i class="fas fa-question-circle" title="Attention une fois le type sauvegardé, il ne peut plus être modifié !"></i></sup>         
                                    </label>
									<div class="col-xs-11 col-sm-7">
										<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type">
											<option value="gas">{{Gaz}}</option>
											<option value="water">{{Eau}}</option>
											<option value="electricity">{{Electricité}}</option>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Type Comptage}}
                                         <sup><i class="fas fa-question-circle" title="Total: Compteur totalisateur<br>Instantané: Compteur impulsionnel '+1 à chaque commande' !!! "></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-7">
										<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="countType">
											<option value="total" selected="selected">{{Total}}</option>
											<option value="instant">{{Instantané}}</option>
										</select>
									</div>
								</div>
                                           
                                <div class="form-group">
									<label class="col-sm-4 control-label">{{Compteur impulsions}}
                                         <sup><i class="fas fa-question-circle" title="Commande de comptage d'origine"></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-7">
										<div class="input-group">
											<input class="eqLogicAttr form-control" disabled data-l1key="configuration" data-l2key="pulse" data-mono="1"/>
											<span class="input-group-btn">
												<a class="btn btn-default listCmdI listCmdInfoNumeric" ><i class="fas fa-list-alt"></i></a>
											</span>
										</div>
									</div>
								</div>
                                
                                              
                                <div class="form-group">
									<label class="col-sm-4 control-label">{{Type impulsion}}
                                         <sup><i class="fas fa-question-circle" title="Pas utilisé pour l'instant "></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-7">
										<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pulseType">
											<option value="volume" selected="selected">{{Volume(litre,m³,...)}}</option>
											<option value="conso">{{Consomation(Wh,kWh,...)}}</option>
                                        </select>
									</div>
								</div>
                                <div class="form-group">
									<label class="col-sm-4 control-label">{{Poid par impulsion}}
                                         <sup><i class="fas fa-question-circle" title="A combien correspond 1 impulsion"></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-3">
										<input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pulseWeight">
                                
                                    </div>
                                    <div class="col-xs-11 col-sm-1"></div> 
                                    <div class="col-xs-11 col-sm-3">
										<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pulseUnit">
											<option value="l" selected="selected">{{Litre}}</option>
											<option value="m³">{{m³}}</option>
                                           	<option value="Wh" selected="selected">{{Wh}}</option>
											<option value="kWh">{{kWh}}</option>
										</select>
									</div>                                                            
								</div>
                                
                                <div class="form-group hidden">
									<label class="col-sm-4 control-label">{{Index correcteur}}
                                         <sup><i class="fas fa-question-circle" title="Index réel du compteur(Gazpar, Linky...)"></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-3">
										<input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="indexVolCible">
									
                                    </div>
                                    <div class="col-xs-11 col-sm-1"></div> 
                                    <div class="col-xs-11 col-sm-3">
										
									</div> 
                                    <div class="col-xs-11 col-sm-1">
										<span class="label label-info label-sm eqLogicAttr" data-l1key="configuration" data-l2key="indexPulseAdd" title="Impulsions additionnelles"></span>
									</div>          
								</div>
                                <div class="form-group elgaz">
									<label class="col-sm-4 control-label">{{Coef de conversion}}
                                         <sup><i class="fas fa-question-circle" title="Coef de conversion Gaz ex: 11 pour (1m3 = 11kWh)"></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-3">
										<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="coefType">
											<option value="cmd">{{Commande}}</option>
											<option value="perso" selected="selected">{{Personalisé}}</option>
										</select>
									</div>
                                    <div class="col-xs-11 col-sm-4">
										<input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="coef_perso"  id="coef_perso" value="10.91">
                                        
                                          
                                              
                                              <!-- disabled style="display : none;" -->
                                              
                                        <div class="input-group" id="div_cmd_coef" style="display : none;">
											<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="coef_cmd" data-mono="1" />
											<span class="input-group-btn">
												<a class="btn btn-default listCmdI listCmdInfoNumeric" ><i class="fas fa-list-alt"></i></a>
											</span>
										</div>      
									</div>          
								</div>
                             </div>  <!-- Paramètres -->              
                                              
                               
                      	<br/>
                                
                                              
   <!-- *************************************** -->
								
							<legend><i class="fas fa-cogs"></i> {{Paramètres avancés}}</legend>
							<div id="div_advancedParams" style="overflow-x: hidden; display: none;">                                           
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Prix unitaire}}
                                         <sup><i class="fas fa-question-circle" title="Prix du kWh ou m³"></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-3">
										<input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cost_unit">
                                
                                    </div>
                                    <div class="col-xs-11 col-sm-1"></div> 
                                    <div class="col-xs-11 col-sm-3">
									</div>                                                            
								</div>
                                           
                                <div class="form-group">
									<label class="col-sm-4 control-label">{{Corriger Vol_index}}
										<sup><i class="fas fa-question-circle" title="Corriger les impulsions pour correspondre à l'index réel"></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-7">
										<a class="btn btn-warning btn-sm" id="bt_setIndex"><i class="fas fa-eraser"></i> {{Corriger}}</a>
                                        <div id="md_modalIndex" style="overflow-x: hidden; display: none;"></div>
									</div>
								</div>							
								
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Renseigner des valeurs}}
										<sup><i class="fas fa-question-circle" title="Mofifier les valeur de l'historique"></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-7">
										<a class="btn btn-default btn-sm" id="bt_HistlValue"><i class="icon techno-courbes2"></i> {{Renseigner}}</a>
									</div>
								</div>
                                
                                          
								<div class="form-group">
									<label class="col-sm-4 control-label">{{URL à appeler}}
										<sup><i class="fas fa-question-circle" title="URL à appeler en cas de besoin"></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-7">
                                      <a class="btn btn-default btn-sm" id="bt_accessUrls"><i class="fas fa-key"></i> {{Push-urls}}</a>
										
									  
                                      <div id="md_modalPimp" style="overflow-x: hidden; display: none;">
										<label>{{Accès interne: }}</label><br>
                                          <div class="alert alert-info">
                                            <?php 
                                                echo '<span id="internalNetworkAccess">'
                                                    . network::getNetworkAccess('internal') . '/plugins/pulseCounter/core/php/go.php?apikey=' 
                                                    . jeedom::getApiKey('pulseCounter') 
                                                    . '&eqid='
                                                    .'</span>';
                                            ?>
                                          </div>
                                          <label>{{Accès externe: }}</label><br>
                                          <div class="alert alert-info">
                                            <?php 
                                                echo '<span id="externalNetworkAccess">'
                                                    . network::getNetworkAccess('external') . '/plugins/pulseCounter/core/php/go.php?apikey=' 
                                                    . jeedom::getApiKey('pulseCounter') 
                                                    . '&eqid='
                                                    .'</span>';
                                              ?>
                                          </div>
                                          <label>{{Accès api_Jeedom: }}</label><br>
                                          <div class="alert alert-info">
                                            <?php 
                                                echo '<span id="jee_externalNetworkAccess">'
                                                    . network::getNetworkAccess('external') . '/core/api/jeeApi.php?plugin=pulseCounter'
                                                    . '&type=event&apikey=' . jeedom::getApiKey($plugin->getId()) . '&id=#cmd_id#&value=#value#'
                                                   .'</span>';
                                              ?>
                                          </div>       
                                                
									  </div>
                                        
									</div>
								</div> 
                                                
                                <div class="form-group">
									<label class="col-sm-4 control-label">{{Régénérer les commandes}}
										<sup><i class="fas fa-question-circle" title="N'utililiser qu'en cas de besoin absolue"></i></sup>
                                    </label>
									<div class="col-xs-11 col-sm-7">
										<a class="btn btn-danger btn-sm" id="bt_SyncCmds"><i class="fas fa-sync"></i> {{Régénérer}}</a>
									</div>
								</div>
							</div><!--  advancedParams  -->   
                                          
					</fieldset>
				</form>
			</div>
		</div>
			</div>

			<div role="tabpanel" class="tab-pane" id="commandtab">
				<legend>
					<center class="title_cmdtable">{{Tableau de commandes <?php echo ' - '.$plugName.': ';?>}}
						<span class="eqName"></span>
					</center>
				</legend>
                <legend><i class="fas fa-info-circle"></i>  {{Infos}}</legend>
							 <div class="table-responsive">
                          <table id="table_cmdi" class="table table-bordered table-condensed ">
							
							<thead>
								<tr>
									<th style="width: 50px;">Id</th>
									<th style="width: 250px;">{{Nom}}</th>
									<th style="width: 100px;">{{Type}}</th>
									<th style="width: 250px;">{{Options}}</th>
									<th style="width: 100px;">{{Action}}</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
						</div>
                          
						<legend><i class="fas fa-list-alt"></i>  {{Actions}}</legend>
						 <div class="table-responsive">
                          <table id="table_cmda" class="table table-bordered table-condensed">
							
							<thead>
								<tr>
									<th style="width: 50px;">Id</th>
									<th style="width: 250px;">{{Nom}}</th>
									<th style="width: 100px;">{{Type}}</th>
									<th style="width: 250px;">{{Options}}</th>
									<th style="width: 100px;">{{Action}}</th>
									 
								</tr>
							</thead>
							<tbody></tbody>
						</table> 
                        </div>
			</div>
		</div>

	</div>
</div>

<?php include_file('desktop', 'pulseCounter', 'js', 'pulseCounter');?>
<?php include_file('core', 'plugin.template', 'js');?>