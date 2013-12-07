<?php
/**
 * Helper pour afficher un groupe de boutons radio avec habillage pretty checkbox
 *
 */
class Default_View_Helper_PrettyRadio extends View_Helper
{
	/**
	 *
	 * @param string $name nom du groupe de boutons
	 * @param array $list liste clé => valeur
	 * @param string $selected valeur sélectionnée
	 * @param array $attributes attributs supplémentaires
	 * @param array $settings options pour pretty checkbox
	 * @return string
	 */
	public function displayPrettyRadio($name, array $list, $selected = '',
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

		$str = $this->view->displayRadio($name, $list, $selected, $attributes, $callback);
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