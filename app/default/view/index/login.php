			<div class="clear"></div>
			<div id="content_block_nav">
				<h1>Connexion au requeteur</h1>
				<?php if (isset($message)) {
					echo '<div class="userMsg">' . $this->c($message) . '</div>';
				}?>
				<fieldset>
					<legend>Veuillez renseigner vos identifiants de connexion</legend>
					<form method="post" action="<?=Request::createUri('::login')?>">

						<table cellspacing="20px" align="center">
							<tr>
								<td><label>Adresse email</label></td>
								<td><input type="text" name="login" tabindex="1" autofocus required /></td>
								<td rowspan="2" align="center"><input type="submit" class="btn" value="Connexion"  tabindex="3" /></td>
							</tr>
							<tr>
								<td><label>Mot de passe</label></td>
								<td><input type="password" name="mdp" tabindex="2" required /></td>
							</tr>
						</table>
					</form>
				</fieldset>
				<div class="connexion_footer">
					<p>
						La connexion à votre espace personnel est entièrement sécurisée. <br />
						Vos données sont protégées par nos logiciels. <br />
						Pour toutes demandes d'infomations: <br />
						<a style="text-decoration:underline;" href="mailto:<?=$appVars->MAILCONTACT?>"><?=$appVars->MAILCONTACT?></a>
					</p>
				</div>
			</div>