<?php
/* @var $this View */
$js = <<<'EOT'
	$("#debug header").on("click", function(event) {
		$(this).parent().children(".content").slideToggle();
		$(this).toggleClass("open closed");
	});
	$("#debug .toolbar").on("click", function(event) {
		$(this).parent().children(".dashboard").slideToggle();
		$(this).children(".title").toggleClass("open closed");
	});
EOT;
$this->addReadyEvent($js);

function formatHtmlQueryString($qs)
{
	$qs = str_replace(array('=', '&'),
						array('<span class="sep2">=</span>', '<span class="sep1">&amp;</span>'), $qs);
	return $qs;
}

?>
						<div id="debug">

							<div class="toolbar">
								<span class="title closed">Debug toolbar</span>
								<span class="elapsed">Temps de génération: <?=sprintf('%.3f"', FrontController::getElapsedSecs())?></span>
							</div>

							<div class="dashboard">

								<section id="mvc">
									<header class="open">MVC</header>
									<div class="content" style="display: block;">
										<article id="route">
											<header class="open">Route</header>
											<div class="content" style="display: block;">
												<span class="module"><?=$this->_request->getModule()?></span>&nbsp;:&nbsp;
												<span class="controller"><?=$this->_request->getController()?></span>&nbsp;:&nbsp;
												<span class="action"><?=$this->_request->getAction()?></span>
												<p class="url"><!-- <span class="host">http://<?=$_SERVER['HTTP_HOST']?></span> -->
													<span class="uri"><?=dirname($_SERVER['REQUEST_URI'])?></span>
													/?<span class="args"><?=formatHtmlQueryString($_SERVER['QUERY_STRING'])?></span></p>
											</div>
										</article>

										<article id="droits">
											<header class="closed">Droits d'accès</header>
											<div class="content">
												<ul>
													<li>Login obligatoire: <?=($debugInfos['isLoginRequired'] ? 'Oui' : 'Non')?></li>
													<li>Niveau d'accès: <?=$debugInfos['restrictedLevel']?></li>
												</ul>
											</div>
										</article>
									</div>
								</section>

								<section id="session">
									<header class="open">Session</header>
									<div class="content">
										<article>
											<header class="closed">User</header>
											<div class="content dump">
												<?php if ($myUser = Session::getUser()):?>
													<?=formatHtmlDump($myUser)?>
												<?php else:?>
													Pas de user
												<?php endif;?>
											</div>
										</article>

										<article>
											<header class="closed">Campagne</header>
											<div class="content dump">
												<?php if ($myCmpgn = Session::getCampagne()):?>
													<?=formatHtmlDump($myCmpgn)?>
												<?php else:?>
													Pas de campagne
												<?php endif;?>
											</div>
										</article>
									</div>
								</section>

								<div class="clear"></div>

								<section id="params">
									<header class="open">Paramètres</header>
									<div class="content">
										<article id="method">
											<header class="open">Méthode d'accès</header>
											<div class="content" style="display: block;">
												<?=$this->_request->getMethod()?>
											</div>
										</article>

										<article id="get">
											<header class="closed">$_GET</header>
											<div class="content dump">
											<?php if (!empty($_GET)):?>
												<?=formatHtmlDump($_GET)?>
											<?php else:?>
												Vide
											<?php endif;?>
											</div>
										</article>

										<article id="post">
											<header class="closed">$_POST</header>
											<div class="content dump">
											<?php if (!empty($_POST)):?>
												<?=formatHtmlDump($_POST)?>
											<?php else:?>
												Vide
											<?php endif;?>
											</div>
										</article>
									</div>
								</section>

								<section id="log">
									<header class="closed">Logs</header>
									<div class="content" style="display: none;">
									<?php
									$logs = Logger::getLogEntries();
									if (!empty($logs)) {
										end($logs);
										while ($entry = current($logs)) {
											if ($entry['infos']['type'] == 'sql') {
												$value = formatHtmlSql($entry['entry']);
												$type = 'sql';
											}
											elseif (is_array($entry['entry']) || is_object($entry['entry'])) {
												$value = formatHtmlDump($entry['entry']);
												$type = 'dump';
											}
											else {
												$value = $entry['entry'];
												$type = $entry['infos']['type'];
											}
											echo '<div class="entry">';
											echo '<span class="linenumber">[' . key($logs) . ']</span>';
											echo '<span class="infos">' . $entry['infos']['datetime'] . '</span>';
											echo '<span class="type">' . $entry['infos']['type'] . '</span>';
											echo '<div class="' . $type . '">' . $value  . '</div>';
											echo '</div>';
											prev($logs);
										}
									}
									?>
									</div>
								</section>

							</div><!-- dashboard -->

						</div><!-- debug -->