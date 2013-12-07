<?php
$this->setLayout($appVars->DEFAULT_MODULE . '/view/layout/lightbox.php');
$this->addCss('comptage.css');
$this->addCss('modesaisie.css');
$this->addPlugin('dataTables', array('jquery.dataTables.css', 'jquery.dataTables.min.js'));

$this->addReadyEvent(
'$("#contactTab").dataTable({
	"sPaginationType": "full_numbers",
	"oLanguage": {"sUrl": "' . $appVars->URL_PLGN . 'dataTables/dataTables.fr.txt"}
});');
if ($this->_request->getMethod() == 'POST' && !isset($message)) {
	$this->addJs('parent.location.reload();');
}
?>
			<p style="text-align: left;">
				<a href="<?=$this->c(Request::createUri('comptage:operation:addcontact'))?>" class="add" title="ajouter un nouveau contact">
					<i class="icon-add"></i> Ajouter un contact</a>
			</p>

			<?php if (isset($message)):?>
			<div class="userMsg"><?=$this->c($message)?></div>
			<?php endif;?>

			<form method="post">

				<fieldset>
					<div class="content" style="width: 500px; min-height: 300px;">
						<table id="contactTab">
							<thead>
								<tr>
									<th>Id</th>
									<th>Société</th>
									<th>Prénom</th>
									<th>Nom</th>
									<th>Email</th>
									<th>Sélectionner</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($listeContacts as $contact):?>
							<tr>
								<td><?=$contact['id']?></td>
								<td><?=$contact['societe']?></td>
								<td><?=$contact['prenom']?></td>
								<td><?=$contact['nom']?></td>
								<td><?=$contact['email']?></td>
								<td><input type="radio" name="contact" id="contact" value="<?=$contact['id']?>" /></td>
							</tr>
							<?php endforeach;?>
							</tbody>
						</table>
					</div>
				</fieldset>

				<input type="submit" class="btn" value="valider" />
			</form>