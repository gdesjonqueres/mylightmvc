<?php
$this->setLayout($appVars->DEFAULT_MODULE . '/view/layout/lightbox.php');
$this->addCss('comptage.css');
$this->addCss('modesaisie.css');

if ($this->_request->getMethod() == 'POST' && !isset($message)) {
	$this->addJs('parent.location.reload();');
}
?>
			<?php if (isset($message)):?>
			<div class="userMsg"><?=$this->c($message)?></div>
			<?php endif;?>

			<form id="formContact" method="post" action="<?=Request::createUri('comptage:operation:addcontact')?>">

				<fieldset>
					<div class="content" style="width: 500px; min-height: 300px;">
						<div class="label">Société</div>
						<div class="value"><input type="text" id="societe" name="societe" /></div>
						<div class="label">Prénom</div>
						<div class="value"><input type="text" id="prenom" name="prenom" /></div>
						<div class="label">Nom</div>
						<div class="value"><input type="text" id="nom" name="nom" /></div>
						<div class="label">Email</div>
						<div class="value"><input type="text" id="email" name="email" /></div>
						<div class="label">Téléphone</div>
						<div class="value"><input type="text" id="tel" name="tel" /></div>
						<div class="label">Adresse</div>
						<div class="value"><textarea id="adresse" name="adresse" rows="5" cols="30"></textarea></div>
					</div>
				</fieldset>

					<input type="submit" class="btn" value="valider" />
			</form>