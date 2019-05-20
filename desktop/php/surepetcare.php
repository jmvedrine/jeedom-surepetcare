<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('surepetcare');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add"  >
				<i class="fas fa-plus-circle"></i>
				<br>
				<span >{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" id="bt_syncEqLogic" >
				<i class="fas fa-sync-alt"></i>
				<br>
				<span><center>{{Synchronisation}}</center></span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf" >
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>

		<legend><i class="fas fa-table"></i> {{Mes équipements Sure PetCare}}</legend>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
                if($eqLogic->getConfiguration('type','') != 'device') continue;
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				if ($eqLogic->getConfiguration('iconProduct', '') != '') {
					echo '<img src="' . $eqLogic->getImage() . '"/>';
				} else {
					echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				}
				echo '<br>';
				echo '<span>' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
        <legend><i class="fas fa-table"></i> {{Mes animaux}}</legend>
        <div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
                if($eqLogic->getConfiguration('type','') != 'pet') continue;
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $eqLogic->getImage() . '"/>';
				
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
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<div class="row">
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Nom}}</label>
									<div class="col-sm-4">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'objet}}"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Identifiant}}</label>
									<div class="col-sm-4">
										<input type="text" class="eqLogicAttr form-control" data-l1key="logicalId" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" >{{Objet parent}}</label>
									<div class="col-sm-4">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php
											foreach (jeeObject::all() as $object) {
												echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Catégorie}}</label>
									<div class="col-sm-6">
										<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
											echo '<label class="checkbox-inline">';
											echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
											echo '</label>';
										}
										?>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label"></label>
									<div class="col-sm-6">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
									</div>
								</div>
                                <div class="form-group" id="curfew_lock_time" >
								    <label class="col-sm-2 control-label">{{Heure de début du couvre-feu (HHMM)}}
								    </label>
								    <div class="col-sm-5">
									    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="lock_time" placeholder="{{Heure de début (HHMM)}}"/>
								    </div>
							    </div>
                                <div class="form-group" id="curfew_unlock_time" >
								    <label class="col-sm-2 control-label">{{Heure de fin du couvre-feu (HHMM)}}
								    </label>
								    <div class="col-sm-5">
									    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="unlock_time" placeholder="{{Heure de fin (HHMM)}}"/>
								    </div>
							    </div>
							</fieldset>
						</form>
					</div>
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>
								<table id="table_infoseqlogic" class="table table-condensed" style="border-radius: 10px;">
                                    <thead>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
								<div class="form-group">
									<label class="col-sm-2 control-label"></label>
									<div class="col-sm-8">
										<img src="core/img/no_image.gif" data-original=".jpg" id="img_device" class="img-responsive" style="max-height : 250px;" onerror="this.src='plugins/surepetcare/plugin_info/surepetcare_icon.png'"/>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Commandes}}</a><br/><br/>
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width: 300px;">{{Nom}}</th>
							<th style="width: 130px;">{{Type}}</th>
							<th>{{Logical ID}}</th>
							<th>{{Paramètres}}</th>
							<th style="width: 300px;">{{Options}}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
</div>

</div>
</div>

<?php include_file('core', 'surepetcare', 'class.js', 'surepetcare');?>
<?php include_file('desktop', 'surepetcare', 'js', 'surepetcare');?>
<?php include_file('core', 'plugin.template', 'js');?>
