<?php
$this->addPlugin('colorbox', array('colorbox.css', 'jquery.colorbox-min.js'));
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
'$("#cpgTab").dataTable({
	"sPaginationType": "full_numbers",
	"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
});');

$this->addReadyEvent(
'$(".extraire").click(function(event) {
	var id = $(this).attr("id");
	var arr = id.split("-");
	cpgid = arr[1];
	opid = arr[2];
	$.post("' . Request::createUri('admin:campagne:extraire') . '", {"cpgid": cpgid, "opid": opid}, function(data) {
		if (data.ok == true) {
			alert("Extraction en cours");
			$("#" + id).hide();
		}
		else {
			alert("Une erreur s\'est produite au lancement de l\'extraction");
		}
	}, "json");
})');

$this->addReadyEvent(
'$(".iframe").colorbox({
	iframe: true,
	speed: "200",
	width: "800px",
	height: "600px",
	fastIframe: false,
	onComplete: function(){
		$(this).resize();}
});');
?>
				<div id="content_block_nav">
					<h1>Gestion des campagnes</h1>

					<fieldset>
						<legend>
						<?php if ($flag == 'enattente'):?>
							Opérations en attente
						<?php else:?>
							Toutes les opérations
						<?php endif;?>
						</legend>

						<?php if (empty($listCpg)):?>
							<?php if ($flag == 'enattente'):?>
						<p>Aucune opération en attente</p>
							<?php else:?>
						<p>Aucune opération</p>
							<?php endif;?>
						<?php else:?>
						<table id="cpgTab" class="liste">
							<thead>
								<tr>
									<th>Date</th>
									<!-- <th>Id op</th> -->
									<th>Nom</th>
									<th>Propriétaire</th>
									<th>Statut</th>
									<th>Qté<br />cmdée</th>
									<th>Cible</th>
									<th>Société</th>
									<!-- <th>Contact</th>-->
									<th>&nbsp;</th>
								</tr>
							</thead>
							<?php foreach ($listCpg as $c):?>
							<tr>
								<td><?=$c['date']?></td>
								<!-- <td><?=$c['id_operation']?></td> -->
								<td><?=$this->c($c['libelle'])?></td>
								<td><?=$this->c($c['utilisateur'])?></td>
								<td><?=$this->c($c['statut'])?></td>
								<td style="white-space: nowrap;"><?=$this->formatNumber($c['quantite'])?></td>
								<td><?=$this->c($c['cible'])?></td>
								<td><?=$this->c($c['contact_societe'])?></td>
								<!-- <td>
									<?=$this->c($c['contact_nom'])?>
									<?=(!empty($c['contact_email']) ? '<br />' . $this->c($c['contact_email']) : '')?>
									<?=(!empty($c['contact_tel']) ? '<br />Tel: ' . $this->c($c['contact_tel']) : '')?>
								</td> -->
								<td style="white-space: nowrap; text-align: left;">
									<a class="iframe icone voir" href="<?=Request::createUri('comptage:campagne:recap', array('id' => $c['id_campagne'], 'lightbox' => 'oui'))?>" title="voir la campagne"></a>
									<?php if ($user->canEditOperation($c['statut_id'])):?>
									&nbsp;|&nbsp;<a class="icone edit" href="<?=Request::createUri('comptage:campagne:load', array('id' => $c['id_campagne']))?>" title="modifier la campagne"></a>
									<?php endif;?>
									<?php if ($user->isAllowedAcces('lance_extraction') && $c['statut_id'] == 'STA03'):?>
									&nbsp;|&nbsp;<a class="icone extraire" id="op-<?=$c['id_campagne']?>-<?=$c['id_operation']?>" href="#" title="lancer l'extraction"></a>
									<?php endif;?>
								</td>
							</tr>
							<?php endforeach;?>
							</tbody>
						</table>
						<?php endif;?>

					</fieldset>

					<div id="etape_valide">
						<span class="retour" ><a href="<?=Request::createUri('admin::')?>"> &lt; retour</a></span>
					</div>
				</div>