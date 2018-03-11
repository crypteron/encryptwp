<?php
/**
 * @var $fields EncryptWP_Field[]
 * @var $type string
 */
foreach($fields as $field):?>
	<?php if(is_null($field->label)) continue; ?>
	<div class="ewp-field ewp-field_<?=$field->slug?> ewp-field_<?=$field->slug?>-<?=$field->state?>">
		<div class="ewp-field-title ewp-field-part"><?= $field->label ?>:</div>
		<div class="ewp-field-options">
			<fieldset class="redux-field-container redux-field redux-container-button_set">
				<div class="buttonset ui-buttonset">
					<input type="radio" id="ewp_<?= $type ?>_<?= $field->slug ?>_plain" name="<?= $type ?>[<?= $field->slug ?>]" class="buttonset-item" value="-1" <?php checked(EncryptWP_Field_State::PLAINTEXT, $field->state);?>/>
					<label for="ewp_<?= $type ?>_<?= $field->slug ?>_plain">None</label>
					<input type="radio" id="ewp_<?= $type ?>_<?= $field->slug ?>_encrypted" name="<?= $type ?>[<?= $field->slug ?>]" class="buttonset-item" value="0" <?php checked(EncryptWP_Field_State::ENCRYPTED, $field->state);?>/>
					<label for="ewp_<?= $type ?>_<?= $field->slug ?>_encrypted">Secure</label>
					<input type="radio" id="ewp_<?= $type ?>_<?= $field->slug ?>_search" name="<?= $type ?>[<?= $field->slug ?>]" class="buttonset-item" value="1" <?php checked(EncryptWP_Field_State::ENCRYPTED_SEARCHABLE, $field->state);?>/>
					<label for="ewp_<?= $type ?>_<?= $field->slug ?>_search">Secure + Searchable</label>
				</div>
			</fieldset>
		</div>
	</div>
<?php endforeach; ?>