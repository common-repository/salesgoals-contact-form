<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>

<div class="sg-form">

	<?php if ( !empty( $error ) ): ?>
		<div class="message error"><?php echo $error; ?></div>
	<?php endif; ?>

	<?php if ( !empty( $success ) ): ?>
		<div class="message success"><?php echo $success; ?></div>
	<?php endif; ?>

	<form action="<?php the_permalink(); ?>" method="post">
		<input type="hidden" name="__id" value="<?php echo $id; ?>" />
		<?php echo $content; ?>
		<div class="sg-submit">
			<input type="submit" value="<?php echo __( 'Send', SGCF_TEXT_DOMAIN ) ?>" />
		</div>
	</form>

	<?php if ( !empty( $form->sg_enabled ) && !empty( $form->sg_billing_status ) && $form->sg_billing_status == 'Free'): ?>
		<div class="powered-by"">
			<a href="http://www.salesgoals.com/" target="_blank"><?php echo __( 'Powered by SalesGoals.com', SGCF_TEXT_DOMAIN ); ?></a>
		</div>
	<?php endif; ?>

</div>