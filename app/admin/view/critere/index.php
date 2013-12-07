<?php
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
'$("#critTab").dataTable({
	"sPaginationType": "full_numbers",
	"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
});');
?>
			<div id="content_block_nav">
				<h1>Gestion des critères</h1>

				<?php include 'index/add.php';?>

				<fieldset>
					<legend>Liste des critères</legend>
					<table id="critTab">
						<thead>
							<tr>
								<th>ID</th>
								<th>Code</th>
								<th>Libellé</th>
								<th>Type de cible</th>
								<th>Type de critère</th>
								<th>Type de valeur</th>
								<th>Désactivé</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($listCritere as $critere):?>
						<tr>
							<td><?=$critere->getId()?></td>
							<td><a href="<?=Request::createUri('admin:critere:edit',
																array('id' => $critere->getId()))?>">
									<?=$this->c($critere->getCode())?></a>
							</td>
							<td><?=$this->c($critere->getLibelle())?></td>
							<td><?php $cibletype = $critere->getListCibleType();
								echo $this->c(implode(', ', array_values($cibletype)));?></td>
							<td><?php $type = $critere->getType();
								echo $this->c($type['libelle']);?></td>
							<td><?=$this->c($critere->getTypeValeur())?></td>
							<td><?=($critere->isDesactive() ? 'Oui' : 'Non')?></td>
						</tr>
						<?php endforeach;?>
						</tbody>
					</table>
				</fieldset>

				<div id="etape_valide">
					<span class="retour"><a href="<?=Request::createUri('admin::')?>"> &lt; retour menu</a></span>
				</div>
			</div>