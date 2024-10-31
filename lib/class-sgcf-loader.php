<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

/**
 * Class SGCF_Loader
 *
 * This class is responsible for class auto-loading and class types registry.
 */
class SGCF_Loader {

	/**
	 * Maps class names to their file paths
	 * @var array
	 */
	protected static $map = array();

	/**
	 * Maps class types to their corresponding directory paths
	 * @var array
	 */
	protected static $paths = array();

	/**
	 * Defines class types and their corresponding directories
	 * @param array $paths
	 */
	public static function build( $paths ) {
		self::$paths = array_merge_recursive( self::$paths, $paths );
	}

	/**
	 * Gets the directory corresponding to the $type class type, if no class type is given
	 * it returns an associative array with all class types' directories.
	 * @param null|string $type
	 * @return array|bool
	 */
	public static function paths( $type = null ) {
		if ( ! empty( $type ) )
			return !empty(self::$paths[ $type ]) ? self::$paths[ $type ] : false;
		return self::$paths;
	}

	/**
	 * Declares a class type for a class, the class loader will then load the class when needed.
	 * @param string $class
	 * @param string $type
	 */
	public static function uses( $class, $type ) {
		self::$map[ $class ] = self::$paths[ $type ];
	}

	/**
	 * Loads a class from its file, if a $type is given then the class will be loaded from that
	 * class type directory, if the $alt_path is set, then it will be used to load the file.
	 * @param string $class
	 * @param string|bool $type
	 * @param string|bool $alt_path
	 */
	public static function load( $class, $type = false, $alt_path = false ) {
		$path = false;
		if ( ! empty( $alt_path ) ) {
			$path = $alt_path;
		} elseif ( empty($type) && ! empty( self::$map[ $class ] ) ) {
			$path = self::$map[ $class ];
		} elseif ( ! empty( $type ) ) {
			$path = self::paths( $type );
		}
		if ( ! empty( $path ) && is_string($path) ) {
			$file = 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
			require_once SGCF_PLUGIN_PATH . $path . '/' . $file;
		}
	}

	/**
	 * Registers the class autoload callback
	 */
	public static function register_autoload () {
		spl_autoload_register( array ( __CLASS__, 'load' ) );
	}

	/**
	 * Includes a 3rd party php file
	 * @param string $file_path
	 */
	public static function vendor( $file_path, $once = 'yes' ) {
		$path = SGCF_PLUGIN_PATH . 'vendor/' . $file_path;
		if ( $once == 'yes' )
			include_once $path;
		else
			include $path;
	}

}