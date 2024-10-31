<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

SGCF_Loader::uses( 'SGCF_Component', 'component' );

/**
 * This component wraps some methods of the Salesgoals REST API used by the plugin
 */
class SGCF_Salesgoals_Component extends SGCF_Component {

	/**
	 * Salesgoals API endpoint
	 * @var string
	 */
	protected $endpoint = 'https://my.salesgoals.com/crm';

	/**
	 * Will hold the current user authentication token
	 * @var null|string
	 */
	protected $token = null;

	/**
	 * Sets the current authentication token
	 * @param $token string The authentication token
	 */
	public function set_auth_token( $token ) {
		$this->token = $token;
	}

	/**
	 * Logs in a user with the provided credentials and returns the token
	 * @param $username string
	 * @param $password string
	 * @return array|bool|mixed
	 */
	public function login($username, $password) {
		$result = $this->remote_call( 'apiAuth', 'login', false, array(
			'user' => $username,
			'password' => $password,
		) );

		if ( empty( $result->error) )
			$this->token = $result->token;

		return $result;
	}

	/**
	 * Retrieves the billing status for the authenticated user
	 * @return string
	 */
	public function get_billing_status() {
		return $this->remote_call( 'apiBilling', 'status', true );
	}


	/**
	 * Creates a new contact with the provided information
	 * @param array $data
	 * @return array|bool|mixed
	 */
	public function create_contact ( $data = array() ) {
		$params = array( 'newVersion' => json_encode( $data ) );
		return $this->remote_call( 'apiContact', 'create', true, $params );
	}

	/**
	 * Creates a new calendar item for a certain contact
	 * @param $id
	 * @param $date
	 * @param $title
	 * @param $notes
	 * @param string $action
	 * @param string $address
	 * @return array|bool|mixed
	 */
	public function create_calendar_item( $id, $date, $title, $notes, $action = 'Emails', $address = '' ) {
		$params = array(
			'itemId' => $id,
			'eventDate' => $date,
			'activityType' => $action,
			'title' => $title,
			'notes' => $notes,
			'address' => $address,
		);
		return $this->remote_call( 'apiCalendar', 'newCalendarItem', true, $params );
	}

	/**
	 * Executes a remote call on the Salesgoals API accordingly to the provided controller,
	 * action and parameters.
	 * @param $controller
	 * @param $action
	 * @param bool $token
	 * @param array $params
	 * @return array|bool|mixed
	 * @throws SG_RemoteCall_Exception
	 */
	protected function remote_call( $controller, $action, $token = false, $params = array()) {
		if ( $token && ! empty( $this->token ) )
			$params['token'] = $this->token;

		$params = http_build_query( $params );
		$url = "{$this->endpoint}/$controller/$action?$params";
		$result = wp_remote_get( $url );

		if ( is_wp_error( $result ) ) {
			throw new SG_RemoteCall_Exception();
		}
		if ( $result['response']['code'] != 200 )
			return false;

		$object = json_decode( $result['body'] );
		if ( is_object( $object ) ) {
			return $object;
		}
		return $result['body'];
	}

}

/**
 * Class SG_Exception
 * The base class for the Salesgoals exceptions
 */
class SG_Exception extends Exception { }

/**
 * Class SG_RemoteCall_Exception
 * An exception thrown when a remote call fails
 */
class SG_RemoteCall_Exception extends SG_Exception { }