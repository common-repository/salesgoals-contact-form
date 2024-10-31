<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

// Set the plugin options using the wp options API
add_settings_section(
	'sgcf-recaptcha',
	__( 'reCAPTCHA Settings', SGCF_TEXT_DOMAIN ),
	'sgcf_print_recaptcha_settings',
	'sgcf_settings'
);

add_settings_field(
	'sgcf_recaptcha_pub_key',
	__( 'Public key', SGCF_TEXT_DOMAIN ),
	'sgcf_print_recaptcha_pub_key',
	'sgcf_settings',
	'sgcf-recaptcha'
);

add_settings_field(
	'sgcf_recaptcha_priv_key',
	__( 'Private key', SGCF_TEXT_DOMAIN ),
	'sgcf_print_recaptcha_priv_key',
	'sgcf_settings',
	'sgcf-recaptcha'
);

add_settings_field(
	'sgcf_recaptcha_use_ssl',
	__( 'Use SSL', SGCF_TEXT_DOMAIN ),
	'sgcf_print_recaptcha_use_ssl',
	'sgcf_settings',
	'sgcf-recaptcha'
);

/**
 * Echoes the reCAPTCHA section help text
 */
function sgcf_print_recaptcha_settings() {
	echo sprintf(
		'<p>%s. <a href="http://recaptcha.net/">%s</a>.<br>%s <a href="https://www.google.com/recaptcha/admin/create">%s</a>, %s.</p>',
		__( 'The SalesGoals Contact Form plugin uses the reCAPTCHA service to prevent automated abuse of your contact forms', SGCF_TEXT_DOMAIN ),
		__( 'Read more about recaptcha', SGCF_TEXT_DOMAIN ),
		__( 'In order to use this service you will need to create your reCAPTCHA keys, you can do it', SGCF_TEXT_DOMAIN ),
		__( 'here', SGCF_TEXT_DOMAIN ),
		__( 'and then fill in the form below with your private and public key', SGCF_TEXT_DOMAIN )
	);
}

/**
 * echoes the reCAPTCHA private key input
 */
function sgcf_print_recaptcha_priv_key() {
	$options = get_option( 'sgcf_recaptcha_options' );
	$key = $options['private_key'];
	echo "<input id='sgcf-recaptcha-private-key' name='sgcf_recaptcha_options[private_key]' type='text' value='{$key}' />";
	echo "<p class='description'>" . __( 'Fill in with your registered reCAPTCHA private key', SGCF_TEXT_DOMAIN ) . ".</p>";
}

/**
 * echoes the reCAPTCHA public key input
 */
function sgcf_print_recaptcha_pub_key() {
	$options = get_option( 'sgcf_recaptcha_options' );
	$key = $options['public_key'];
	echo "<input id='sgcf-recaptcha-public-key' name='sgcf_recaptcha_options[public_key]' type='text' value='{$key}' />";
	echo "<p class='description'>" . __( 'Fill in with your registered reCAPTCHA public key', SGCF_TEXT_DOMAIN ) . ".</p>";
}

/**
 * echoes the reCAPTCHA "use ssl" setting checkbox
 */
function sgcf_print_recaptcha_use_ssl() {
	$options = get_option( 'sgcf_recaptcha_options' );
	$use = ! empty( $options['use_ssl'] );
	$checked = $use ? 'checked="checked"' : '';
	echo "<input name='sgcf_recaptcha_options[use_ssl]' type='hidden' value='0' />";
	echo "<input name='sgcf_recaptcha_options[use_ssl]' type='checkbox' value='1' {$checked} />";
	echo "<p class='description'>" . __( 'Select this if you want to use a secure connection while communicating with reCAPTCHA servers', SGCF_TEXT_DOMAIN ) . ".</p>";
}

?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php echo esc_html( __( 'SalesGoals CF Settings', SGCF_TEXT_DOMAIN ) ); ?></h2>

	<?php $this->element('message'); ?>

	<form action="options.php" method="post">
		<?php
			settings_fields( 'sgcf_recaptcha' );
			do_settings_sections( 'sgcf_settings' );
		?>
		<?php submit_button(); ?>

	</form>

</div>
