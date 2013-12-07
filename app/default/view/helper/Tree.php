<?php
/**
 * Helper pour afficher un arbre
 *
 */
class Default_View_Helper_Tree extends View_Helper
{
	/**
	 *
	 * @param string $name nom de l'arbre
	 * @param array $nodes arbre, liste de noeuds
	 * @param array $selected liste de noeuds sélectionnés
	 * @return string
	 */
	public function displayTree($name, array $nodes, array $selected = array())
	{
		$nodesJson = str_replace(array('children', 'label'),
								 array('ChildNodes', 'text'),
								 toJson($nodes));
		$selectedJson = toJson($selected);

		//$this->view->addReadyEvent("new Dtno.helpers.tree('$name', '" . $nodesJson . "', '" . $selectedJson . "');");

		$str = '<div id="' . $name . '" align="left"></div>';
		$js = "new Dtno.helpers.tree('$name', '" . $nodesJson . "', '" . $selectedJson . "');";
		if ($this->view->getLayout()) {
			$this->view->addPlugin('tree', array('tree.css', 'jquery.tree.js'));
			$this->view->addJs('helper/dtno.tree.js');
			$this->view->addReadyEvent($js);
		}
		else {
			$str .= $this->view->displayScript($js);
		}

		return $str;
	}

}