<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php echo esc_html( __( 'Edit contact form', SGCF_TEXT_DOMAIN ) ); ?></h2>

	<?php $this->element('contact_form', array(
		'nonce_token' => 'edit_'.$this->request->data('ID'),
		'auth_posts' => $auth_posts,
	)); ?>
</div>
