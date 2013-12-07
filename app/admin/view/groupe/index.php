<?php
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
'$("#entityTab").dataTable({
	"sPaginationType": "full_numbers",
	"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
});');
?>
			<div id="content_block_nav">
				<h1>Gestion des groupes</h1>

				<?php include 'index/add.php';?>

				<fieldset>
					<legend>Liste des groupes</legend>
					<table id="entityTab">
						<thead>
							<tr>
								<th>ID</th>
								<th>Libellé</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($listGroupe as $entity):?>
						<tr>
							<td><?=$entity->getId()?></td>
							<td><a href="<?=Request::createUri('admin:groupe:edit',
															   array('id' => $entity->getId()))?>">
									<?=$this->c($entity->getLibelle())?></a>
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