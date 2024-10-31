<?php

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

SGCF_Loader::uses( 'SGCF_Helper', 'helper' );

/**
 * Class SGCF_List_Helper
 *
 * This helper will implement lists related logic for our plugin views.
 */
class SGCF_List_Helper extends SGCF_Helper {

	/**
	 * Loads and instantiates a list named as $name.
	 * @param string $list
	 * @return WP_List_Table
	 */
	public function instantiate( $list ) {
		$class = 'SGCF_' . $list . '_List_Table';
		if ( ! class_exists( $class ) ) {
			SGCF_Loader::load( $class, false, SGCF_Loader::paths( 'helper' ) . 'lists' );
		}
		return new $class();
	}

}