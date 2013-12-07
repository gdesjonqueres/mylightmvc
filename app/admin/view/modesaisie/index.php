<?php
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
'$("#modeTab").dataTable({
	"sPaginationType": "full_numbers",
	"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
});');
?>
			<div id="content_block_nav">
				<h1>Gestion des modes de saisie</h1>

				<?php include 'index/add.php';?>

				<fieldset>
					<legend>Liste des modes de saisie</legend>
					<table id="modeTab">
						<thead>
							<tr>
								<th>ID</th>
								<th>Code</th>
								<th>Libellé</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($listMode as $mode):?>
						<tr>
							<td><?=$mode->getId()?></td>
							<td><a href="<?=Request::createUri('admin:modesaisie:edit',
																array('id' => $mode->getId()))?>">
									<?=$this->c($mode->getCode())?></a>
							</td>
							<td><?=$this->c($mode->getLibelle())?></td>
							<td><?=$this->c($mode->getDescription())?></td>
						</tr>
						<?php endforeach;?>
						</tbody>
					</table>
				</fieldset>

				<div id="etape_valide">
					<span class="retour"><a href="<?=Request::createUri('admin::')?>"> &lt; retour menu</a></span>
				</div>
			</div>