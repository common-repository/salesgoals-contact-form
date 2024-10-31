<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

/**
 * Class SGCF_View
 *
 * This class handles view rendering related logic.
 */
class SGCF_View {

	/**
	 * Default extension for view files
	 * @var string
	 */
	public $extension = '.php';

	/**
	 * Current request instance.
	 * @var null
	 */
	public $request = null;

	/**
	 * User messages to be rendered.
	 * @var array
	 */
	public $messages = array();

	/**
	 * Contains a current stack of view block names
	 * @var array
	 */
	protected $active_blocks = array();

	/**
	 * An associative array with view blocks output
	 * @var array
	 */
	protected $blocks = array();

	/**
	 * loads a view helper named as $name.
	 * @param string $name
	 */
	public function load_helper( $name ) {
		$class = 'SGCF_' . $name . '_Helper';
		SGCF_Loader::uses( $class, 'helper' );
		$this->{$name} = new $class();
	}

	/**
	 * Starts capturing the output to a view block
	 * @param $name
	 */
	public function start( $name ) {
		$this->active_blocks[] = $name;
		ob_start();
	}

	/**
	 * Stops capturing the output to the current view block
	 */
	public function end() {
		$output = ob_get_clean();
		$name = array_pop( $this->active_blocks );
		$this->blocks[ $name ] = $output;
	}

	/**
	 * Gets the content for a view block with the given name
	 * @param string $name The view block name
	 * @return string
	 */
	public function fetch( $name ) {
		return $this->blocks[ $name ];
	}

	/**
	 * Renders a view element, this method lets you pass view variables to that element.
	 * By default elements are located under the 'elements' subdirectory.
	 * @param string $name
	 * @param array $vars
	 */
	public function element ( $name, $vars = array() ) {
		$this->render( $name, $vars, 'elements' );
	}

	/**
	 * Renders a view file.
	 * @param $__view
	 * @param $__vars
	 * @param $__dir
	 * @param $__enchoes
	 */
	public function render ( $__view, $__vars, $__dir, $__echoes = 'yes' ) {
		extract( $__vars, EXTR_SKIP );
		ob_start();
		include SGCF_PLUGIN_PATH . SGCF_Loader::paths( 'view' ) . $__dir . '/' . $__view . $this->extension;
		$output = ob_get_clean();
		if ( $__echoes == 'yes' ) {
			echo $output;
		}
		return $output;
	}

}