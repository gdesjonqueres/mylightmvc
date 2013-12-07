<?php
/**
 * Helper pour afficher des cases à cocher avec l'habillage "pretty checkbox"
 *
 */
class Default_View_Helper_PrettyCheckbox extends View_Helper
{
	/**
	 *
	 * @param unknown $name nom de base des cases à cocher
	 * @param array $list liste des valeurs
	 * @param array $checked liste des valeurs sélectionnées
	 * @param array $attributes attributs supplémentaires
	 * @param array $settings options pour pretty checkbox
	 * @return string
	 */
	public function displayPrettyCheckbox($name, array $list, array $checked = array(),
							array $attributes = array(), array $settings = array()
	){
		$defaults = array('display' => 'list',
							'checkboxWidth' => 14,
							'checkboxHeight' => 14);
		$settings = array_merge($defaults, $settings);
		//$this->view->addReadyEvent('$(".pc_' . $name . '").prettyCheckboxes(' . toJson($settings) . ');');
		$js = '$(".pc_' . $name . '").prettyCheckboxes(' . toJson($settings) . ');';

		/*$callback = function (&$input, $i) {
			$input = '<div style="float:' . ($i % 2 ? 'right' : 'left') . '">' . $input . '</div>';
			if ($i % 2) {
				$input .= '<div class="clear"></div>';
			}
		};*/
		$callback = '';

		if (isset($attributes['class'])) {
			$attributes['class'] = $attributes['class'] . ' pc_' . $name;
		}
		else {
			$attributes['class'] = 'pc_' . $name;
		}

		$str = $this->view->displayCheckbox($name, $list, $checked, $attributes, 1, $callback);
		if ($this->view->getLayout()) {
			$this->view->addPlugin('prettyCheckboxes', array('prettyCheckboxes.css', 'prettyCheckboxes.js'));
			$this->view->addCss('helper/prettycb.css');
			$this->view->addReadyEvent($js);
		}
		else {
			$str .= $this->view->displayScript($js);
		}

		return $str;
	}

}