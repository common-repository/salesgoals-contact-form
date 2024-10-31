<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2>
		<?php echo esc_html( __( 'SalesGoals Contact Forms', SGCF_TEXT_DOMAIN ) ); ?>
		<a href="<?php echo esc_url(sprintf('?page=%s&action=%s',$_REQUEST['page'],'add_form')) ?>" class="add-new-h2"><?php echo esc_html__( 'Add new', SGCF_TEXT_DOMAIN ); ?></a>
	</h2>
	<?php
	$this->element('message');

	$data = array();
	foreach ( $forms as $form ) {
		$author = WP_User::get_data_by('id', $form->post_author);
		$data[] = array(
			'ID' => $form->ID,
			'title' => $form->post_title,
			'author' => $author->display_name,
			'shortcode' => ''
		);
	}

	$list = $this->List->instantiate('Form');
	$list->load_data($data);
	$list->prepare_items();
	?>

	<form action="<?php echo add_query_arg(array('early' => 1)); ?>" method="post">
		<?php echo $list->display(); ?>
	</form>

</div>