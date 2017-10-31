<?php
/**
 * @var $option_name string
 * @var $options EncryptWP_Options_Manager
 * @var $args array
 * @var $prefix string
 */
?>

<input id="strict_mode_off" type="radio" name="<?= $option_name ?>[<?= $args['label_for'] ?>]" value="0" <?php checked($options->strict_mode, false) ?>/>
<label for="strict_mode_off"><?php esc_html_e('Off', $prefix); ?></label>

<input id="strict_mode_on" type="radio" name="<?= $option_name ?>[<?= $args['label_for'] ?>]" value="1" <?php checked($options->strict_mode, true) ?>/>
<label for="strict_mode_on"><?php esc_html_e('On', $prefix); ?></label>

<p class="description">
	<?php esc_html_e( 'Should EncryptWP trigger an error if unencrypted text is found in a secure property? This is required to prevent tamper protection. Only enable after you\'ve encrypted your whole database below.', $prefix ); ?>
</p>
