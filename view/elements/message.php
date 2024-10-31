<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

foreach ( $this->messages as $class => $message ) {
	echo "<div id='message' class='{$class}'><p>{$message}</p></div>";
}