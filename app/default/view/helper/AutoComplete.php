<?php
/**
 * Helper pour gérer un auto complete
 *
 */
class Default_View_Helper_AutoComplete extends View_Helper
{
	/**
	 *
	 * @param string $name nom de l'input text
	 * @param string $uriLookup uri de la fonction de recherche
	 * @param array $argsLookup arguments à fournir à la fonction de recherche
	 * @param array $options options supplémentaires
	 * @return string
	 */
	public function displayAutoComplete($name, $uriLookup, $argsLookup = array(), $options = array())
	{
		$options = array_merge($options, array('searchUri' => $uriLookup, 'searchData' => $argsLookup));

		$str = '<input type="text" id="' . $name . '" placeholder="Tapez vos mots clés, c\'est trouvé..." autocomplete="off" />';
		$js = "new Dtno.helpers.autoComplete('$name', '" . toJson($options) . "')";
		if ($this->view->getLayout()) {
			$this->view->addCss('helper/autocomplete.css');
			$this->view->addJs('helper/dtno.autocomplete.js');
			$this->view->addReadyEvent($js);
		}
		else {
			$str .= $this->view->displayScript($js);
		}

		return $str;
	}

}