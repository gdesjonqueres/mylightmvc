<?php
/**
 * Helper pour afficher un groupe de boutons radio
 *
 */
class Default_View_Helper_Radio extends View_Helper
{
	/**
	 *
	 * @param string $name nom du groupe
	 * @param array $list liste clé => valeur
	 * @param string $selected valeur sélectionnée
	 * @param array $attributes attributs supplémentaire
	 * @param string $glue séparateur
	 * @return string
	 */
	public function displayRadio($name, array $list, $selected = '',
							array $attributes = array(), $glue = '&nbsp;'
	){
		$selected = (array) $selected;

		$tabElem = array();
		foreach ($list as $id => $label) {

			$attrs = $attributes;
			$attrs['type']  = 'radio';
			$attrs['name']  = $name;
			//$attrs['name']  = 'myradio_input_' . $name;
			$attrs['id']    = $name . "_$id";
			$attrs['value'] = $id;
			//$attrs['value'] = $label;
			$attrs['data-label'] = $label;
			if (in_array($id, $selected)) {
				$attrs['checked'] = 'checked';
			}

			$tabElem[] = $this->view->displayFormInput($attrs) .
						'<label for="' . $this->view->c($attrs['id']) . '">' . $this->view->c($label) . '</label>';
		}

		//$tabElem[] = $this->view->displayFormInput(array('type' => 'hidden', 'id' => $name));

		/*$js = '
		$("#myradio_div_' . $name . ' input[type=radio]").on("change", function(index) {
			var id = $(this).attr("id").split("_");
			if (id[1]) {
				$("#' . $name . '").attr("name", "' . $name . '[" + id[1] + "]");
				$("#' . $name . '").attr("value", $(this).val());
			}
			else {
				$("#' . $name . '").attr("name", "' . $name . '[]");
				$("#' . $name . '").attr("value", "");
			}
		});
		';
		$this->view->addReadyEvent($js);*/

		if (is_callable($glue)) {
			array_walk($tabElem, $glue);
			$ret = implode('&nbsp;', $tabElem);
		}
		else {
			$ret = implode($glue, $tabElem);
		}

		return $ret;

		//return '<div id="myradio_div_' . $name . '">' . $ret . '</div>';
	}

}