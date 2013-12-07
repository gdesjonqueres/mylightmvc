<?php
$this->addCss('admin.css');
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
'$("#socTab").dataTable({
	"sPaginationType": "full_numbers",
	"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
});');
?>
			<div id="content_block_nav">
				<h1>Gestion des sociétés</h1>

				<?php include 'index/add.php';?>

				<fieldset>
					<legend>Liste des sociétés</legend>
					<table id="socTab">
						<thead>
							<tr>
								<th>ID</th>
								<th>Raison sociale</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($listSociete as $societe):?>
						<tr>
							<td><?=$societe->getId()?></td>
							<td><a href="<?=Request::createUri('admin:societe:edit',
															   array('id' => $societe->getId()))?>">
									<?=$this->c($societe->getRaisonSociale())?></a>
							</td>
						</tr>
						<?php endforeach;?>
						</tbody>
					</table>
				</fieldset>

				<div id="etape_valide">
					<span class="retour"><a href="<?=Request::createUri('admin::')?>"> &lt; retour menu</a></span>
				</div>
			</div>