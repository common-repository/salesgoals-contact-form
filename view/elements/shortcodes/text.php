<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>

<?php if ( empty($nowrap) ): ?>
	<div class="sg-input">
<?php endif; ?>
<?php
	if ( !empty( $label ) )
		echo "<label for='$name'>$label</label>";

	echo "<input name='$name' type='$type' placeholder='$placeholder' class='$class' id='$id' value='$value' />";

	if ( !empty( $error ) )
		echo "<div class='error'>$error</div>";
?>
<?php if ( empty($nowrap) ): ?>
	</div>
<?php endif; ?>