<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

SGCF_Loader::uses( 'SGCF_Custom_Post_Type_Model', 'model' );

/**
 * Class SGCF_Form_Model
 *
 * This model represents a plugin's contact form.
 */
class SGCF_Form_Model extends SGCF_Custom_Post_Type_Model {

	/**
	 * Custom post type used.
	 * @var string
	 */
	protected $post_type = 'sgcf_contact_form';

	/**
	 * Post fields used.
	 * @var array
	 */
	protected $fields = array(
		'ID',
		'post_title',
		'post_content',
		'post_author',
	);

	/**
	 * Fields that represent post meta data used.
	 * @var array
	 */
	protected $meta_fields = array(
		'mail_from',
		'mail_to',
		'mail_subject',
		'mail_template',
		'auto_enabled',
		'auto_from',
		'auto_subject',
		'auto_template',
		'sg_enabled',
		'sg_auth_key',
		'sg_auth_user',
		'sg_title',
		'sg_billing_status',
	);

	/**
	 * Returns the data shown by default in a vew form
	 * @return array
	 */
	public function defaultData() {
		$user = wp_get_current_user();
		return array(
			'post_content' =>
				'[sg-sender-name label="Name"]' . "\n\n" .
				'[sg-sender-email label="Email"]' . "\n\n" .
				'[sg-subject label="Subject"]' . "\n\n" .
				'[sg-message label="Message"]',
			'mail_to' => $user->data->user_email,
			'mail_from' => '[sender-name] <[sender-email]>',
			'mail_subject' => '[subject]',
			'mail_template' =>
				__( 'From', SGCF_TEXT_DOMAIN ) . ": [sender-name] <[sender-email]>\n" .
				__( 'Subject', SGCF_TEXT_DOMAIN ) . ": [subject]\n\n" .
				__( 'Message', SGCF_TEXT_DOMAIN ) . ":\n[message]\n\n",
			'auto_enabled' => false,
			'auto_from' => $user->data->user_email,
			'auto_subject' => '',
			'auto_template' => '',
			'sg_enabled' => true,
			'sg_auth_key' => '',
			'sg_auth_user' => '',
			'sg_billing_status' => false,
		);
	}

	/**
	 * Disables Salesgoals integration if an authentication key isn't set.
	 * @return bool
	 */
	public function before_save() {
		if ( ! parent::before_save() )
			return false;

		if ( ! empty( $this->sg_auth_key ) ) {
			$sg_key = $this->sg_auth_key;
		} elseif ( ! empty( $this->ID ) ) {
			$sg_key = $this->find($this->ID)->sg_auth_key;
		} else {
			$sg_key = null;
		}
		if ( ! empty( $this->sg_enabled ) && empty( $sg_key ) )
			$this->sg_enabled = false;

		return true;
	}

	/**
	 * Tells whether a post is owned by a specified user.
	 * @param $user_id
	 * @return bool
	 */
	public function is_owned_by( $user_id ) {
		$user_id = intval( $user_id );
		$post_author = intval( $this->post_author );
		return $user_id && $post_author == $user_id;
	}

}