<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

SGCF_Loader::uses( 'SGCF_Model', 'model' );

/**
 * Class SGCF_Custom_Post_Type_Model
 *
 * This model handles logic related to data from a custom post type.
 */
abstract class SGCF_Custom_Post_Type_Model extends SGCF_Model {

	/**
	 * Post type identifier
	 * @var string
	 */
	protected $post_type = false;

	/**
	 * Default conditions to list posts
	 * @var array
	 */
	protected $default_conditions = array(
		'post_type' => false,
		'orderby' => 'title',
		'order' => 'ASC',
		'posts_per_page' => -1,
	);

	/**
	 * Post fields
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Metadata fields
	 * @var array
	 */
	protected $meta_fields = array();

	/**
	 * Data from the post
	 * @var array
	 */
	protected $data = array();

	/**
	 * Post metadata
	 * @var array
	 */
	protected $meta_data = array();

	/**
	 * Creates a new model instance, if $data is given, it'll initialize the new model
	 * instance's data.
	 * @param array $data
	 * @return SGCF_Custom_Post_Type_Model
	 */
	public function create( $data = array() ) {
		$new = clone $this;
		$new->set( $data );
		return $new;
	}

	/**
	 * Sets new data in the model.
	 * @param array $data
	 */
	public function set( $data ) {
		// sanitize post title
		if ( !empty( $data['post_title'] ) )
			$data['post_title'] = wp_strip_all_tags( $data['post_title'] );

		if ( ! is_array( $this->data ) )
			$this->data = array();

		// collect post field data
		foreach ( $this->fields as $field ) {
			if ( array_key_exists( $field, $data ) ) {
				$this->data[ $field ] = $data[ $field ];
			}
		}

		// sets post metadata
		foreach ( $this->meta_fields as $field ) {
			if ( array_key_exists( $field, $data ) ) {
				$this->meta_data[ $field ] = $data[ $field ];
			}
		}
	}

	/**
	 * Resets model data, if the $data array is given that will become model's new data.
	 * @param bool|mixed $data
	 */
	public function reset( $data = false ) {
		$this->data = $this->meta_data = array();
		if ( ! empty( $data ) )
			$this->set( $data );
	}

	/**
	 * Returns all posts of this custom type.
	 * @param array $conditions
	 * @return array
	 */
	public function find_all( $conditions = array() ) {
		$this->default_conditions['post_type'] = $this->post_type;
		$result = new WP_Query( array_merge( $this->default_conditions, $conditions ) );
		$converted = array();
		foreach ( (array) $result->posts as $post ) {
			$model = $this->create( $post->to_array() );
			$model->set( $model->post_metadata() );
			$converted[] = $model;
		}
		return $converted;
	}

	/**
	 * Finds and loads a post where the post id equals to $id.
	 * @param $id
	 * @return bool|SGCF_Custom_Post_Type_Model
	 */
	public function find( $id ) {
		$result = get_post( $id );
		if ( $result ) {
			$model = $this->create( $result->to_array() );
			$model->set( $model->post_metadata() );
			return $model;
		}
		return false;
	}

	/**
	 * Saves post data and metadata. If the post ID is provided, that post will be updated,
	 * otherwise a new post will be created.
	 * @param bool $data
	 * @return bool|int|WP_Error
	 */
	public function save( $data = false ) {
		if ( !empty($data) ) {
			$this->set( $data );
		} elseif ( empty( $this->data ) ) {
			return false;
		}

		if ( ! $this->before_save() )
			return false;

		$create = empty( $this->data['ID'] );

		$this->data['post_type'] = $this->post_type;
		$this->data['post_status'] = 'publish';

		if ( $create ) {
			$id = wp_insert_post( $this->data );
		} else {
			$id = wp_update_post( $this->data );
		}

		foreach ( $this->meta_data as $field => $value) {
			update_post_meta($id, $field, $value);
		}

		$this->after_save( $create );

		return $id;
	}

	/**
	 * A function executed always before a post is saved, if it returns false the save
	 * operation is aborted.
	 * @return bool
	 */
	public function before_save() {
		return true;
	}

	/**
	 * A function executed always after a post is saved.
	 */
	public function after_save( $created ) {
	}

	/**
	 * Deletes a post with id equal to $id.
	 * @param $id
	 * @return bool
	 */
	public function delete ( $id ) {
		if ( empty( $id ) && ! empty( $this->ID ) )
			$id = $this->ID;
		return wp_delete_post( $id, true ) !== false;
	}

	/**
	 * Lets the data fields to be accessed as properties.
	 * @param $var
	 * @return mixed
	 */
	public function __get( $var ) {
		if ( !empty( $this->data[ $var ] ) )
			return $this->data[ $var ];
		if ( !empty( $this->meta_data[ $var ] ) )
			return $this->meta_data[ $var ];
	}

	/**
	 * Sets a data field through object properties.
	 * @param $var string
	 * @param $value
	 */
	public function __set( $var, $value ) {
		if ( $this->is_field( $var ) )
			$this->set( array( $var => $value ) );
		else
			$this->{$var} = $value;
	}

	/**
	 * Checks is a field data is set.
	 * This method is needed so empty() can handle model fields as properties.
	 * @param $key string
	 * @return bool
	 */
	public function __isset( $key ) {
		if ( null === $this->__get( $key ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Returns true if there's a field named $name
	 * @param $name string
	 * @return bool
	 */
	public function is_field( $name ) {
		return in_array( $name, $this->fields ) || in_array( $name, $this->meta_fields );
	}

	/**
	 * Returns an array with the model data fields set
	 * @return array
	 */
	public function to_array() {
		return array_merge( $this->data, $this->meta_data );
	}

	/**
	 * Loads post metadata and returns it as an associative array.
	 * @return array
	 */
	protected function post_metadata() {
		$data = array();
		$meta = get_post_meta($this->ID);
		foreach ( $meta as $field => $value ) {
			$data[ $field ] = reset( $value );
		}
		return $data;
	}

}