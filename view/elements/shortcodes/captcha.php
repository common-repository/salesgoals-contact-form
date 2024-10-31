<?php if ( ! empty( $theme ) ): ?>
	<script type="text/javascript">
		var RecaptchaOptions = {
			theme : '<?php echo $theme; ?>'
		};
	</script>
<?php endif; ?>

<?php if ( empty($nowrap) ): ?>
	<div class="sg-captcha">
<?php endif; ?>

<?php
	echo recaptcha_get_html($public_key, null, $use_ssl);
?>

<?php if ( ! empty( $error ) ): ?>
	<div class="error">
		<?php echo $error; ?>
	</div>
<?php endif; ?>

<?php if ( empty($nowrap) ): ?>
	</div>
<?php endif; ?>