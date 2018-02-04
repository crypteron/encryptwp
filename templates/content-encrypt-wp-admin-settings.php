<?php
/**
 * @var $option_group string
 * @var $prefix string
 * @var $options EncryptWP_Options
 */
?>

<div class="wrap ewp-settings">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<div class="redux-container">
		<form id="encrypt-wp-settings">
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
							<p class="description">Note: making encrypted text searchable adds performance and storage overhead. Only enable enable if you intend on searching users by that specific field.</p>
							<?php foreach($options->user_meta_fields as $field => $status):?>
								<div class="ewp-field ewp-field_<?=$field?> ewp-field_<?=$field?>-<?=$status?>">
									<div class="ewp-field-title ewp-field-part"><?= $field ?>:</div>
									<div class="ewp-field-options">
										<fieldset class="redux-field-container redux-field redux-container-button_set">
											<div class="buttonset ui-buttonset">
												<input type="radio" id="ewp_user_field_<?= $field ?>_plain" name="user_fields[<?= $field ?>]" class="buttonset-item" value="-1" <?php checked(EncryptWP_Field_State::PLAINTEXT, $status);?>/>
												<label for="ewp_user_field_<?= $field ?>_plain">None</label>
												<input type="radio" id="ewp_user_field_<?= $field ?>_encrypted" name="user_fields[<?= $field ?>]" class="buttonset-item" value="0" <?php checked(EncryptWP_Field_State::ENCRYPTED, $status);?>/>
												<label for="ewp_user_field_<?= $field ?>_encrypted">Secure</label>
												<input type="radio" id="ewp_user_field_<?= $field ?>_search" name="user_fields[<?= $field ?>]" class="buttonset-item" value="1" <?php checked(EncryptWP_Field_State::ENCRYPTED_SEARCHABLE, $status);?>/>
												<label for="ewp_user_field_<?= $field ?>_search">Secure + Searchable</label>
											</div>
										</fieldset>
									</div>
								</div>
							<?php endforeach; ?>
						</td>
					</tr>
					</tbody>
				</table>
			<?php submit_button( 'Save Settings' ); ?>
			</div>
		</form>
	</div>


</div>
