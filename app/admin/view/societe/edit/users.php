				<fieldset>
					<legend>Types d'utilisateurs</legend>
					<table id="typeTab">
						<thead>
							<tr>
								<th>Type d'utilisateur</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($listUserTypesGlobal as $id => $label):?>
							<tr>
								<td><?=$this->c($label)?></td>
								<?php if (isset($listUserTypesSociete[$id])){
									$aUrl = Request::createUri('admin:userType:edit',
															   array('id' => $listUserTypesSociete[$id]->getId()));
									$aLbl = 'Modifier';
								}
								else {
									$aUrl = Request::createUri('admin:userType:add',
															   array('idSoc'  => $societe->getId(),
															   		 'idType' => $id));
									$aLbl = 'Créer';
								}?>
								<td><a href="<?=$aUrl?>"><?=$aLbl?></a></td>
							</tr>
							<?php endforeach;?>

						</tbody>
					</table>
				</fieldset>
				<fieldset>
					<legend>Liste des utilisateurs</legend>
					<table id="userTab">
						<thead>
							<tr>
								<th>Identifiant</th>
								<th>Nom</th>
								<th>Type d'utilisateur</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($utilisateurs as $utilisateur):?>
							<tr>
								<td><?=$utilisateur->getId()?></td>
								<td><a href="<?=(Request::createUri('admin:user:edit',
															        array('id' => $utilisateur->getId())))?>">
									<?=$this->c($utilisateur->getPrenom())?> <?=$this->c($utilisateur->getNom())?></a></td>
								<td><a href="<?=(Request::createUri('admin:userType:edit',
															        array('id' => $utilisateur->getType()->getId())))?>">
									<?=$this->c($utilisateur->getType()->getLibelle())?></a></td>
							</tr>
							<?php endforeach;?>
						</tbody>
					</table>
				</fieldset>