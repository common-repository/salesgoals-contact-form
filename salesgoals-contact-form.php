<?php
/*
Plugin Name: SalesGoals Contact Form
Plugin URI: http://www.salesgoals.com/salesgoals-com-contact-form-wp-plugin
Description: Easily add a contact us form to your website. Also enables integration with salesgoals.com CRM.
Version: 1.0.22
Author: SalesGoals.com
Author URI: http://www.salesgoals.com
License: GPLv3
*/

/* Copyright 2013 Self Evident Enterprises, LLC. (email : contact@selfevidententerprises.com)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301
USA */

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if ( !defined( 'SGCF_PLUGIN_BASENAME' ) )
define( 'SGCF_PLUGIN_BASENAME', plugin_basename( __FILE__  ) );

if ( !defined( 'SGCF_PLUGIN_NAME' ) )
define( 'SGCF_PLUGIN_NAME', dirname( plugin_basename( __FILE__  ) ) );

if ( !defined( 'SGCF_PLUGIN_FILE' ) )
define( 'SGCF_PLUGIN_FILE', __FILE__ );

if ( !defined( 'SGCF_VERSION' ) )
define( 'SGCF_VERSION', '1.0.5' );

if ( !defined( 'SGCF_REQUIRED_WP_VERSION' ) )
define( 'SGCF_REQUIRED_WP_VERSION', '3.5' );

if ( !defined( 'SGCF_TEXT_DOMAIN' ) )
define( 'SGCF_TEXT_DOMAIN', 'sgcf' );

if ( !defined( 'SGCF_PLUGIN_PATH' ) )
define( 'SGCF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

if ( !defined( 'SGCF_URL' ) )
	define( 'SGCF_URL', plugins_url() . '/' . basename( dirname(__FILE__) ) . '/' );

require_once 'lib/class-sgcf-loader.php';

// setup class paths
SGCF_Loader::build(array(
	'lib' => 'lib/',
	'controller' => 'controller/',
	'component' => 'controller/component/',
	'model' => 'model/',
	'view' => 'view/',
	'view_class' => 'view/class/',
	'helper' => 'view/helper/',
));

// registers the automatic class loading
SGCF_Loader::register_autoload();

SGCF_Loader::uses( 'SGCF_Plugin', 'lib' );
SGCF_Loader::uses( 'SGCF_Request', 'lib' );

// initializes the plugin
SGCF_Plugin::bootstrap(new SGCF_Request());

if ( is_admin() )
	SGCF_Plugin::load_admin ();
else
	SGCF_Plugin::load_frontend ();
