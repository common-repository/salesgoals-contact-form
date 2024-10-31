<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

/**
 * Class SGCF_Controller
 *
 * This class handles a certain controller's base logic, it provides base methods
 * for plugin's controllers.
 */
abstract class SGCF_Controller {

	/**
	 * Controller's name
	 * @var string
	 */
	protected $name = null;

	/**
	 * Default view helpers used by controller's actions
	 * @var array
	 */
	protected $helpers = array();

	/**
	 * Default controller components used by the current controller
	 * @var array
	 */
	protected $components = array();

	/**
	 * Default models used by controller's actions
	 * @var array
	 */
	protected $models = array();

	/**
	 * View class' name
	 * @var string
	 */
	protected $view_class = 'SGCF_View';

	/**
	 * View class instance
	 * @var SGCF_View
	 */
	protected $view = null;

	/**
	 * Current request's object.
	 * @var SGCF_Request
	 */
	protected $request = null;

	/**
	 * Default controller's action.
	 * This is the action executed when no action is specified.
	 * @var string
	 */
	protected $default_action = 'index';

	/**
	 * Controller user messages.
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Variables to send to the view.
	 * @var array
	 */
	protected $view_vars = array();

	/**
	 * This flag tells us when an early action execution is requested, this kind of action
	 * let's you execute certain tasks like redirects, before any output is set.
	 * @var bool
	 */
	protected $early_action = false;

	/**
	 * Controller's constructor, it accepts a request instance holding current request's information.
	 * @param SGCF_Request $request
	 */
	public function __construct( $request ) {
		$this->request = $request;
		$this->initialize();
	}

	/**
	 * Controller's initialization, this method is executed when the controller is created and it
	 * guesses the controller's name, if not set, and loads default view helpers and models.
	 */
	protected function initialize() {
		// set controller's name
		if ( empty($this->name) ) {
			$class = get_class( $this );
			preg_match( '/^SGCF_(.*?)_Controller$/', $class, $matches );
			$this->name = strtolower( $matches[1] );
		}

		// instantiate the view class
		SGCF_Loader::uses( $this->view_class, 'view_class' );
		$this->view = new $this->view_class();
		$this->view->request = $this->request;

		// load view helpers
		foreach ( (array) $this->helpers as $helper ) {
			$this->view->load_helper( $helper );
		}

		// load models
		foreach ( (array) $this->models as $model ) {
			$this->load_model( $model );
		}

		// load components
		foreach ( (array) $this->components as $key => $component ) {
			$settings = array();
			if ( is_array( $component ) ) {
				$component = $key;
				$settings = $component;
			}
			$this->load_component( $component, $settings );
		}

		$this->hook_action( 'admin_init', '_process_early_actions' );
	}

	/**
	 * Loads a model into the controller by specifying its name.
	 * That model instance will therefore be accessed through $this->{$name}
	 * @param string $name
	 */
	public function load_model( $name ) {
		$class = 'SGCF_' . $name . '_Model';
		SGCF_Loader::uses( $class, 'model' );
		$this->{$name} = new $class();
	}

	/**
	 * Loads a controller component into the current controller by specifying its name.
	 * That component instance will therefore be accessed through $this->{$name}
	 * @param string $name
	 */
	public function load_component( $name, $settings = array() ) {
		$class = 'SGCF_' . $name . '_Component';
		SGCF_Loader::uses( $class, 'component' );
		$this->{$name} = new $class( $this, $settings );
	}

	/**
	 * Sets a variable to be sent to the view.
	 * @param string|array $var
	 * @param null|mixed $value
	 */
	protected function set( $var, $value = null ) {
		if ( is_array( $var ) ) {
			$this->view_vars = array_merge(
				$this->view_vars, $var
			);
		} else {
			$this->view_vars[ $var ] = $value;
		}
	}

	/**
	 * Renders a view with the variables specified in $vars or already set before.
	 * By default the view file is located under a directory with the same name as the controller's.
	 * @param SGCF_View $view
	 * @param array $vars
	 * @param string|null $dir
	 * @param string $echoes whether to echo the output or not
	 * @return string a string containing the generated output
	 */
	public function render( $view, $vars = array(), $dir = null, $echoes = 'yes' ) {
		if ( empty( $dir ) ) {
			$dir = $this->name;
		}
		$this->processMessage();
		$vars = array_merge( $this->view_vars, $vars );
		$output = $this->view->render(  $view, $vars, $dir, $echoes );
		$this->view_vars = array();
		return $output;
	}

	/**
	 * Sets a user message to be rendered by the view on this request.
	 * @param $key
	 */
	protected function setMessage( $key ) {
		$class = $this->messages[ $key ]['class'];
		$this->view->messages[$class] = $this->messages[ $key ]['text'];
	}

	/**
	 * Checks if a user message display was requested, if so, the corresponding user
	 * message is set.
	 */
	protected function processMessage() {
		$msg = $this->request->get( 'message' );
		if ( !empty( $msg ) ) {
			$this->setMessage( $msg );
		}
	}

	/**
	 * Performs a page redirect.
	 * @param array $args
	 * @param bool $safe
	 */
	public function redirect ( $args = array(), $safe = true ) {
		$url = add_query_arg(array_merge( array( 'early' => false ), $args ));
		if ( $safe ) {
			wp_safe_redirect( $url );
		} else {
			wp_redirect( $url );
		}
		die;
	}

	/**
	 * Processes an early action, when requested. This method is executed by the
	 * admin_init hook.
	 */
	public function _process_early_actions () {
		if ( $this->request->get( 'early' ) ) {
			$this->dispatch_action();
			$this->early_action = true;
		}
	}

	/**
	 * Dispatches a requested action by executing a public method with the same name in
	 * the controller's instance, and that doesn't start with an underscore character.
	 */
	public function dispatch_action() {
		if ( $this->early_action )
			return;
		if ( empty( $this->request->get['action'] ) ) {
			// if no action is set, runs the default one
			$this->{$this->default_action}();
		} elseif ( substr( $this->request->get['action'], 0, 1 ) !== '_' ) {
			// checks if the action is a public method not started with an '_'
			$action = $this->request->get['action'];
			if ( method_exists( $this, $action ) ) {
				$reflection = new ReflectionMethod($this, $action);
				if ($reflection && $reflection->isPublic()) {
					$this->{$action}();
				}
			}
		}
	}

	/**
	 * An add_action() wrapper to add an action to a certain hook, where the
	 * $action parameter is a controller method.
	 * @param string $hook
	 * @param string $method
	 * @param int $priority
	 * @param int $accepted_args
	 */
	protected function hook_action( $hook, $method, $priority = 10, $accepted_args = 1 ) {
		add_action( $hook, array($this, $method), $priority, $accepted_args );
	}

}