<?php
/**
 * Super classe pour les helper de vue
 *
 * @package Fw\View
 */
abstract class View_Helper
{
	/**
	 * Référence vers la vue courante
	 * @var View
	 */
	protected $view;

	public function __construct(View $view)
	{
		$this->view = $view;
	}
}