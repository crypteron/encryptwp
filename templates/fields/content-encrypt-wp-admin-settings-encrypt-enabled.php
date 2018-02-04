<?php
/**
 * @var $option_name string
 * @var $options EncryptWP_Options
 * @var $args array
 * @var $prefix string
 */
?>

<input id="encrypt_enabled_off" type="radio" name="<?= $option_name ?>[<?= $args['label_for'] ?>]" value="0" <?php checked($options->encrypt_enabled, false) ?>/>
<label for="encrypt_enabled_off"><?php esc_html_e('Off', $prefix); ?></label>

<input id="encrypt_enabled_on" type="radio" name="<?= $option_name ?>[<?= $args['label_for'] ?>]" value="1" <?php checked($options->encrypt_enabled, true) ?>/>
<label for="encrypt_enabled_on"><?php esc_html_e('On', $prefix); ?></label>

<p class="description">
	<?php esc_html_e( 'Should EncryptWP automatically encrypt sensitive user fields?', $prefix ); ?>
</p>
