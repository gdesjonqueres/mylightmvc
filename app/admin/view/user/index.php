<?php
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
'$("#userTab").dataTable({
	"sPaginationType": "full_numbers",
	"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
});');
?>
			<div id="content_block_nav">
				<h1>Gestion des utilisateurs</h1>
				<fieldset>
					<legend>Liste des utilisateurs</legend>
					<table id="userTab">
						<thead>
							<tr>
								<th>Identifiant</th>
								<th>Nom</th>
								<th>Type d'utilisateur</th>
								<th>Societe</th>
							</tr>
						</thead>
						<?php foreach ($listUtilisateur as $utilisateur):?>
						<tr>
							<td><?=$utilisateur->getId()?></td>
							<td><a href="<?=Request::createUri('admin:user:edit',
															   array('id' => $utilisateur->getId()))?>">
									<?=$this->c($utilisateur->getPrenom() . ' ' . $utilisateur->getNom())?></a>
							</td>
							<td><a href="<?=Request::createUri('admin:userType:edit',
															   array('id' => $utilisateur->getType()->getId()))?>">
									<?=$this->c($utilisateur->getType()->getLibelle())?></a></td>
							<td><?=$this->c($utilisateur->getSociete()->getRaisonSociale())?></td>
						</tr>
						<?php endforeach;?>
						</tbody>
					</table>
				</fieldset>

				<div id="etape_valide">
					<span class="retour"><a href="<?=Request::createUri('admin::')?>"> &lt; retour menu</a></span>
				</div>
			</div>