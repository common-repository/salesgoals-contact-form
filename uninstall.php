<?php

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

// If uninstall not called from WordPress, exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

if ( !defined( 'SGCF_PLUGIN_PATH' ) )
	define( 'SGCF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once SGCF_PLUGIN_PATH . 'salesgoals-contact-form.php';

SGCF_Plugin::uninstall_action();