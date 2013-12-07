<?php
/* @var $crit Comptage_Model_Critere */
/* @var $myCible Comptage_Model_Cible */

$js = <<<'EOT'
	$(".hasMultipleModes").on("click", function(event) {
		var myJqObj = $(this).parents("li").children("ul");
		myJqObj.slideToggle();
		$("ul > li > ul").not(myJqObj).slideUp();
	});
EOT;
$this->addReadyEvent($js);

function getIcon($item, $type)
{
	$app = Application::getInstance();

	switch ($type) {
		case 'critere':
			$pathToIcon = 'comptage/menu/criteres/' . $item . '.png';
			$pathToStd  = 'comptage/menu/criteres/critere.png';
			break;

		case 'criteretype':
			$pathToIcon = 'comptage/menu/criteretypes/' . $item . '.png';
			$pathToStd  = 'comptage/menu/criteretypes/criteretype.png';
			break;

		case 'modesaisie':
			$pathToIcon = 'comptage/menu/modessaisie/' . $item . '.png';
			$pathToStd  = 'comptage/menu/modessaisie/modesaisie.png';
			break;
	}
	if (fileExists($app->PATH_IMG . '/' . $pathToIcon)) {
		return $app->URL_IMG . $pathToIcon;
	}
	else {
		return $app->URL_IMG . $pathToStd;
	}
}

function isSelected($cible, $critere)
{
	return isset($cible[$critere->getId()]);
}

//Logger::log($listeCriteres);
?>
<menu id="menu-criteres" class="menu gradient">
	<h1>Critères</h1>

	<?php $lastType = '';
	foreach ($listeCriteres as $crit) {
		if ($user->isAllowedCritere($crit->getId()) && !$crit->isDesactive()) {

			$type = $crit->getType();
			$tabModeSaisie = $crit->getListModeSaisie();

			if ($lastType && $type['id'] != $lastType) {
				echo '</ul>';
			}

			if ($type['id'] != $lastType):?>
			<h2>
				<span class="icon"><img src="<?=getIcon($type['code'], 'criteretype')?>" alt="<?=$type['code']?>" /></span>
				<span class="title"><?=$this->c($type['libelle'])?></span>
			</h2>
			<ul class="top">
			<?php endif;?>

			<?php if (!empty($tabModeSaisie) && count($tabModeSaisie) == 1):
				$modeSaisie = array_shift($tabModeSaisie);?>

				<li<?=(isSelected($myCible, $crit) ? ' class="selected"' : '')?>>
					<span class="title">
						<a title="<?=$this->c($crit->getLibelle())?>" class="<?=($modeSaisie->getCode() == 'import' ? 'iframe' : 'lightbox')?>"
							href="<?=$this->c(Request::createUri('comptage:modesaisie:' . $modeSaisie->getCode(),
														array('critere' => $crit->getId())))?>" data-mode="<?=$modeSaisie->getCode()?>" data-critere="<?=$crit->getId()?>">
								<?=$this->c($crit->getLibelle())?></a>
					</span>
			<?php else:?>

				<li<?=(isSelected($myCible, $crit) ? ' class="selected"' : '')?>>
					<span class="title"><a class="hasMultipleModes" href="#" title="<?=$this->c($crit->getLibelle())?>">
						<?=$crit->getLibelle()?></a></span>

					<ul class="modes-saisie">
					<?php foreach ($tabModeSaisie as $modeSaisie):?>
						<li>
							<span class="icon"><img src="<?=getIcon($modeSaisie->getCode(), 'modesaisie')?>" alt="<?=$modeSaisie->getCode()?>" /></span>
							<span class="title">
								<a title="<?=$this->c($modeSaisie->getDescription())?>" class="<?=($modeSaisie->getCode() == 'import' ? 'iframe' : 'lightbox')?>"
									href="<?=$this->c(Request::createUri('comptage:modesaisie:' . $modeSaisie->getCode(),
																array('critere' => $crit->getId())))?>" data-mode="<?=$modeSaisie->getCode()?>" data-critere="<?=$crit->getId()?>">
									<?=$this->c($modeSaisie->getLibelle())?></a>
							</span>
						</li>
					<?php endforeach;?>
					</ul>
			<?php endif;?>

			<?php
			echo '</li>';
			$lastType = $type['id'];
		}
	}
	echo '</ul>';?>

</menu>