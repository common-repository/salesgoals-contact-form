<?php

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if ( ! class_exists( 'WP_List_Table' ) )
	require_once ( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class SGCF_Form_List_Table extends WP_List_Table {

	public $data = array();

	public function __construct($args = array()) {
		parent::__construct(array(
			'plural' => 'forms',
			'singular' => 'form',
		));
	}

	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __('Title', SGCF_TEXT_DOMAIN),
			'shortcode' => __('Shortcode', SGCF_TEXT_DOMAIN),
			'author'    => __('Author', SGCF_TEXT_DOMAIN),
		);
		return $columns;
	}

	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->data;
	}

	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	public function column_shortcode( $item ) {
		return "[sg-form id='{$item['ID']}']";
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'title'  => array('title',true),
			'author' => array('author',true),
		);
		return $sortable_columns;
	}

	public function column_title($item) {
		$delete_url = wp_nonce_url(
			sprintf('?page=%s&action=%s&id=%s&early=1',$_REQUEST['page'],'delete_form', $item['ID']),
			SGCF_PLUGIN_NAME . '-delete_form_' . $item['ID']
		);
		$actions = array(
			'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>',$_REQUEST['page'],'edit_form',$item['ID']),
			'delete' => "<a href='$delete_url'>Delete</a>",
		);

		return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions) );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete'    => 'Delete'
		);
		return $actions;
	}

	public function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="form[]" value="%s" />', $item['ID']
		);
	}

	public function load_data( $data ) {
		$this->data = $data;
	}

}