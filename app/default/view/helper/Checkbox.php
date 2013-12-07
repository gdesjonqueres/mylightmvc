<?php
/**
 * Helper pour gérer des cases à cocher
 *
 */
class Default_View_Helper_Checkbox extends View_Helper
{
	/**
	 *
	 * @param string $name nom de base de l'input
	 * @param array $list liste des valeurs
	 * @param array $checked liste des valeurs sélectionnées
	 * @param array $attributes attributs supplémentaires
	 * @param mixed $values inutilisé
	 * @param string $glue séparateur
	 * @return string
	 */
	public function displayCheckbox($name, array $list, array $checked = array(),
							array $attributes = array(), $values = 1, $glue = '&nbsp;'
	){
		$tabElem = array();
		foreach ($list as $id => $label) {

			$attrs = $attributes;
			$attrs['type']  = 'checkbox';
			//$attrs['name']  = $name . "[$id]";
			//$attrs['name']  = $name . "[]";
			$attrs['name']  = $name . "[$id]";
			$attrs['id']    = $name . "_$id";
			//$attrs['value'] = $values;
			//$attrs['value'] = $id;
			$attrs['value'] = $label;
			if (in_array($id, $checked)) {
				$attrs['checked'] = 'checked';
			}

			$tabElem[] = $this->view->displayFormInput($attrs) .
						'<label for="' . $this->view->c($attrs['id']) . '">' . $this->view->c($label) . '</label>';
		}

		if (is_callable($glue)) {
			array_walk($tabElem, $glue);
			return implode('&nbsp;', $tabElem);
		}
		return implode($glue, $tabElem);
	}

}