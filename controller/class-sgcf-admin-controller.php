<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

SGCF_Loader::uses('SGCF_Controller', 'controller');

/**
 * Class SGCF_Admin_Controller
 *
 * This controller handles administration related actions.
 */
class SGCF_Admin_Controller extends SGCF_Controller {

	/**
	 * Default models.
	 * @var array
	 */
	protected $models = array( 'Form' );

	/**
	 * Default view helpers
	 * @var array
	 */
	protected $helpers = array( 'List' );

	/**
	 * Default controller components
	 * @var array
	 */
	protected $components = array( 'Salesgoals' );

	/**
	 * Default action
	 * @var string
	 */
	protected $default_action = 'index';

	/**
	 * User messages used on this controller
	 * @var array
	 */
	protected $messages = array(
		'successful_save' => array(
			'text' => 'The form was successfully saved',
			'class' => 'updated',
		),
		'error_save' => array(
			'text' => 'An error occurred while saving the form',
			'class' => 'error',
		),
		'successful_delete' => array(
			'text' => 'The form was successfully deleted',
			'class' => 'updated',
		),
		'error_delete' => array(
			'text' => 'An error occurred while deleting the form',
			'class' => 'error',
		),
		'settings_update_success' => array(
			'text' => 'Your settings were successfully updated',
			'class' => 'updated',
		),
		'settings_update_error' => array(
			'text' => 'An error occurred while trying to save your settings, please check the errors below',
			'class' => 'error',
		),
	);

	/**
	 * Controller's initialization method, it sets up the admin menu items in order to
	 * let the user access plugin's actions.
	 */
	protected function initialize() {
		parent::initialize();
		$this->hook_action( 'admin_menu', '_setup_admin_menu' );
		$this->hook_action( 'admin_enqueue_scripts', '_setup_scripts' );
		$this->hook_action( 'admin_init', '_register_settings' );
		$this->hook_action( 'wp_ajax_get_auth_key', '_get_auth_key' );
		$this->hook_action( 'wp_ajax_copy_auth_key', '_copy_auth_key' );
		$this->hook_action( 'wp_ajax_remove_auth_key', '_remove_auth_key' );
	}

	/**
	 * This is the default action, and it executes the default page's action depending on the requested page
	 */
	public function index() {
		$page = $this->request->get( 'page' );
		switch ( $page ) {
			case 'sgcf_forms':
				$this->list_forms();
				break;
			case 'sgcf_settings':
				$this->settings();
				break;
		}
	}

	/**
	 * This action processes the settings page
	 */
	public function settings() {
		$settings_updated = $this->request->get( 'settings-updated' );
		if ( $settings_updated === 'true' ) {
			$this->setMessage( 'settings_update_success' );
		} elseif ( $settings_updated === 'false' ) {
			$this->setMessage( 'settings_update_error' );
		}
		$this->render( 'settings' );
	}

	/**
	 * This action lists all contact forms and let's the user to edit/delete them, or create
	 * a new one.
	 */
	public function list_forms() {
		if ( $this->request->is( 'post' ) ) {
			check_admin_referer( 'bulk-forms' );
			$action = $this->request->data['action'] == -1 ?
				$this->request->data['action2'] : $this->request->data['action'];
			switch( $action ) {
				case 'delete':
					foreach ( (array) $this->request->data['form'] as $id ) {
						if (!$this->Form->delete($id)) {
							$this->setMessage('error_delete');
							break;
						} else {
							$this->setMessage('successful_delete');
						}
					}
					break;
			}
			$this->redirect();
		}
		$forms = $this->Form->find_all();
		$this->render( 'form_list', compact( 'forms' ) );
	}

	/**
	 * Contact form edition screen and saves those editions.
	 */
	public function edit_form() {
		$id = $this->request->get( 'id' );
		if ( empty( $id ) ) {
			return;
		}
		if ( !empty( $this->request->data ) ) {
			check_admin_referer( SGCF_PLUGIN_NAME . '-edit_' . $id );
			if ( $this->Form->save( $this->request->data ) ) {
				$this->redirect(array('message' => 'successful_save'));
			} else {
				$this->redirect(array('message' => 'error_save'));
			}
		} else {
			$this->request->data = $this->Form->find( $id )->to_array();
		}
		$auth_posts = $this->get_posts_with_auth( $id );
		$this->render( 'edit_form', compact( 'auth_posts' ) );
	}

	/**
	 * This action renders the create form screen, and handles the saving logic.
	 */
	public function add_form() {
		if ( !empty( $this->request->data ) ) {
			check_admin_referer( SGCF_PLUGIN_NAME . '-add' );
			if ( $this->Form->save( $this->request->data ) ) {
				$this->redirect(array(
					'action' => 'list_forms',
					'message' => 'successful_save'
				));
			} else {
				$this->redirect(array('message' => 'error_save'));
			}
		} else {
			$this->request->data = $this->Form->defaultData();
		}
		$auth_posts = $this->get_posts_with_auth();
		$this->render( 'add_form', compact( 'message', 'auth_posts' ) );
	}

	/**
	 * Returns a list of posts with a Salesgoals.com auth key that belong to the
	 * current logged in user.
	 * @param integer|null $exclude_id
	 * @return array
	 */
	protected function get_posts_with_auth( $exclude_id = null ) {
		$posts = $this->Form->find_all( array(
			'author='.get_current_user_id(),
			'meta_key' => 'sg_enabled',
			'meta_value' => true,
		) );
		foreach ( $posts as $key => $post ) {
			if ( empty( $post->sg_auth_key ) || $post->ID == $exclude_id )
				unset( $posts[ $key ] );
		}
		return $posts;
	}

	/**
	 * Deletes a contact form
	 */
	public function delete_form() {
		$id = $this->request->get( 'id' );
		if ( empty( $id ) )
			return;
		check_admin_referer( SGCF_PLUGIN_NAME . '-delete_form_' . $id );
		if ( $this->Form->delete( $id ) ) {
			$this->redirect(array(
				'message' => 'successful_delete',
				'action' => 'list_forms',
			));
		} else {
			$this->redirect(array(
				'message' => 'error_delete',
				'action' => 'list_forms',
			));
		}
	}

	/**
	 * Copies a Salesgoals authentication key from a post to another one
	 */
	public function _copy_auth_key() {
		$from = intval($this->request->post( 'from' ));
		$to = intval($this->request->post( 'to' ));
		$from_post = $this->Form->find( $from );
		$to_post = $this->Form->find( $to );
		$user_id = get_current_user_id();
		$is_admin = is_admin();
		if ( is_object($from_post) && ( $from_post->is_owned_by( $user_id ) || $is_admin ) ) {
			if ( is_object($to_post) && ( $to_post->is_owned_by( $user_id ) || $is_admin ) ) {
				$this->Form->save(array(
					'ID' => $to,
					'sg_enabled' => true,
					'sg_auth_key' => $from_post->sg_auth_key,
					'sg_auth_user' => $from_post->sg_auth_user,
					'sg_billing_status' => $from_post->sg_billing_status,
				));
			}
			$data = array(
				'success' => true,
				'auth_key' => $from_post->sg_auth_key,
				'user' => $from_post->sg_auth_user,
				'billing_status' => $from_post->sg_billing_status,
			);
		} else {
			$data = array(
				'success' => false,
				'message' =>
					__( 'Your request has incorrect parameters, please try again later', SGCF_TEXT_DOMAIN ),
			);
		}
		echo json_encode( $data );
		die; // prevents the default behavior which adds the exit status to the end of the response body
	}

	/**
	 * Retrives an authentication key using the Salesgoals.com API
	 */
	public function _get_auth_key() {
		$username = $this->request->post( 'username' );
		$password = $this->request->post( 'password' );
		$id = $this->request->post( 'form_id' );
		try {
			$response = $this->Salesgoals->login( $username, $password );
			$status = $this->Salesgoals->get_billing_status();
			if ( empty( $response->error ) ) {
				$data = array(
					'success' => true,
					'username' => $username,
					'auth_key' => $response->token,
					'billing_status' => $status,
				);
				// automatically saves the key if the form already exists
				if ( !empty( $id ) ) {
					$post = $this->Form->find( $id );
					$user_id = get_current_user_id();
					if ( !empty( $id ) && is_object($post) && ( $post->is_owned_by( $user_id ) || is_admin() ) ) {
						$this->Form->save(array(
							'ID' => $id,
							'sg_enabled' => true,
							'sg_auth_key' => $response->token,
							'sg_auth_user' => $username,
							'sg_billing_status' => $status,
						));
					} else {
						$data = array(
							'success' => false,
							'message' => __( "You are not authorized to do this action", SGCF_TEXT_DOMAIN ),
						);
					}
				}
			} else {
				$message = __( 'An unexpected error occurred during the operation', SGCF_TEXT_DOMAIN );
				switch ($response->error) {
					case 'MissingParameters':
						$message = __( 'Please provide the username and password', SGCF_TEXT_DOMAIN );
						break;
					case 'BadLogin':
						$message = __( 'The username or password are not valid', SGCF_TEXT_DOMAIN );
						break;
					case 'UserDisabled':
						$message = __( 'The user account is currently disabled', SGCF_TEXT_DOMAIN );
						break;
				}
				$data = array(
					'success' => false,
					'message' => $message,
				);
			}
		} catch (SG_RemoteCall_Exception $e) {
			$data = array(
				'success' => false,
				'message' => __(
					'It was not possible to connect to server, please try again later',
					SGCF_TEXT_DOMAIN
				),
			);
		}
		echo json_encode( $data );
		die; // prevents the default behavior which adds the exit status to the end of the response body
	}

	/**
	 * Removes a Salesgoals.com authentication key from a post
	 */
	public function _remove_auth_key() {
		$id = intval( $this->request->post( 'form' ) );
		$post = $this->Form->find( $id );
		if ( ! empty( $post ) && ( $post->is_owned_by( get_current_user_id() ) || is_admin() ) ) {
			$this->Form->save(array(
				'ID' => $id,
				'sg_enabled' => false,
				'sg_auth_key' => '',
				'sg_auth_user' => '',
			));
			$data = array( 'success' => true );
		} else {
			$data = array(
				'success' => false,
				'message' => __( "The request has incorrect parameters or you are not authorized to do this action", SGCF_TEXT_DOMAIN ),
			);
		}
		echo json_encode( $data );
		die; // prevents the default behavior which adds the exit status to the end of the response body
	}

	/**
	 * Sets up administration menu items for the plugin.
	 */
	public function _setup_admin_menu() {
		add_menu_page(
			'Salesgoals',
			__( 'SalesGoals CF', SGCF_TEXT_DOMAIN ),
			'manage_options',
			'salesgoals',
			false,
			SGCF_URL . 'assets/img/icon-16x16.png'
		);
		add_submenu_page(
			'salesgoals',
			__( 'SalesGoals contact forms', SGCF_TEXT_DOMAIN ),
			__( 'Contact forms', SGCF_TEXT_DOMAIN ),
			'manage_options', 'sgcf_forms',
			array( $this, 'dispatch_action' )
		);
		add_submenu_page(
			'salesgoals',
			__( 'Settings', SGCF_TEXT_DOMAIN ),
			__( 'Settings', SGCF_TEXT_DOMAIN ),
			'manage_options', 'sgcf_settings',
			array( $this, 'dispatch_action' )
		);
		// remove the default parent menu reference
		remove_submenu_page('salesgoals', 'salesgoals');
	}

	/**
	 * Include plugin's CSS and JS files
	 */
	public function _setup_scripts() {
		if ( !in_array($this->request->get('page'), array( 'sgcf_forms', 'sgcf_settings' ) ) )
			return;

		wp_enqueue_script( 'jquery' );

		wp_register_script( 'tip_tip', plugins_url( '/assets/js/jquery.tipTip.minified.js', SGCF_PLUGIN_FILE ), array('jquery') );
		wp_enqueue_script( 'tip_tip' );

		wp_register_script( 'sgcf_forms', plugins_url( '/assets/js/forms.js', SGCF_PLUGIN_FILE ), array('jquery', 'tip_tip') );
		wp_enqueue_script( 'sgcf_forms' );

		wp_register_style( 'tip_tip', plugins_url( '/assets/css/tipTip.css', SGCF_PLUGIN_FILE ) );
		wp_enqueue_style( 'tip_tip' );

		wp_register_style( 'sgcf_forms', plugins_url( '/assets/css/admin_forms.css', SGCF_PLUGIN_FILE ) );
		wp_enqueue_style( 'sgcf_forms' );

	}

	/**
	 * registers plugin settings
	 */
	public function _register_settings() {
		register_setting(
			'sgcf_recaptcha',
			'sgcf_recaptcha_options',
			''
		);
	}

}