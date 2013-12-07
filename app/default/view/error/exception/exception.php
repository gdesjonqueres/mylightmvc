<?php if (!isset($noCss) || $noCss !== true) {
	$this->addCss('exception.css');
}?>
<?php if (isset($exception)):?>
	<p><em>D&eacute;tail de l'exception:</em></p>
	<div class="exception">
		<div class="header">
			<span class="type">(<?=get_class($exception)?>: <?=$exception->getCode()?>)</span>
			<?=$this->c($exception->getMessage())?>
		</div>
		<p>
			Dans le fichier <em><?=$exception->getFile()?></em>
			en ligne <em><?=$exception->getLine()?></em>
		</p>
		<?php if ($exception instanceof Db_Exception):?>
		<p>
			<h2>Requête:</h2>
			<div class="sql"><?=formatHtmlSql($exception->getQuery())?></div>
		</p>
		<p>
			<h2>Connexion:</h2>
			<div class="dump"><?=formatHtmlDump($exception->getConnection())?></div>
		</p>
		<?php endif;?>
		<p>
			<h2>Trace:</h2>
			<div class="dump"><?=formatHtmlDump($exception->getTrace())?></div>
		</p>
	</div>
<?php endif;?>