<?php
$this->addPlugin('colorbox', array('colorbox.css', 'jquery.colorbox-min.js'));
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
'$("#cpgTab").dataTable({
	"sPaginationType": "full_numbers",
	"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
});');

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
				<div class="block_left">
					<?php include 'comptage/view/index/menu/menu.php';?>
				</div>

				<div class="content_bg">
					<h1>Toutes mes campagnes</h1>

					<?php if(empty($listCpg)):?>
					<p>Vous n'avez aucune campagne</p>
					<?php else:?>
					<table id="cpgTab" class="liste">
						<thead>
							<tr>
								<th>Date</th>
								<th>Nom</th>
								<th>Statut</th>
								<th>Qté<br />cmdée</th>
								<th>Cible</th>
								<th>Société</th>
								<!-- <th>Contact</th> -->
								<th>Actions</th>
							</tr>
						</thead>
						<?php foreach ($listCpg as $c):?>
						<tr>
							<td><?=$c['date']?></td>
							<td><?=$this->c($c['libelle'])?></td>
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
							</td>
						</tr>
						<?php endforeach;?>
						</tbody>
					</table>
					<?php endif;?>
				</div>