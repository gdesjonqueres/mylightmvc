<?php
/* @var $myCritere Comptage_Model_Critere */
/* @var $this View */

$this->setLayout($appVars->DEFAULT_MODULE . '/view/layout/lightbox.php');
$this->addCss('comptage.css');
$this->addJs('comptage/campagne.js');

if (isset($myCritere)) {
	$js = <<<'EOT'
	$(".delete").on("mouseover", function(event) {
		$(this).siblings(".title").addClass("todelete");
	});
	$(".delete").on("mouseout", function(event) {
		$(this).siblings(".title").removeClass("todelete");
	});
EOT;
	$this->addReadyEvent($js);
}
else {
	//$this->addReadyEvent('parent.location.reload();');
    $this->addReadyEvent('parent.$.fn.colorbox.close();');
}
?>
				<?php if (isset($myCritere)):?>
				<h1><?=$this->c($myCritere->getLibelle())?></h1>

				<form id="formEditValeurs" method="post" action="<?=Request::createUri('comptage:critere:editValeurs',
																			array('critere' => $myCritere->getId()))?>">
					<fieldset>
						<p class="description">
							<span>Pour supprimer une valeur, cliquez sur <i class="icon-delete-small icon-small"></i></span></span>
						</p>

						<div class="content" style="max-height: 500px; overflow-y: auto;">
							<ul>
							<?php foreach ($myValeurs as $key => $val):?>
								<li>
									<span class="title" title="<?=$this->c($val)?>"><?=$this->c($this->truncate($val, 50))?></span>
									<button type="button" class="delete btn-reset" onClick="Dtno.models.critere.removeValeur('<?=$myCritere->getId()?>', '<?=$key?>')" title="supprimer">
										<i class="icon-delete-small icon-small"></i></button>
								</li>
							<?php endforeach;?>
							</ul>
						</div>
					</fieldset>
				</form>
				<?php endif;?>
