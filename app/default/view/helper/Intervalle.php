<?php
/**
 * Helper pour gérer un intervalle
 *
 */
class Default_View_Helper_Intervalle extends View_Helper
{
	/**
	 *
	 * @param unknown $name nom de l'intervalle
	 * @param array $list liste de valeurs
	 * @param string $min valeur min sélectionnée
	 * @param string $max valeur max sélectionnée
	 * @return string
	 */
	public function displayIntervalle($name, array $list, $min = '', $max = '')
	{
		$options = array('takeBoundValues' => false,
						'range' => $list);
		if ($min || $max) {
			$options['values'] = array();
			if ($min !== '') {
				$options['values']['lower'] = $min;
			}
			if ($max !== '') {
				$options['values']['upper'] = $max;
			}
		}

		$str = '<div id="' . $name . '" class="slider"></div>';
		$js = '$("#' . $name . '").intervalle(' . toJson($options) . ');';
		if ($this->view->getLayout()) {
			$this->view->addPlugin('jquery-ui', array('jquery-ui-1.10.1.custom.min.css', 'jquery-ui-1.10.1.custom.min.js'));
			$this->view->addCss('helper/intervalle.css');
			$this->view->addJs('helper/dtno.intervalle.js');
			$this->view->addReadyEvent($js);
		}
		else {
			$str .= $this->view->displayScript($js);
		}

		return $str;
	}

}