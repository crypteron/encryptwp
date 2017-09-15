<?php
/**
 * @var $option_group string
 * @var $prefix string
 */
?>
<?php settings_errors( $prefix . '_messages' ); ?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
		<?php
		// output security fields for the registered setting "wporg"
		settings_fields( $option_group );
		// output setting sections and their fields
		// (sections are registered for "wporg", each field is registered to a specific section)
		do_settings_sections( $option_group );
		// output save settings button
		submit_button( 'Save Settings' );
		?>
	</form>
</div>
