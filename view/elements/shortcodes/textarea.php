<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<div class="sg-input">
	<?php
	if ( !empty( $label ) )
		echo "<label for='$name'>$label</label>";

	echo "<textarea name='$name' class='$class' id='$id'>$value</textarea>";

	if ( !empty( $error ) )
		echo "<div class='error'>$error</div>";
	?>
</div>