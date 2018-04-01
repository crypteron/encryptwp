<?php
/**
 * @var $option_group string
 * @var $prefix string
 * @var $options EncryptWP_Options
 * @var $nonce string
 * @var $action string
 * @var $template_manager \TrestianCore\v1\Template_Manager
 * @var $key_defined bool
 * @var $key_new string | bool
 * @var $token_key_new = string | bool
 */
?>

<div class="wrap ewp-settings">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php if(!$key_defined):?>
		<p>No encryption keys defined. Copy the following into wp-config.php:</p>
<pre><code>define('CIPHER_CORE_KEY', '<?= $key_new ?>');
define('CIPHER_CORE_TOKEN_KEY', '<?= $token_key_new ?>');
</code></pre>
	<?php endif;?>

	<div class="redux-container">
		<form id="encrypt-wp-settings">
			<input name="nonce" type="hidden" value="<?= $nonce ?>"/>
			<input name="action" type="hidden" value="<?= $action ?>"/>
			<div class="redux-main">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<div class="redux_field_th">Encrypt User Fields
								<span class="description">Should EncryptWP secure your user data?</span>
							</div>
						</th>
						<td>
							<fieldset class="redux-field-container redux-field redux-container-button_set">
								<div class="buttonset ui-buttonset">
									<input type="radio" id="ewp_encrypt_enabled_no" name="encrypt_enabled" class="buttonset-item" value="0" <?php checked(false, $options->encrypt_enabled);?>/>
									<label for="ewp_encrypt_enabled_no">No</label>
									<input type="radio" id="ewp_encrypt_enabled_yes" name="encrypt_enabled" class="buttonset-item" value="1" <?php checked(true, $options->encrypt_enabled);?>/>
									<label for="ewp_encrypt_enabled_yes">Yes</label>
								</div>
							</fieldset>
						</td>
					</tr>
					<tr class="ewp-field_encrypt_enabled ewp-field_encrypt_enabled-<?= $options->encrypt_enabled ? '1' : '0'?>">
						<th scope="row">
							<div class="redux_field_th">Encrypt User Fields
								<span class="description">Which user fields should EncryptWP secure?</span>
							</div>
						</th>
						<td>
							<p class="description">Making encrypted text searchable adds performance and storage overhead. Only enable enable if you intend on searching users by that specific field.</p>
							<?php $template_manager->load_template('templates/fields/content-encrypt-wp-admin-fields.php', ['fields'=>$options->user_fields, 'type'=>'user_fields']); ?>
							<?php $template_manager->load_template('templates/fields/content-encrypt-wp-admin-fields.php', ['fields'=>$options->user_meta_fields, 'type'=>'user_meta_fields']); ?>
						</td>
					</tr>
					<tr class="ewp-field_encrypt_enabled ewp-field_encrypt_enabled-<?= $options->encrypt_enabled ? '1' : '0'?>">
						<th scope="row">
							<div class="redux_field_th">Encrypt User Email
								<span class="description">Enable email encryption?</span>
							</div>
						</th>
						<td>
							<p class="description">May not be compatible with all plugins.</p>
							<?php $template_manager->load_template('templates/fields/content-encrypt-wp-admin-encrypt-email.php', ['encrypt_email'=>$options->encrypt_email]); ?>
						</td>
					</tr>
					<tr class="ewp-field_encrypt_enabled ewp-field_encrypt_enabled-<?= $options->encrypt_enabled ? '1' : '0'?>">
						<th scope="row">
							<div class="redux_field_th">Admin Notification on Insecure Data
								<span class="description">Receive admin notice and email when insecure data is found?</span>
							</div>
						</th>
						<td>
							<p class="description">Insecure data within in a secure user field may mean that your database was tampered with.</p>
							<div class="ewp-field-options">
								<fieldset class="redux-field-container redux-field redux-container-button_set">
									<div class="buttonset ui-buttonset">
										<input type="radio" id="ewp_strict_mode_off" name="strict_mode" class="buttonset-item" value="0" <?php checked(false, $options->strict_mode);?>/>
										<label for="ewp_strict_mode_off">No</label>
										<input type="radio" id="ewp_strict_mode_on" name="strict_mode" class="buttonset-item" value="1" <?php checked(true, $options->strict_mode);?>/>
										<label for="ewp_strict_mode_on">Yes</label>
									</div>
								</fieldset>
							</div>
						</td>
					</tr>
					<tr class="ewp-field_encrypt_enabled ewp-field_encrypt_enabled-<?= $options->encrypt_enabled ? '1' : '0'?>">
						<th scope="row">
							<div class="redux_field_th">Admin Email Addresses
								<span class="description">Who should be notified on errors?</span>
							</div>
						</th>
						<td>
							<p class="description">Comma separated list of email addresses.</p>
							<div class="ewp-field-options">
								<fieldset class="redux-field-container redux-field">
									<input type="text" name="admin_notify" class="regular-text" value="<?= implode($options->admin_notify, ', ') ?>"/>
								</fieldset>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
			<?php submit_button( 'Save Settings' ); ?>
			</div>
		</form>
	</div>


</div>
