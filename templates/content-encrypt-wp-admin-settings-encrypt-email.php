<?php
/**
 * @var $option_name string
 * @var $options EncryptWP_Options_Manager
 * @var $args array
 * @var $prefix string
 */
?>

<?php if(!$options->encrypt_email):?>
	<div class="ewp-loading">
	<svg class="lds-spinner" width="50px"  height="50px"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" style="background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%;"><g transform="rotate(0 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.9166666666666666s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(30 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.8333333333333334s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(60 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.75s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(90 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.6666666666666666s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(120 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.5833333333333334s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(150 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.5s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(180 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.4166666666666667s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(210 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.3333333333333333s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(240 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.25s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(270 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.16666666666666666s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(300 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="-0.08333333333333333s" repeatCount="indefinite"></animate>
			</rect>
		</g><g transform="rotate(330 50 50)">
			<rect x="47" y="24" rx="9.4" ry="4.8" width="6" height="12" fill="#ff727d">
				<animate attributeName="opacity" values="1;0" times="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animate>
			</rect>
		</g></svg>
</div>
<?php endif; ?>
<div class="ewp-encrypt-email-supported <?= $options->encrypt_email ? 'ewp-enabled' : 'ewp-disabled'?>">
	<input id="encrypt_email_off" type="radio" name="<?= $option_name ?>[<?= $args['label_for'] ?>]" value="0" <?php checked($options->encrypt_email, false) ?>/>
	<label for="encrypt_email_off"><?php esc_html_e('Off', $prefix); ?></label>

	<input id="encrypt_email_on" type="radio" name="<?= $option_name ?>[<?= $args['label_for'] ?>]" value="1" <?php checked($options->encrypt_email, true) ?>/>
	<label for="encrypt_email_on"><?php esc_html_e('On', $prefix); ?></label>

	<p class="description">
		<?php esc_html_e( 'This feature is compatible with all of your plugins but may ru n.', $prefix ); ?>
	</p>
</div>

<p class="ewp-encrypt-email-unsupported">
	This feature is incompatible with one or more plugins you currently have installed which currently override
	the <code>get_user_by</code> function. Please contact support for more details.
</p>

