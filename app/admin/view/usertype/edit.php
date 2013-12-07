<?php
$this->addCss('admin.css');
?>
			<div class="clear"></div>
			<div id="content_block_nav">
				<h1>Fiche type utilisateur</h1>
				<h2><?=$this->c($userType->getLibelle())?> - <?=$this->c($userType->getSociete()->getRaisonSociale())?></h2>
				<?php if (isset($message)) {
					echo '<div class="userMsg">' . $this->c($message) . '</div>';
				}?>

				<?php include 'edit/droits.php';?>

				<div id="etape_valide">
					<span class="retour" ><a href="<?=Request::createUri('admin::')?>"> &lt; retour</a></span>
				</div>
			</div>