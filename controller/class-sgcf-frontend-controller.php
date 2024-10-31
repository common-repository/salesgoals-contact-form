<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

SGCF_Loader::uses('SGCF_Controller', 'controller');

/**
 * Class SGCF_Frontend_Controller
 *
 * Controller to handle frontend related actions.
 */
class SGCF_Frontend_Controller extends SGCF_Controller {

	/**
	 * The components preloaded by this controller
	 * @var array
	 */
	protected $components = array(
		'Shortcodes',
		'Salesgoals',
	);

	/**
	 * Initializes the controller by registering callbacks and actions
	 */
	protected function initialize() {
		parent::initialize();
		$this->Shortcodes->register();
		$this->Shortcodes->request = $this->request;
		$this->Shortcodes->register_success_callback( array( $this, '_send_form_email' ) );
		$this->Shortcodes->register_success_callback( array( $this, '_send_form_autoresponse' ) );
		$this->Shortcodes->register_success_callback( array( $this, '_create_salesgoals_contact' ) );
		$this->hook_action( 'wp_enqueue_scripts', '_setup_scripts' );
	}

	/**
	 * A callback that is executed when a form is submitted.
	 * Sends the form by email.
	 * @param $form
	 */
	public function _send_form_email( $form ) {
		$vars = $this->current_form_vars( $form );
		$message = $this->process_template( $vars, $form->mail_template );
		$from = $this->process_template( $vars, $form->mail_from );
		$subject = $this->process_template( $vars, $form->mail_subject );
		$to = $this->process_template( $vars, $form->mail_to );;
		$headers = array( 'From: ' . $from );
		wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * A callback that is executed when a form is submitted.
	 * Sends an automatic response if enabled.
	 * @param $form
	 */
	public function _send_form_autoresponse( $form ) {
		if ( ! $form->auto_enabled )
			return;
		$vars = $this->current_form_vars( $form );
		$message = $this->process_template( $vars, $form->auto_template );
		$from = $this->process_template( $vars, $form->auto_from );
		$subject = $this->process_template( $vars, $form->auto_subject );
		$to = $this->Shortcodes->form_data('sender-email');
		$headers = array( 'From: ' . $from );
		wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * A callback that is executed when a form is submitted.
	 * Creates a calendar appointment on the salesgoals platform, if enabled.
	 * @param $form
	 */
	public function _create_salesgoals_contact( $form ) {
		if ( ! $form->sg_enabled )
			return;
		$this->Salesgoals->set_auth_token( $form->sg_auth_key );
		try {
			$contact = $this->Salesgoals->create_contact(array(
				'email' => $this->Shortcodes->form_data( 'sender-email' ),
				'firstName' => $this->Shortcodes->form_data( 'sender-name' ),
			));
			if ( is_object( $contact ) && $contact->success ) {
				$this->Salesgoals->create_calendar_item(
					$contact->contact->id,
					date( 'Y-m-d', strtotime( '+1 weekday' ) ),
					$this->Shortcodes->form_data( 'subject' ),
					$this->Shortcodes->form_data( 'message' )
				);
			}
		} catch ( SG_Exception $e ) { }
	}

	/**
	 * Processes content and replace variable tags with the respective variable content
	 * @param array $vars
	 * @param string $content
	 * @return string
	 */
	protected function process_template($vars, $content) {
		foreach ( (array) $vars as $key => $value )
			$content = str_replace( "[$key]", $value, $content );
		return $content;
	}

	/**
	 * Retrieves an associative array with the current form data
	 * @return array
	 */
	protected function current_form_vars( $form ) {
		$id = $form->ID;
		$vars = array();
		foreach ( $this->request->data as $key => $value ) {
			if (strpos($key, "{$id}_") !== 0) {
				continue;
			}
			$key = substr($key, strlen("{$id}_"));
			$vars[$key] = $value;
		}
		return $vars;
	}

	/**
	 * Add assets to the document header.
	 */
	public function _setup_scripts() {
		wp_register_style( 'sgcf_forms', plugins_url( '/assets/css/forms.css', SGCF_PLUGIN_FILE ) );
		wp_enqueue_style( 'sgcf_forms' );
	}

}