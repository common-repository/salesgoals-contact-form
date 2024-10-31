<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

/**
 * Class SGCF_Request
 *
 * This class holds information about the current request.
 */
class SGCF_Request {

	/**
	 * GET variables
	 * @var array
	 */
	public $get = array();

	/**
	 * POST variables
	 * @var array
	 */
	public $post = array();

	/**
	 * This array is used to carry data submitted by and into forms
	 * @var array
	 */
	public $data = array();

	/**
	 * Request type detecting data
	 * @var array
	 */
	protected $detectors = array(
		'get' => array('env' => 'REQUEST_METHOD', 'value' => 'GET'),
		'post' => array('env' => 'REQUEST_METHOD', 'value' => 'POST'),
		'put' => array('env' => 'REQUEST_METHOD', 'value' => 'PUT'),
		'delete' => array('env' => 'REQUEST_METHOD', 'value' => 'DELETE'),
		'ajax' => array('env' => 'HTTP_X_REQUESTED_WITH', 'value' => 'XMLHttpRequest'),
		'ssl' => array('env' => 'HTTPS', 'value' => 1),
	);

	public $env = array();

	/**
	 * Class constructor, it accepts a flag which says whether or not process the web
	 * environment variables. Useful for testing porposes.
	 * @param string|bool $processEnvironment
	 */
	public function __construct( $processEnvironment = true ) {
		if ( $processEnvironment ) {
			$this->env = $_SERVER;
			$this->processGet();
			$this->processPost();
		}
	}

	/**
	 * Gathers request's GET variables
	 */
	protected function processGet() {
		$this->get = $_GET;
	}

	/**
	 * Gathers request's POST variables
	 */
	protected function processPost() {
		$this->post = $_POST;
		if ( !empty( $this->post ) ) {
			$this->data = $this->post;
		}
	}

	/**
	 * Gets a data variable, it is mostly used by forms.
	 * If that variable doesn't exist false will be returned.
	 * @return mixed|bool
	 */
	public function data( $var ) {
		if ( !empty( $this->data[ $var ] ) ) {
			return $this->data[ $var ];
		}
		return false;
	}

	/**
	 * Gets a GET variable, if that variable doesn't exist false will be returned.
	 * @param string $var
	 * @return bool|mixed
	 */
	public function get( $var ) {
		if ( !empty( $this->get[ $var ] ) ) {
			return $this->get[ $var ];
		}
		return false;
	}

	/**
	 * Gets a POST variable, if that variable doesn't exist false will be returned.
	 * @param string $var
	 * @return bool|mixed
	 */
	public function post( $var ) {
		if ( !empty( $this->post[ $var ] ) ) {
			return $this->post[ $var ];
		}
		return false;
	}

	/**
	 * Check whether or not a request is a certain type.
	 * @param string $method
	 * @return bool
	 */
	public function is( $method ) {
		$detector = $this->detectors[ $method ];
		return $this->env[ $detector['env'] ] == $detector['value'];
	}

	/**
	 * Returns remote ip address
	 * @return string
	 */
	public function ip_address() {
		return $this->env["REMOTE_ADDR"];
	}

}