<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

SGCF_Loader::uses( 'SGCF_Component', 'component' );

/**
 * Class SGCF_Shortcodes_Component
 *
 * The Shortcodes component.
 * This class implements contact form shortcodes and all the logic behind
 * it's submission.
 */
class SGCF_Shortcodes_Component extends SGCF_Component {

	/**
	 * The shortcodes list
	 * @var array
	 */
	protected $shortcodes = array(
		'sg-form', 'sg-sender-name', 'sg-subject', 'sg-sender-email', 'sg-message',
		'sg-text', 'sg-email', 'sg-textarea', 'sg-captcha',
	);

	/**
	 * Path to the shortcodes view files
	 * @var string
	 */
	protected $path = 'elements/shortcodes';

	/**
	 * Used to store the form model instance
	 * @var null
	 */
	protected $form = null;

	/**
	 * Tells whether occurred validation errors or not
	 * @var bool
	 */
	protected $has_errors = false;

	/**
	 * Tells when validation should run or not
	 * @var bool
	 */
	protected $validate = false;

	/**
	 * An array containing the callback functions to execute when the form
	 * is successfully submitted.
	 * @var array
	 */
	protected $callbacks = array();

	/**
	 * The current request
	 * @var null|SGCF_Request
	 */
	public $request = null;

	/**
	 * Registers the existent shortcodes' callbacks.
	 */
	public function register() {
		foreach ( (array) $this->shortcodes as $shortcode ) {
			$method = 'shortcode_' . preg_replace( '/[^a-z0-9_]/', '_', $shortcode );
			if ( method_exists( $this, $method ) )
				add_shortcode( $shortcode, array( $this, $method ) );
		}
	}

	/**
	 * sg-name shortcode callback.
	 * @param array $attrs
	 * @return string
	 */
	public function shortcode_sg_sender_name( $attrs = array() ) {
		$options = array(
			'required' => true,
			'name' => 'sender-name',
		);
		return $this->generate_input_shortcode($attrs, 'text', 'text', $options);
	}

	/**
	 * sg-subject shortcode callback
	 * @param array $attrs
	 * @return string
	 */
	public function shortcode_sg_subject( $attrs = array() ) {
		$options = array(
			'required' => true,
			'name' => 'subject',
		);
		return $this->generate_input_shortcode($attrs, 'text', 'text', $options);
	}

	/**
	 * sg-sender-email shortcode callback
	 * @param array $attrs
	 * @return string
	 */
	public function shortcode_sg_sender_email( $attrs = array() ) {
		$options = array(
			'required' => true,
			'name' => 'sender-email',
		);
		return $this->generate_input_shortcode($attrs, 'email', 'text', $options);
	}

	/**
	 * sg-message shortcode callback
	 * @param array $attrs
	 * @return string
	 */
	public function shortcode_sg_message( $attrs = array() ) {
		$options = array(
			'required' => true,
			'name' => 'message',
		);
		return $this->generate_input_shortcode($attrs, 'textarea', 'textarea', $options);
	}

	/**
	 * sg-text shortcode callback
	 * @param array $attrs
	 * @return string
	 */
	public function shortcode_sg_text( $attrs = array() ) {
		return $this->generate_input_shortcode($attrs, 'text', 'text');
	}

	/**
	 * sg-email shortcode callback
	 * @param array $attrs
	 * @return string
	 */
	public function shortcode_sg_email( $attrs = array() ) {
		return $this->generate_input_shortcode($attrs, 'email', 'text');
	}

	/**
	 * sg-textarea shortcode callback
	 * @param array $attrs
	 * @return string
	 */
	public function shortcode_sg_textarea( $attrs = array() ) {
		return $this->generate_input_shortcode($attrs, 'textarea', 'textarea');
	}

	/**
	 * Renders an input shortcode
	 * @param array $attrs
	 * @param $type
	 * @param $tpl
	 * @param array $extra_attrs
	 * @return bool|string
	 */
	public function generate_input_shortcode( $attrs = array(), $type, $tpl, $extra_attrs = array() ) {
		$defaults = array(
			'class' => '',
			'id' => '',
			'placeholder' => '',
			'label' => '',
			'name' => '',
			'required' => false,
			'nowrap' => false,
		);

		// boolean attributes need to be set manually
		$attrs_flipped = array_flip( $attrs );
		if ( array_key_exists( 'required', $attrs_flipped ) ) {
			$attrs['required'] = array_key_exists( 'required', $attrs_flipped );
		}
		if ( !array_key_exists( 'nowrap', $attrs_flipped ) ) {
			$attrs['nowrap'] = array_key_exists( 'nowrap', $attrs_flipped );
		}

		$defaults = array_merge( $defaults, $extra_attrs );
		$vars = shortcode_atts( $defaults, $attrs );
		if ( empty( $vars['name'] ) ) {
			trigger_error( __( 'It was found an input shortcode with no name attribute, ignoring.', SGCF_TEXT_DOMAIN ) );
			return false;
		}
		$vars['type'] = $type;
		$vars['value'] = $this->form_data( $vars['name'] );
		$vars['error'] = $this->is_invalid( $vars['name'], $vars );
		$vars['name'] = "{$this->form->ID}_{$vars['name']}";

		return $this->controller->render( $tpl, $vars, $this->path, 'no' );
	}

	/**
	 * sg-captcha shortcode callback
	 * @param array $attrs
	 * @return string
	 */
	public function shortcode_sg_captcha( $attrs = array() ) {
		$defaults = array(
			'public_key' => '',
			'private_key' => '',
			'nowrap' => false,
			'use_ssl' => false,
			'theme' => '',
		);
		// includes the reCAPTCHA lib needed by this shortcode
		SGCF_Loader::vendor( 'recaptchalib.php' );

		// boolean attributes need to be set manually
		if (!empty($attrs)) {
			$attrs_flipped = array_flip( $attrs );
			if ( array_key_exists( 'use_ssl', $attrs_flipped ) ) {
				$attrs['use_ssl'] = array_key_exists( 'use_ssl', $attrs_flipped );
			}
			if ( !array_key_exists( 'nowrap', $attrs_flipped ) ) {
				$attrs['nowrap'] = array_key_exists( 'nowrap', $attrs_flipped );
			}
		}

		$vars = shortcode_atts( $defaults, $attrs );
		$options = get_option( 'sgcf_recaptcha_options' );

		if ( empty( $vars['public_key'] ) || empty( $vars['private_key'] ) ) {
			$vars['private_key'] = $options['private_key'];
			$vars['public_key'] = $options['public_key'];
			$vars['use_ssl'] = $options['use_ssl'];
		}

		// validation
		if ( ! empty( $this->request->data ) && $this->validate ) {
			$resp = recaptcha_check_answer (
				$vars['private_key'],
				$this->request->ip_address(),
				$this->request->post('recaptcha_challenge_field'),
				$this->request->post('recaptcha_response_field')
			);
			if ( ! $resp->is_valid ) {
				$this->has_errors = true;
				$vars[ 'error' ] = "The codes didn't match, please try again";
			}
		}

		return $this->controller->render( 'captcha', $vars, $this->path, 'no' );
	}

	/**
	 * sg-form shortcode callback
	 * @param array $attrs
	 * @return bool|string
	 */
	public function shortcode_sg_form( $attrs = array() ) {
		if ( empty( $attrs['id'] ) )
			return false;

		$this->controller->load_model( 'Form' );
		$this->form = $form = $this->controller->Form->find( $attrs['id'] );

		$do_process_form =
			$this->request->is( 'post' ) && $this->request->post('__id') == $attrs['id'];

		if ( $do_process_form )
			$this->validate = true;

		$vars = array(
			'content' => do_shortcode($form->post_content),
			'id' => $attrs['id'],
			'form' => $this->form,
		);

		if ( $do_process_form ) {
			if ( ! $this->has_errors ) {
				$this->submit_data();
				$vars['success'] = __( 'Your message has been sent!', SGCF_TEXT_DOMAIN );
				foreach ( $this->request->data as $key => $val )
					$this->request->data[ $key ] = '';
				$this->validate = false;
				$vars['content'] = do_shortcode( $form->post_content );
			} else {
				$vars['error'] = __( 'It was not possible to submit your message, please check the errors below', SGCF_TEXT_DOMAIN );
			}
		}

		$this->validate = false;
		$this->has_errors = false;
		$this->form = null;

		return $this->controller->render( 'form', $vars, $this->path, 'no' );
	}

	/**
	 * Retrieves the current POSTed form field
	 * @param $name
	 * @return bool|mixed
	 */
	public function form_data( $name ) {
		return $this->request->data("{$this->form->ID}_{$name}");
	}

	/**
	 * Sets a request data variable to the current form.
	 * @param string $name
	 * @param mixed $value
	 * @return string
	 */
	private function set_form_data( $name, $value ) {
		return $this->request->data["{$this->form->ID}_{$name}"] = $value;
	}

	/**
	 * Validates a data item corresponding to an input shortcode
	 * @param string $name
	 * @param array $options
	 * @return bool|string|void
	 */
	protected function is_invalid( $name, $options ) {
		if ( ! $this->validate )
			return false;

		$item = $this->form_data( $name );

		if ( $options['required'] && empty( $item ) ) {
			$this->has_errors = true;
			return __( 'This field can not be left empty', SGCF_TEXT_DOMAIN );
		}

		$email_regex = '/[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+?\.[a-zA-Z]/';
		if ( $options['type'] == 'email' && !preg_match( $email_regex, $item ) ) {
			$this->has_errors = true;
			return __( 'The email address is not valid', SGCF_TEXT_DOMAIN );
		}

		return false;
	}

	/**
	 * This method is run when a form is submitted with no validation errors, and
	 * it executes all registered callbacks..
	 */
	protected function submit_data() {
		foreach ( (array) $this->callbacks as $callback )
			call_user_func_array( $callback, array( $this->form ) );
	}

	/**
	 * Register a callback function to be executed once the form is successfully submitted,
	 * the callback function may accept a form object as its argument.
	 * @param $callback
	 */
	public function register_success_callback( $callback ) {
		$this->callbacks[] = $callback;
	}

}