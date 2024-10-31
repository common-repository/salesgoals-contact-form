<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

/**
 * Class SGCF_Component
 * Implements the base class for components.
 * Components provide extra features to the attached controller.
 */
class SGCF_Component {

	/**
	 * The controller instance
	 * @var null|SGCF_Controller
	 */
	protected $controller = null;

	/**
	 * Component instance settings
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Constructs the new component instance.
	 * @param SGCF_Controller $controller
	 * @param array $settings
	 */
	public function __construct(SGCF_Controller $controller, $settings = array()) {
		$this->settings = $settings;
		$this->controller = $controller;
	}

}