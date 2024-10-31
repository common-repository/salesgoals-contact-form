<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>

<?php $this->element('message'); ?>

<div id="poststuff">
	<form action="<?php echo add_query_arg(array('early' => 1)); ?>" method="post">

		<?php wp_nonce_field( SGCF_PLUGIN_NAME . '-' . $nonce_token ); ?>
		<input name='ID' type="hidden" value="<?php echo esc_attr_e($this->request->data('ID')); ?>" />
		<input name='sgcf_id' type="hidden" value="<?php esc_attr_e($this->request->data('sgcf_id')); ?>" />

		<div id="titlediv">
			<div id="titlewrap">
				<input placeholder="<?php echo __( 'Enter title here', SGCF_TEXT_DOMAIN ); ?>" required="required" type="text" name="post_title" id="title" value="<?php esc_attr_e($this->request->data('post_title')); ?>" />
			</div>
		</div>

		<div id="form-content-holder">
			<div class="half-left">
				<?php
					wp_editor(
						$this->request->data('post_content'),
						'post_content',
						array(
							'editor_height' => 360,
							'media_buttons' => false,
						)
					);
				?>
			</div>
			<div class="half-right">
				<div id="form-fields">
					<div id="fieldsdiv" class="postbox">
						<h3><span><?php echo __( 'Add fields', SGCF_TEXT_DOMAIN ); ?></span></h3>
						<div class="inside">
							<ul id="field-links">
								<li>
									<a class="thickbox button-secondary" href="#TB_inline?width=400&inlineId=text-field"><?php echo __( 'Text', SGCF_TEXT_DOMAIN ); ?></a>
								</li>
								<li>
									<a class="thickbox button-secondary" href="#TB_inline?width=400&inlineId=textarea-field"><?php echo __( 'Textarea', SGCF_TEXT_DOMAIN ); ?></a>
								</li>
								<li>
									<a class="thickbox button-secondary" href="#TB_inline?width=400&inlineId=email-field"><?php echo __( 'Email', SGCF_TEXT_DOMAIN ); ?></a>
								</li>
								<li>
									<a class="thickbox button-secondary" href="#TB_inline?width=400&inlineId=captcha-field"><?php echo __( 'Captcha', SGCF_TEXT_DOMAIN ); ?></a>
								</li>
							</ul>
							<br class="clear">
						</div>
					</div>
				</div>
			</div>
			<br class="clear">
		</div>

		<div id="form-salesgoals-settings" class="settings-container">
			<div id="sg-div" class="postbox">
				<h3><span><?php echo __( 'SalesGoals settings', SGCF_TEXT_DOMAIN ); ?></span></h3>
				<div class="inside">
					<div id="enable-sg">
						<div class="input" id="sg-enabled-check">
							<label for="sg_enabled"><?php echo __( 'Enable integration with SalesGoals', SGCF_TEXT_DOMAIN ); ?>:</label>
							<?php $checked = !empty($this->request->data['sg_enabled']) ? 'checked="checked"' : ''; ?>
							<input name='sg_enabled' type='hidden' value='0' />
							<input id="sg-enabled-check" name='sg_enabled' type='checkbox' value='1' <?php echo $checked; ?> />
						</div>
					</div>

					<div class="status">
						<?php
							$sg_user = $warning_msg = '';
							$display_warning = false;
							$display_success = false;
							if ( ! $this->request->data('sg_auth_key') ) {
								$display_warning = true;
							} else {
								$sg_user = $this->request->data['sg_auth_user'];
								$display_success = true;
							}
						?>
						<div id="sg-warning" class="alert-message warning <?php echo $display_warning ? '' : 'hidden';?>">
							<?php echo __(
								"You don't have an authentication key set yet, please use ".
								"the options below to get a SalesGoals authentication key",
								SGCF_TEXT_DOMAIN
							); ?>
						</div>
						<div id="sg-success" class="alert-message success <?php echo $display_success ? '' : 'hidden'; ?>">
							<?php
								echo __(
										'Using the following SalesGoals account',
										SGCF_TEXT_DOMAIN
									) . ": <span class='account'>{$sg_user}</span>. " .
									sprintf(
										__( 'You have a SalesGoals "%s" plan.', SGCF_TEXT_DOMAIN ),
										"<span class='plan'>{$this->request->data['sg_billing_status']}</span>"
									);
							?>
							<span id="remove-auth-key-spinner" class="spinner" style="display: none;"></span>
							<button id="remove-auth-key" class="button"><?php echo __( 'Remove key', SGCF_TEXT_DOMAIN ); ?></button>
							<br class="clear" />
						</div>
					</div>
					<div id="sg-get-auth" class="<?php echo ! empty( $this->request->data['sg_auth_key'] ) ? 'hidden' : ''; ?>">
						<div class="half-left">
							<div class="input">
								<label for="sg_username"><?php echo __( 'Username', SGCF_TEXT_DOMAIN ); ?>:</label>
								<input type="text" name="sg_username" value="<?php esc_attr_e($this->request->data('sg_auth_user')); ?>" />
							</div>
							<div class="input">
								<label for="sg_password"><?php echo __( 'Password', SGCF_TEXT_DOMAIN ); ?>:</label>
								<input type="password" name="sg_password" value="" />
							</div>
							<button id="get-auth-key" class="button button-primary right" type="button"><?php echo __( 'Get authentication key', SGCF_TEXT_DOMAIN); ?></button>
							<span id="get-auth-key-spinner" class="spinner" style="display: none;"></span>
						</div>

						<?php if ( ! empty( $auth_posts ) ): ?>
							<div class="half-right">
								<div class="input">
									<label for="sg_copy_form"><?php echo __( 'Copy the key from the following form', SGCF_TEXT_DOMAIN ); ?>:</label>
									<select name="sg_copy_form">
										<?php
										foreach ( (array) $auth_posts as $post ) {
											echo sprintf( '<option value="%s">%s</option>', $post->ID, $post->post_title );
										}
										?>
									</select>
								</div>
								<button id="copy-auth-key" class="button button-primary left" type="button"><?php echo __( 'Copy authentication key', SGCF_TEXT_DOMAIN); ?></button>
								<span id="copy-auth-key-spinner" class="spinner" style="display: none;"></span>
							</div>
						<?php endif; ?>

						<br class="clear">
					</div>
					<div class="sg-checkbox-info"><?php echo __( 'If you have a free plan in SalesGoals, a "Powered by SalesGoals.com" link will be shown below the contact form.', SGCF_TEXT_DOMAIN ); ?></div>
				</div>
			</div>
		</div>

		<div id="form-email-settings" class="settings-container">
			<div id="email-div" class="postbox">
				<h3><span><?php echo __( 'Email settings', SGCF_TEXT_DOMAIN ); ?></span></h3>
				<div class="inside">
					<div class="half-left">
						<div class="input">
							<label for="mail_from"><?php echo __( 'From', SGCF_TEXT_DOMAIN ); ?>:</label>
							<input type="text" name="mail_from" value="<?php esc_attr_e($this->request->data('mail_from')); ?>" />
						</div>
						<div class="input">
							<label for="mail_to"><?php echo __( 'To', SGCF_TEXT_DOMAIN ); ?>:</label>
							<input type="text" name="mail_to" value="<?php esc_attr_e($this->request->data('mail_to')); ?>" />
						</div>
						<div class="input">
							<label for="mail_subject"><?php echo __( 'Subject', SGCF_TEXT_DOMAIN ); ?>:</label>
							<input type="text" name="mail_subject" value="<?php esc_attr_e($this->request->data('mail_subject')); ?>" />
						</div>
					</div>
					<div class="half-right">
						<div class="input">
							<label for="mail_template"><?php echo __( 'Message body', SGCF_TEXT_DOMAIN ); ?>:</label>
							<textarea name="mail_template"><?php esc_attr_e($this->request->data('mail_template')); ?></textarea>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
		</div>

		<div id="form-autoresponse-settings" class="settings-container">
			<div id="autoresponse-div" class="postbox">
				<h3><span><?php echo __( 'Autoresponse settings', SGCF_TEXT_DOMAIN ); ?></span></h3>
				<div class="inside">
					<div id="enable-auto">
						<div class="input" id="auto-enabled-check">
							<label for="auto_enabled"><?php echo __( 'Enable automatic response', SGCF_TEXT_DOMAIN ); ?>:</label>
							<?php $checked = !empty($this->request->data['auto_enabled']) ? 'checked="checked"' : ''; ?>
							<input name='auto_enabled' type='hidden' value='0' />
							<input id="auto-enabled-check" name='auto_enabled' type='checkbox' value='1' <?php echo $checked; ?> />
						</div>
					</div>
					<div class="half-left">
						<div class="input">
							<label for="auto_from"><?php echo __( 'From', SGCF_TEXT_DOMAIN ); ?>:</label>
							<input type="text" name="auto_from" value="<?php esc_attr_e($this->request->data('auto_from')); ?>" />
						</div>
						<div class="input">
							<label for="auto_subject"><?php echo __( 'Subject', SGCF_TEXT_DOMAIN ); ?>:</label>
							<input type="text" name="auto_subject" value="<?php esc_attr_e($this->request->data('auto_subject')); ?>" />
						</div>
					</div>
					<div class="half-right">
						<div class="input">
							<label for="auto_template"><?php echo __( 'Message body', SGCF_TEXT_DOMAIN ); ?>:</label>
							<textarea name="auto_template"><?php esc_attr_e($this->request->data('auto_template')); ?></textarea>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
		</div>

		<?php submit_button( __( 'Save form', SGCF_TEXT_DOMAIN ), 'primary', 'submit' ); ?>

	</form>
</div>


<?php add_thickbox(); ?>

<?php $this->start('common_custom_field_attributes'); ?>
	<fieldset>
		<legend><?php echo __( 'General options', SGCF_TEXT_DOMAIN ); ?></legend>
		<div class="input">
			<label for="name"><?php echo __( 'Name', SGCF_TEXT_DOMAIN ); ?></label>
			<input type="text" name="name" required="required" />
		</div>
		<div class="input">
			<label for="label"><?php echo __( 'Label', SGCF_TEXT_DOMAIN ); ?></label>
			<input type="text" name="label" />
		</div>
		<div class="input">
			<label for="placeholder"><?php echo __( 'Placeholder', SGCF_TEXT_DOMAIN ); ?></label>
			<input type="text" name="placeholder" />
		</div>
		<div class="input">
			<label for="required"><?php echo __( 'Is this field required?', SGCF_TEXT_DOMAIN ); ?></label>
			<input type="checkbox" name="required" />
		</div>
	</fieldset>
	<fieldset>
		<legend><?php echo __( 'Display options', SGCF_TEXT_DOMAIN ); ?></legend>
		<div class="input">
			<label for="class"><?php echo __( 'Class', SGCF_TEXT_DOMAIN ); ?></label>
			<input type="text" name="class" />
		</div>
		<div class="input">
			<label for="id"><?php echo __( 'Id', SGCF_TEXT_DOMAIN ); ?></label>
			<input type="text" name="id" />
		</div>
		<div class="input">
			<label for="nowrap"><?php echo __( 'Disable input wrapper?', SGCF_TEXT_DOMAIN ); ?></label>
			<input type="checkbox" name="nowrap" />
		</div>
	</fieldset>
<?php $this->end(); ?>

<div id="text-field" style="display: none;">
	<div class="custom-field-dialog" data-type="sg-text">
		<h2><?php echo __( 'Add text field', SGCF_TEXT_DOMAIN ); ?></h2>
		<?php echo $this->fetch('common_custom_field_attributes'); ?>
		<button class="button-primary"><?php echo __( 'Insert', SGCF_TEXT_DOMAIN ); ?></button>
	</div>
</div>

<div id="email-field" style="display: none;">
	<div class="custom-field-dialog" data-type="sg-email">
		<h2><?php echo __( 'Add email field', SGCF_TEXT_DOMAIN ); ?></h2>
		<?php echo $this->fetch('common_custom_field_attributes'); ?>
		<button class="button-primary"><?php echo __( 'Insert', SGCF_TEXT_DOMAIN ); ?></button>
	</div>
</div>

<div id="textarea-field" style="display: none;">
	<div class="custom-field-dialog" data-type="sg-textarea">
		<h2><?php echo __( 'Add textarea field', SGCF_TEXT_DOMAIN ); ?></h2>
		<fieldset>
			<legend><?php echo __( 'Input options', SGCF_TEXT_DOMAIN ); ?></legend>
			<div class="input">
				<label for="cols"><?php echo __( 'Columns', SGCF_TEXT_DOMAIN ); ?></label>
				<input type="number" name="cols" />
			</div>
			<div class="input">
				<label for="rows"><?php echo __( 'Rows', SGCF_TEXT_DOMAIN ); ?></label>
				<input type="number" name="rows" />
			</div>
		</fieldset>
		<?php echo $this->fetch('common_custom_field_attributes'); ?>
		<button class="button-primary"><?php echo __( 'Insert', SGCF_TEXT_DOMAIN ); ?></button>
	</div>
</div>

<div id="captcha-field" style="display: none;">
	<div class="custom-field-dialog" data-type="sg-captcha">
		<h2><?php echo __( 'Add captcha', SGCF_TEXT_DOMAIN ); ?></h2>
		<fieldset>
			<legend><?php echo __( 'reCAPTCHA options', SGCF_TEXT_DOMAIN ); ?></legend>
			<?php
				echo sprintf(
					'<p class="description">%s. <a href="http://recaptcha.net/">%s</a>.<br>%s <a href="https://www.google.com/recaptcha/admin/create">%s</a>, %s.</p>',
					__( 'The SalesGoals Contact Form plugin uses the reCAPTCHA service to prevent automated abuse of your contact forms', SGCF_TEXT_DOMAIN ),
					__( 'Read more about recaptcha', SGCF_TEXT_DOMAIN ),
					__( 'In order to use this service you will need to create your reCAPTCHA keys, you can do it', SGCF_TEXT_DOMAIN ),
					__( 'here', SGCF_TEXT_DOMAIN ),
					__( 'and then fill in the form below with your private and public key', SGCF_TEXT_DOMAIN )
				);
			?>
			<div class="input">
				<label for="use_global">
					<?php echo __( 'Use global options', SGCF_TEXT_DOMAIN ); ?>
					<a href="#" class="help-tip" title="<?php echo __( 'Check this box if you want to use the reCAPTCHA settings defined on the plugin settings page', SGCF_TEXT_DOMAIN ); ?>">[?]</a>
				</label>
				<input type="checkbox" name="use_global" />
			</div>
			<br class="clear" />
			<div class="input">
				<label for="public_key">
					<?php echo __( 'Public key', SGCF_TEXT_DOMAIN ); ?>
					<a href="#" class="help-tip" title="<?php echo __( 'Fill in with your registered reCAPTCHA private key', SGCF_TEXT_DOMAIN ); ?>">[?]</a>
				</label>
				<input type="text" name="public_key" />
			</div>
			<div class="input">
				<label for="private_key">
					<?php echo __( 'Private key', SGCF_TEXT_DOMAIN ); ?>
					<a href="#" class="help-tip" title="<?php echo __( 'Fill in with your registered reCAPTCHA public key', SGCF_TEXT_DOMAIN ); ?>">[?]</a>
				</label>
				<input type="text" name="private_key" />
			</div>
			<div class="input">
				<label for="use_ssl">
					<?php echo __( 'Use SSL', SGCF_TEXT_DOMAIN ); ?>
					<a href="#" class="help-tip" title="<?php echo __( 'Select this if you want to use a secure connection while communicating with reCAPTCHA servers', SGCF_TEXT_DOMAIN ); ?>">[?]</a>
				</label>
				<input type="checkbox" name="use_ssl" />
			</div>
		</fieldset>
		<fieldset>
			<legend><?php echo __( 'Display options', SGCF_TEXT_DOMAIN ); ?></legend>
			<div class="input">
				<label for="theme"><?php echo __( 'Theme', SGCF_TEXT_DOMAIN ); ?></label>
				<select name="theme">
					<option value="red">Red</option>
					<option value="white">White</option>
					<option value="blackglass">Blackglass</option>
					<option value="clean">Clean</option>
				</select>
			</div>
			<div class="input">
				<label for="nowrap"><?php echo __( 'Disable wrapper?', SGCF_TEXT_DOMAIN ); ?></label>
				<input type="checkbox" name="nowrap" />
			</div>
		</fieldset>
		<button class="button-primary"><?php echo __( 'Insert', SGCF_TEXT_DOMAIN ); ?></button>
	</div>
</div>