<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

/**
 * Class SGCF_Plugin
 *
 * This class provides plugin's general methods which are not associated with specific
 * system components.
 */
class SGCF_Plugin {

	/**
	 * The controller in use.
	 * @var SGCF_Controller
	 */
	protected static $controller = null;

	/**
	 * The request object
	 * @var SGCF_Request
	 */
	protected static $request = null;

	/**
	 * Executes the plugin bootstrap process.
	 * @param SGCF_Request|null $request
	 */
	public static function bootstrap($request = null) {
		self::$request = $request;
		self::check_requirements();
		self::register_activation_hook();
		self::register_init_hook();
		self::register_uninstall_hook();
	}

	/**
	 * Loads the frontend controller
	 */
	public static function load_frontend() {
		self::$controller = self::get_controller( 'frontend' );
	}

	/**
	 * Loads the administration controller
	 */
	public static function load_admin() {
		self::$controller = self::get_controller( 'admin' );
	}

	/**
	 * Loads and returns an instance for the controller with named by $name.
	 * @param string $name
	 * @return SGCF_Controller
	 */
	public static function get_controller( $name ) {
		$class = 'SGCF_' . ucwords( $name ) . '_Controller';
		SGCF_Loader::load( $class, 'controller' );
		return new $class( self::$request );
	}

	/**
	 * Adds the plugin's init action to the the init hook
	 */
	protected static function register_init_hook() {
		add_action( 'init', array( __CLASS__, 'init_action') );
	}

	/**
	 * Executes the plugin's init action.
	 */
	public static function init_action() {
		self::load_text_domain();
		self::register_custom_post_types();
	}

	/**
	 * Adds the plugin's activation action to the the activation hook
	 */
	protected static function register_activation_hook() {
		register_activation_hook( SGCF_PLUGIN_FILE, array( __CLASS__, 'activate_action') );
	}

	/**
	 * Plugin's activate action
	 */
	public static function activate_action() {
		$recaptcha_opts = get_option('sgcf_recaptcha_options');
		if ( empty( $recaptcha_opts ) ) {
			$recaptcha_opts = array(
				'public_key' => '',
				'private_key' => '',
				'use_ssl' => '',
			);
			add_option( 'sgcf_recaptcha_options', $recaptcha_opts, '', 'no' );
		}
    }

	/**
	 * Adds the plugin's uninstall action to the uninstall hook.
	 */
	protected static function register_uninstall_hook() {
		register_activation_hook( SGCF_PLUGIN_FILE, array( __CLASS__, 'uninstall_action') );
	}

	/**
	 * Plugin's uninstall action.
	 */
	public static function uninstall_action() {
		// Deletes all form posts
		SGCF_Loader::load( 'SGCF_Form_Model', 'model' );
		$model = new SGCF_Form_Model();
		$posts = $model->find_all();
		foreach ( (array) $posts as $post ) {
			$post->delete();
		}

		// Deletes plugin options
		delete_option( 'sgcf_recaptcha_options' );
	}

	/**
	 * Loads the text domain used for loading translation files.
	 */
	public static function load_text_domain() {
        load_plugin_textdomain( SGCF_TEXT_DOMAIN, false, SGCF_PLUGIN_NAME . '/languages' );
    }

	/**
	 * Registers custom post types used in the plugin.
	 */
	protected static function register_custom_post_types () {
		register_post_type( 'sgcf_contact_form', array(
			'labels' => array(
				'name' => __( 'Contact Forms', SGCF_TEXT_DOMAIN ),
				'singular_name' => __( 'Contact Form', SGCF_TEXT_DOMAIN ) ),
			'rewrite' => false,
			'query_var' => false
		));
	}

	/**
	 * Checks for plugin's requirements, if they're not met the plugin is deactivated.
	 */
	protected static function check_requirements() {
		If ( version_compare( get_bloginfo( 'version' ), SGCF_REQUIRED_WP_VERSION, '<' ) ) {
			deactivate_plugins( SGCF_PLUGIN_FILE );
		}
	}

}