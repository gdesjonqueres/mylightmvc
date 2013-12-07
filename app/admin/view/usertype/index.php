<?php
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
'$("#userTab").dataTable({
	"sPaginationType": "full_numbers",
	"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
});');
?>
			<div id="content_block_nav">
				<h1>Gestion des types d'utilisateurs</h1>
				<h2>Société: <?=$societe->getRaisonSociale()?></h2>
				<fieldset>
					<legend>Liste des types d'utilisateurs</legend>
					<table id="userTab">
						<thead>
							<tr>
								<th>Type d'utilisateur</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($listUserTypesGlobal as $id => $label):?>
							<tr>
								<td><?=$this->c($label)?></td>
								<?php if (isset($listUserTypesSociete[$id])){
									$aUrl = Request::createUri('admin:userType:edit',
															   array('id' => $listUserTypesSociete[$id]->getId()));
									$aLbl = 'Modifier';
								}
								else {
									$aUrl = Request::createUri('admin:userType:add',
															   array('idSoc'  => $societe->getId(),
															   		 'idType' => $id));
									$aLbl = 'Créer';
								}?>
								<td><a href="<?=$aUrl?>"><?=$aLbl?></a></td>
							</tr>
							<?php endforeach;?>
						</tbody>
					</table>
				</fieldset>

				<div id="etape_valide">
					<span class="retour"><a href="<?=Request::createUri('admin::')?>"> &lt; retour menu</a></span>
				</div>
			</div>